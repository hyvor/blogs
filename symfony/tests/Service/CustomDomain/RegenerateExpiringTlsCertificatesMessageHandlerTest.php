<?php

namespace App\Tests\Service\CustomDomain;

use App\Entity\Enum\CustomDomainTlsProvider;
use App\Service\Hosting\CustomDomain\Message\RegenerateExpiringTlsCertificatesMessage;
use App\Service\Hosting\CustomDomain\MessageHandler\RegenerateExpiringTlsCertificatesMessageHandler;
use App\Service\Hosting\CustomDomain\PrivateKey;
use App\Tests\Factory\BlogFactory;
use App\Tests\Factory\CustomDomainFactory;
use Hyvor\Internal\Bundle\Testing\KernelTestCase;
use Hyvor\Internal\Util\Crypt\Encryption;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Clock\Clock;
use Symfony\Component\Clock\MockClock;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\JsonMockResponse;
use Symfony\Component\HttpClient\Response\MockResponse;
use Symfony\Contracts\HttpClient\HttpClientInterface;

#[CoversClass(RegenerateExpiringTlsCertificatesMessageHandler::class)]
class RegenerateExpiringTlsCertificatesMessageHandlerTest extends KernelTestCase
{
    public function test_renews_certificate_expiring_within_30_days(): void
    {
        Clock::set(new MockClock());
        $this->getService(CacheItemPoolInterface::class)->clear();

        $customDomain = CustomDomainFactory::createActiveFor(
            BlogFactory::createOne(['subdomain' => 'regen-tls-renews']),
            'renew.example.com',
            [
                'tls_provider' => CustomDomainTlsProvider::AUTO,
                'valid_to' => new \DateTimeImmutable('+10 days'),
                'private_key_encrypted' => $this->encryptedPrivateKey(),
            ]
        );

        static::getContainer()->set(
            HttpClientInterface::class,
            $this->acmeMockClient(fn() => $this->validAuthorizationResponse('token-renew'))
        );

        $handler = $this->getService(RegenerateExpiringTlsCertificatesMessageHandler::class);
        $handler(new RegenerateExpiringTlsCertificatesMessage());

        // the new certificate's validity comes from the (fixed) sample PEM, not from our
        // fixture's original valid_to, so any change confirms a renewal actually happened
        $this->assertNotEquals(new \DateTimeImmutable('+10 days'), $customDomain->getValidTo());
        $this->assertSame(self::PEM_CERTIFICATE_SAMPLE, $customDomain->getCertificate());
    }

    public function test_skips_certificates_outside_the_renewal_window_and_custom_tls(): void
    {
        Clock::set(new MockClock());
        $this->getService(CacheItemPoolInterface::class)->clear();

        $blog1 = BlogFactory::createOne(['subdomain' => 'regen-tls-not-expiring']);
        $notExpiringSoon = CustomDomainFactory::createActiveFor($blog1, 'not-expiring.example.com', [
            'tls_provider' => CustomDomainTlsProvider::AUTO,
            'valid_to' => new \DateTimeImmutable('+60 days'),
            'private_key_encrypted' => $this->encryptedPrivateKey(),
        ]);

        $blog2 = BlogFactory::createOne(['subdomain' => 'regen-tls-custom']);
        $customTls = CustomDomainFactory::createActiveFor($blog2, 'custom-tls.example.com', [
            'tls_provider' => CustomDomainTlsProvider::CUSTOM,
            'valid_to' => new \DateTimeImmutable('+5 days'),
            'private_key_encrypted' => $this->encryptedPrivateKey(),
        ]);

        $calls = 0;
        static::getContainer()->set(HttpClientInterface::class, new MockHttpClient(
            function () use (&$calls): MockResponse {
                $calls++;
                return new MockResponse('', ['http_code' => 500]);
            }
        ));

        $handler = $this->getService(RegenerateExpiringTlsCertificatesMessageHandler::class);
        $handler(new RegenerateExpiringTlsCertificatesMessage());

        $this->assertSame(0, $calls, 'Neither certificate should have been touched via ACME');
        $this->assertSame('cert-pem-data', $notExpiringSoon->getCertificate());
        $this->assertSame('cert-pem-data', $customTls->getCertificate());
    }

