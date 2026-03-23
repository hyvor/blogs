<?php

namespace App\Service\TlsCertificate\Acme;

use App\Service\TlsCertificate\Acme\Dto\AccountInternalDto;
use App\Service\TlsCertificate\Acme\Dto\AuthorizationResponse\AuthorizationResponse;
use App\Service\TlsCertificate\Acme\Dto\DirectoryDto;
use App\Service\TlsCertificate\Acme\Dto\OrderResponse;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Clock\ClockInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\HeaderBag;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\Exception\ExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class AcmeClient implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public const string DIRECTORY_URL_LETSENCRYPT_PRODUCTION = 'https://acme-v02.api.letsencrypt.org/directory';
    public const string DIRECTORY_URL_LETSENCRYPT_STAGING = 'https://acme-staging-v02.api.letsencrypt.org/directory';

    private const string CACHE_ACCOUNT_KEY = 'acme_account';

    private string $directoryUrl;
    private DirectoryDto $directory;
    private AccountInternalDto $account;

    public function __construct(
        private HttpClientInterface $http,
        private CacheInterface $cache,
        private DenormalizerInterface $denormalizer,
        private ClockInterface $clock,
//        private DnsResolveInterface $dnsResolve,
        #[Autowire('%kernel.environment%')]
        private string $env,
    ) {
        if ($this->env === 'prod') {
            $this->directoryUrl = self::DIRECTORY_URL_LETSENCRYPT_PRODUCTION; // @codeCoverageIgnore
        } else {
            $this->directoryUrl = self::DIRECTORY_URL_LETSENCRYPT_STAGING;
        }
    }

    /**
     * @throws AcmeException
     */
    public function init(): void
    {
        $this->loadDirectory();
        $this->loadAccount();
    }

    /**
     * @throws AcmeException
     */
    private function loadDirectory(): void
    {
        if (isset($this->directory)) {
            return; // @codeCoverageIgnore
        }

        $this->directory = $this->httpRequest($this->directoryUrl, returnType: DirectoryDto::class, method: 'GET');
    }

    private function loadAccount(): void
    {
        if (isset($this->account)) {
            return; // @codeCoverageIgnore
        }

        $this->account = $this->cache->get(self::CACHE_ACCOUNT_KEY, function () {
            $privateKey = openssl_pkey_new([
                "private_key_type" => OPENSSL_KEYTYPE_RSA,
                "private_key_bits" => 2048,
            ]);

            if ($privateKey === false) {
                // @codeCoverageIgnoreStart
                throw new AcmeException(
                    'Failed to generate private key for ACME account: ' . openssl_error_string()
                );
                // @codeCoverageIgnoreEnd
            }

            openssl_pkey_export($privateKey, $privateKeyPem);
            assert(is_string($privateKeyPem));

            $this->account = new AccountInternalDto(
                privateKeyPem: $privateKeyPem,
                kid: null,
            );

            return $this->newAccount();
        });
    }

    /**
     * Manages one global ACME account per Hyvor Talk instance
     * @throws AcmeException
     */
    private function newAccount(): AccountInternalDto
    {
        $this->logger?->info('Creating new ACME account');

        $payload = [
            'contact' => [],
            'termsOfServiceAgreed' => true
        ];

        $headerBag = new HeaderBag();
        $this->httpRequest($this->directory->newAccount, $payload, headerBag: $headerBag);

        $kid = $headerBag->get('location');
        if (!$kid) {
            throw new AcmeException('No KID returned from ACME server');  // @codeCoverageIgnore
        }

        $this->account = new AccountInternalDto(
            privateKeyPem: $this->account->privateKeyPem,
            kid: $kid,
        );

        return $this->account;
    }


    /**
     * Orders a new certificate for the given domain
     * and returns a PendingOrder
     * PendingOrder->keyAuthorization is the token for the HTTP-01 challenge
     * @throws AcmeException
     */
    public function newOrder(string $domain): PendingOrder
    {
        $this->logger?->info('Calling new order endpoint');
        $payload = [
            'identifiers' => [
                ['type' => 'dns', 'value' => $domain],
            ],
        ];

        $headers = new HeaderBag();
        $response = $this->httpRequest(
            $this->directory->newOrder,
            $payload,
            returnType: OrderResponse::class,
            headerBag: $headers,
        );
        $orderUrl = $headers->get('location');
        if (!$orderUrl) {
            throw new AcmeException('No order URL returned from ACME server');  // @codeCoverageIgnore
        }

        $this->logger?->info('Order created successfully, fetching authorization', [
            'authorizationUrls' => $response->authorizations,
        ]);
        $authorizationUrl = $response->firstAuthorizationUrl();
        $authorization = $this->httpRequest(
            $authorizationUrl,
            // needs empty payload than empty object
            // https://datatracker.ietf.org/doc/html/rfc8555/#section-7.5
            payload: "",
            returnType: AuthorizationResponse::class,
        );

        $this->logger?->info('Authorization fetched, preparing HTTP-01 challenge response');
        $httpChallenge = $authorization->getFirstHttp01Challenge();
        $thumbprint = $this->base64url(
            hash('sha256', (string)json_encode($this->getJwk()), true)
        );
        $keyAuthorization = $httpChallenge->token . '.' . $thumbprint;

        return new PendingOrder(
            domain: $domain,
            token: $httpChallenge->token,
            keyAuthorization: $keyAuthorization,
            orderUrl: $orderUrl,
            challengeUrl: $httpChallenge->url,
            authorizationUrl: $authorizationUrl,
            finalizeOrderUrl: $response->finalize,
        );
    }

    /**
     * @param class-string<T> $returnType
     * @param array<string, mixed> $payload
     * @param 'POST'|'GET'|'HEAD' $method
     * @param ReturnRawContent $returnRawContent
     * @return (ReturnRawContent is true ? string : T)
     * @throws AcmeException
     * @template T of object
     * @template ReturnRawContent of bool
     */
    private function httpRequest(
        string $url,
        string|array $payload = [],
        string $returnType = \stdClass::class,
        string $method = 'POST',
        ?HeaderBag $headerBag = null, // will capture headers
        bool $returnRawContent = false,
    ) {
        try {
            $options = [
                'headers' => [
                    'Content-Type' => 'application/jose+json',
                ],
            ];

            if ($method === 'POST') {
                $options['json'] = $this->sign($payload, $url);
            }

            $response = $this->http->request(
                $method,
                $url,
                $options,
            );

            if ($headerBag !== null) {
                foreach ($response->getHeaders() as $name => $values) {
                    foreach ($values as $value) {
                        $headerBag->set($name, $value);
                    }
                }
            }

            if ($returnRawContent) {
                return $response->getContent();
            }

            $body = $response->toArray();
            /** @var T $object */
            $object = $this->denormalizer->denormalize($body, $returnType);

            return $object;
            // @codeCoverageIgnoreStart
        } catch (ExceptionInterface $e) {
            $this->logger?->error('HTTP request to ACME server failed', [
                'url' => $url,
                'payload' => $payload,
                'exception' => $e->getMessage(),
            ]);

            throw new AcmeException('HTTP request failed: ' . $e->getMessage());
        } catch (\Symfony\Component\Serializer\Exception\ExceptionInterface $e) {
            $this->logger?->error('Failed to deserialize ACME server response', [
                'url' => $url,
                'payload' => $payload,
                'exception' => $e->getMessage(),
            ]);

            throw new AcmeException('Deserialization failed: ' . $e->getMessage());
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * account->privateKey must be set before calling this method
     *
     * @param string|array<string, mixed> $payload
     * @return array<string, mixed>
     * @throws AcmeException
     */
    private function sign(string|array $payload, string $url): array
    {
        $nonce = $this->fetchNonce();

        $protected = [
            'alg' => 'RS256',
            'nonce' => $nonce,
            'url' => $url,
        ];

        if ($this->account->kid) {
            $protected['kid'] = $this->account->kid;
        } else {
            $jwk = $this->getJwk();
            $protected['jwk'] = $jwk;
        }

        $protectedBase64 = $this->base64url($this->jsonEncode($protected));
        $payloadBase64 = $this->base64url(is_string($payload) ? $payload : $this->jsonEncode($payload));

        openssl_sign(
            $protectedBase64 . "." . $payloadBase64,
            $signature,
            $this->account->privateKeyPem,
            OPENSSL_ALGO_SHA256
        );
        assert(is_string($signature));
        $signatureBase64 = $this->base64url($signature);

        return [
            'protected' => $protectedBase64,
            'payload' => $payloadBase64,
            'signature' => $signatureBase64,
        ];
    }


    /**
     * @return array<string, string>
     */
    private function getJwk(): array
    {
        /**
         * @var array{rsa: array{n: string, e: string}} $details
         */
        $details = openssl_pkey_get_details($this->account->getPrivateKey());
        return [
            'e' => $this->base64url($details['rsa']['e']),
            'kty' => 'RSA',
            'n' => $this->base64url($details['rsa']['n']),
        ];
    }

    /**
     * @param array<mixed> $data
     */
    private function jsonEncode(array $data): string
    {
        $json = json_encode((object)$data);
        assert(is_string($json));
        return $json;
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    /**
     * @throws AcmeException
     */
    private function fetchNonce(): string
    {
        $headers = new HeaderBag();
        $this->httpRequest(
            $this->directory->newNonce,
            method: 'HEAD',
            headerBag: $headers,
            returnRawContent: true,
        );

        $nonce = $headers->get('replay-nonce');

        if (!$nonce) {
            throw new AcmeException('No nonce returned from ACME server'); // @codeCoverageIgnore
        }

        return $nonce;
    }
}