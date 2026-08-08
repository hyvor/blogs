<?php

namespace App\Tests\Helper;

/**
 * Generates a throwaway self-signed private key / certificate pair for
 * exercising the "bring your own TLS" custom domain flow in tests.
 */
class SelfSignedCertificate
{
    /**
     * @return array{privateKeyPem: string, certificatePem: string}
     */
    public static function generate(string $domain = 'example.com'): array
    {
        $privateKey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_RSA,
            'private_key_bits' => 2048,
        ]);
        if (!$privateKey instanceof \OpenSSLAsymmetricKey) {
            throw new \RuntimeException('Failed to generate a private key'); // @codeCoverageIgnore
        }

        $csr = openssl_csr_new(['commonName' => $domain], $privateKey);
        if (!$csr instanceof \OpenSSLCertificateSigningRequest) {
            throw new \RuntimeException('Failed to generate a certificate signing request'); // @codeCoverageIgnore
        }

        // openssl_csr_new() receives $privateKey by reference, which invalidates its
        // narrowed type; re-check it before using it again below.
        if (!$privateKey instanceof \OpenSSLAsymmetricKey) {
            throw new \RuntimeException('Private key became invalid after generating the CSR'); // @codeCoverageIgnore
        }

        $cert = openssl_csr_sign($csr, null, $privateKey, 90);
        if (!$cert instanceof \OpenSSLCertificate) {
            throw new \RuntimeException('Failed to sign the certificate'); // @codeCoverageIgnore
        }

        $privateKeyPem = '';
        openssl_pkey_export($privateKey, $privateKeyPem);
        if (!is_string($privateKeyPem)) {
            throw new \RuntimeException('Failed to export the private key to PEM format'); // @codeCoverageIgnore
        }

        $certificatePem = '';
        openssl_x509_export($cert, $certificatePem);
        if (!is_string($certificatePem)) {
            throw new \RuntimeException('Failed to export the certificate to PEM format'); // @codeCoverageIgnore
        }

        return [
            'privateKeyPem' => $privateKeyPem,
            'certificatePem' => $certificatePem,
        ];
    }
}