    public function test_logs_error_and_continues_with_next_domain_when_acme_fails(): void
    {
        Clock::set(new MockClock());
        $this->getService(CacheItemPoolInterface::class)->clear();

        $blog1 = BlogFactory::createOne(['subdomain' => 'regen-tls-fails']);
        $failing = CustomDomainFactory::createActiveFor($blog1, 'failing.example.com', [
            'tls_provider' => CustomDomainTlsProvider::AUTO,
            // also within the "alert the user" window
            'valid_to' => new \DateTimeImmutable('+3 days'),
            'private_key_encrypted' => $this->encryptedPrivateKey(),
        ]);

        $blog2 = BlogFactory::createOne(['subdomain' => 'regen-tls-succeeds']);
        $succeeding = CustomDomainFactory::createActiveFor($blog2, 'succeeding.example.com', [
            'tls_provider' => CustomDomainTlsProvider::AUTO,
            'valid_to' => new \DateTimeImmutable('+20 days'),
            'private_key_encrypted' => $this->encryptedPrivateKey(),
        ]);

        // authorization is polled once per domain (both go straight to a terminal status,
        // no pending->valid transition needed): calls 1-2 belong to the first (more urgent,
        // and therefore first-processed) domain and fail; calls 3-4 belong to the second and
        // succeed.
        $authorizationCall = 0;
        $mockClient = $this->acmeMockClient(function () use (&$authorizationCall): JsonMockResponse {
            $authorizationCall++;
            if ($authorizationCall <= 2) {
                return new JsonMockResponse([
                    'status' => 'invalid',
                    'challenges' => [
                        [
                            'type' => 'http-01',
                            'url' => 'https://acme.org/challenge/1',
                            'token' => 'token-fail',
                            'status' => 'invalid',
                            'error' => [
                                'type' => 'urn:ietf:params:acme:error:connection',
                                'detail' => 'Connection refused',
                                'status' => 400,
                            ],
                        ],
                    ],
                ]);
            }

            return $this->validAuthorizationResponse('token-succeed');
        });
        static::getContainer()->set(HttpClientInterface::class, $mockClient);

        $handler = $this->getService(RegenerateExpiringTlsCertificatesMessageHandler::class);
        // should not throw, despite the first domain failing
        $handler(new RegenerateExpiringTlsCertificatesMessage());

        $this->assertSame('cert-pem-data', $failing->getCertificate());
        $this->assertSame(self::PEM_CERTIFICATE_SAMPLE, $succeeding->getCertificate());

        $this->assertTrue(
            $this->getTestLogger()->hasErrorThatContains(
                'Failed to regenerate TLS certificate for custom domain'
            )
        );
    }

    private function encryptedPrivateKey(): string
    {
        return $this->getService(Encryption::class)->encryptString(PrivateKey::generatePrivateKeyPem());
    }

    /**
     * @param \Closure(): JsonMockResponse $authorizationResponse
     */
    private function acmeMockClient(\Closure $authorizationResponse): MockHttpClient
    {
        return new MockHttpClient(function (string $method, string $url) use ($authorizationResponse): MockResponse {
            if ($method === 'GET') {
                return $this->directoryResponse();
            }
            if ($method === 'HEAD') {
                return $this->nonceResponse();
            }

            return match ($url) {
                'https://acme.org/newAccount' => new JsonMockResponse([], info: [
                    'response_headers' => ['Location' => ['https://acme.org/acct/1']],
                ]),
                'https://acme.org/newOrder' => new JsonMockResponse(
                    [
                        'status' => 'pending',
                        'authorizations' => ['https://acme.org/authz/1'],
                        'finalize' => 'https://acme.org/finalize/1',
                    ],
                    info: ['response_headers' => ['Location' => ['https://acme.org/order/1']]],
                ),
                'https://acme.org/authz/1' => $authorizationResponse(),
                'https://acme.org/challenge/1' => new JsonMockResponse([]),
                'https://acme.org/finalize/1' => new JsonMockResponse([]),
                'https://acme.org/order/1' => new JsonMockResponse([
                    'status' => 'valid',
                    'finalize' => 'https://acme.org/finalize/1',
                    'authorizations' => [],
                    'certificate' => 'https://acme.org/cert/1',
                ]),
                'https://acme.org/cert/1' => new MockResponse(self::PEM_CERTIFICATE_SAMPLE),
                default => new MockResponse('', ['http_code' => 404]),
            };
        });
    }

    private function validAuthorizationResponse(string $token): JsonMockResponse
    {
        return new JsonMockResponse([
            'status' => 'valid',
            'challenges' => [
                [
                    'type' => 'http-01',
                    'url' => 'https://acme.org/challenge/1',
                    'token' => $token,
                ],
            ],
        ]);
    }

    private function directoryResponse(): JsonMockResponse
    {
        return new JsonMockResponse([
            'newAccount' => 'https://acme.org/newAccount',
            'newOrder' => 'https://acme.org/newOrder',
            'newNonce' => 'https://acme.org/newNonce',
            'revokeCert' => 'https://acme.org/revokeCert',
            'keyChange' => 'https://acme.org/keyChange',
        ]);
    }

    private static int $nonceCounter = 0;

    private function nonceResponse(): MockResponse
    {
        self::$nonceCounter++;
        return new MockResponse('', [
            'response_headers' => [
                'Replay-Nonce' => ['test-nonce-' . self::$nonceCounter],
            ],
        ]);
    }

    // DATA
    public const string PEM_CERTIFICATE_SAMPLE = <<<EOT
-----BEGIN CERTIFICATE-----
MIIFQjCCBCqgAwIBAgISLN/KRgzNY5rzTbuApEmkbGyfMA0GCSqGSIb3DQEBCwUA
MFgxCzAJBgNVBAYTAlVTMSAwHgYDVQQKExcoU1RBR0lORykgTGV0J3MgRW5jcnlw
dDEnMCUGA1UEAxMeKFNUQUdJTkcpIFJpZGRsaW5nIFJodWJhcmIgUjEyMB4XDTI1
MTEyNTAyNTkxMVoXDTI2MDIyMzAyNTkxMFowJzElMCMGA1UEAxMcbXgubWFpbHRl
c3QuaHl2b3JzdGFnaW5nLmNvbTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoC
ggEBAOlgRpA9FpdPlxkucP6ekHTwctIkeLBTKu5o1eFgeV7nUfPh6gzrVJVw9eyF
PzlxiGT1SF6aZgC2QiNtoOpzy3N3RcglaKYb2XdTtvlOEi+ABQfvsa2zzGuc/mvo
UJCICO+9izuNMpipk7C0XzZI9flqkhI9zDLQP7iOlceGRgWyo4tQVnR6JQ01ms2L
yFA2SHd5C6jsY/x3b2y8xpqPS7ctfyVLMOMobJS5v9lee2NZOo4huorcr4CCtZ2g
D4ZBlk2vRqOvn/Q3wESa3B+rZehDBmJwPyyAhpSJv0zeJ7mBUsIvkdlIjifdnatt
1PqSQZzd2amHTLRWGUPPIZ2T9VkCAwEAAaOCAjUwggIxMA4GA1UdDwEB/wQEAwIF
oDAdBgNVHSUEFjAUBggrBgEFBQcDAQYIKwYBBQUHAwIwDAYDVR0TAQH/BAIwADAd
BgNVHQ4EFgQU8WXY4gKUNp1jcT+9GOLxrZi7/aAwHwYDVR0jBBgwFoAU9kMDkS9m
Ja+FJd3kZNFpfsuiHNkwNwYIKwYBBQUHAQEEKzApMCcGCCsGAQUFBzAChhtodHRw
Oi8vc3RnLXIxMi5pLmxlbmNyLm9yZy8wJwYDVR0RBCAwHoIcbXgubWFpbHRlc3Qu
aHl2b3JzdGFnaW5nLmNvbTATBgNVHSAEDDAKMAgGBmeBDAECATAyBgNVHR8EKzAp
MCegJaAjhiFodHRwOi8vc3RnLXIxMi5jLmxlbmNyLm9yZy8zMi5jcmwwggEFBgor
BgEEAdZ5AgQCBIH2BIHzAPEAdwAW6GnB0ZXq18P4lxrj8HYB94zhtp0xqFIYtoN/
MagVCAAAAZq5KPgAAAAEAwBIMEYCIQCYvAfRshjHgUX4wxfHOiWghUaZlru0xACl
Q+Cpit1O5gIhAKJjy9xEpUkbfw4sZouRLeucns1wtWCtrP7KyRUZA7wjAHYAsMyD
5aX5fWuvfAnMKEkEhyrH6IsTLGNQt8b9JuFsbHcAAAGauSj37gAABAMARzBFAiBH
M11ZWGNgsNCd0Zzt1+Kq3mehU83IQpASLVVrc2a/cgIhAJ08mWKqe3q6VJ0LUQoi
zh/te0Pi2vJZfIRnoHCgFbiNMA0GCSqGSIb3DQEBCwUAA4IBAQAIfhxtkkwJYmpo
lqdY4YQMO681SLAP+Rf9ahkmLNo9DwhEaayqCK/WsJPaOqs1aAHSAZvY+u27EPmI
E7UjRUXdmXueDjzTC3e94GiecFpjaOj7Ypn4TZvQafVQWwi0bhjM/rQpWT8sA7AQ
tPq2kw+iFqbplozaDBslo9A6lLR0axcbUfFoEdLHOwctjbVNqiiS3Iva5DMdb0CO
NyPjDylFLagLMJsf7qp/mxUYu2oZTTaJxfOceppbtW/G+wlNNCbTneir5S0BXt88
QT4SMlPgSazTjxuw6VKMMLPRAWmtdJiNRL57pHyK4A5tdK1YiVqXQZuGNSgM2oYU
Ja3j4HN3
-----END CERTIFICATE-----

-----BEGIN CERTIFICATE-----
MIIFSjCCAzKgAwIBAgIQG+VMtXt5SUBCqOW+UHI8WTANBgkqhkiG9w0BAQsFADBm
MQswCQYDVQQGEwJVUzEzMDEGA1UEChMqKFNUQUdJTkcpIEludGVybmV0IFNlY3Vy
aXR5IFJlc2VhcmNoIEdyb3VwMSIwIAYDVQQDExkoU1RBR0lORykgUHJldGVuZCBQ
ZWFyIFgxMB4XDTI0MDMxMzAwMDAwMFoXDTI3MDMxMjIzNTk1OVowWDELMAkGA1UE
BhMCVVMxIDAeBgNVBAoTFyhTVEFHSU5HKSBMZXQncyBFbmNyeXB0MScwJQYDVQQD
Ex4oU1RBR0lORykgUmlkZGxpbmcgUmh1YmFyYiBSMTIwggEiMA0GCSqGSIb3DQEB
AQUAA4IBDwAwggEKAoIBAQDYHSGRScFq1U7sAS8YfqaA2I/hnt1bHeDT3/lnQcQ4
U24ic0bSHmDECLIgQh8b4HhKG0G5X9P7+8JQgWQbizkNJYps3IOwhqiG0FmPvR1m
/byeEtidU0aVtAQoLNIkHulaw1l3nCIBGtL5ZGGKzOGP39VRNp2iV4a1TIGnb0/F
z08FWhvkAxaX6WP8HH9aFvvsnjH14D+O24WBJjqHe5V8+IiLoxM9EcRCgOk3oAS2
VngAnoSqkqeHKoIIz8t761lEk34Ln8qhEEHCxaX44Y4glllbLQL1fN1tEUiV7Gfw
W4JU644Em2RkzHr5G/rD6aam6EXgmIq4Nucj9IENjN7hAgMBAAGjggEAMIH9MA4G
A1UdDwEB/wQEAwIBhjAdBgNVHSUEFjAUBggrBgEFBQcDAgYIKwYBBQUHAwEwEgYD
VR0TAQH/BAgwBgEB/wIBADAdBgNVHQ4EFgQU9kMDkS9mJa+FJd3kZNFpfsuiHNkw
HwYDVR0jBBgwFoAUtfNl8v6wCpIf+zx980SgrGMlwxQwNgYIKwYBBQUHAQEEKjAo
MCYGCCsGAQUFBzAChhpodHRwOi8vc3RnLXgxLmkubGVuY3Iub3JnLzATBgNVHSAE
DDAKMAgGBmeBDAECATArBgNVHR8EJDAiMCCgHqAchhpodHRwOi8vc3RnLXgxLmMu
bGVuY3Iub3JnLzANBgkqhkiG9w0BAQsFAAOCAgEArPTkhI92+dUqNgxVitYi/w8z
xwOXR6rhiJvKaoOFhA8DxqUaQ12eIVrhIN8LSQq2+O/Su3+VQaLXuQoeh/RBts6m
GQS56fTuf6Q7Sb49olfNQjkdV+BqxkUzyWl7j/GAGumpZxGPLRmfUT0BV1jq+2Yd
HKjvo3smWPEBnx/XMQ+p+fXh3xL1ATgB+eFtgQ/mQkVLMDUWGrcHUy78LTVgTZmt
eWhTubOipgNVJi19FHi3cYdsPTM3JOCAGahsXOYV30sU+9TKw+fEG1RgjucVAiPC
VxoyT/xiQ28tJ+MjWodlhrdWlTmiLHm6uBqJV8cEh6v0DwlI6BdnbCqM44d+jsO9
QjlPiG6uGX9LEYaVmgIA3cplNBdWZ3KdYNnxh+bVFz9aO8j1G5kDTNldx6nMP+u5
t1rA0iHIVMhXbY2JALAbO6/kH8XWmghsC81Xr5PzJEnROKZ65daBItHambNl+JJc
mj84Iqe4HIjgOn2/BbfQHu/YfQx8gZGl5nXarDkGtI3IS4jb9OPfJZEErnizRVAV
KsqiDk04Teh3BZX/w2prXHGCdsxCbbQgfximi0Q/Ug1xE0HCmmdTq/ZgYb2zdu3m
s6Sg00ivj7b0LaPtVGt1eEFqX+6OofleMic4uZgiazH/87j0EhBquPEmNPeXEMq1
SIKYbiG+Lshx0NWHFGE=
-----END CERTIFICATE-----
EOT;
}
