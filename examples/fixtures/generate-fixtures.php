<?php

declare(strict_types=1);

/**
 * Generates test fixtures for the examples:
 * - test.p12: self-signed PKCS#12 keystore
 * - sample.pdf: minimal valid PDF
 *
 * Usage: php examples/fixtures/generate-fixtures.php
 */

$dir = __DIR__;

// Generate a self-signed PKCS#12 keystore
$keyPair = \openssl_pkey_new([
    'private_key_type' => \OPENSSL_KEYTYPE_RSA,
    'private_key_bits' => 2048,
]);

if (false === $keyPair) {
    echo "Failed to generate key pair.\n";
    exit(1);
}

$csr = \openssl_csr_new([
    'CN' => 'Authentin Test Signer',
    'O' => 'Authentin',
    'C' => 'EU',
], $keyPair);

if (false === $csr) {
    echo "Failed to generate CSR.\n";
    exit(1);
}

$cert = \openssl_csr_sign($csr, null, $keyPair, 3650);

if (false === $cert) {
    echo "Failed to sign certificate.\n";
    exit(1);
}

$pkcs12 = '';

if (false === \openssl_pkcs12_export($cert, $pkcs12, $keyPair, 'test')) {
    echo "Failed to export PKCS#12.\n";
    exit(1);
}

if (false === \file_put_contents($dir . '/test.p12', $pkcs12)) {
    echo "Failed to write test.p12.\n";
    exit(1);
}

// NOTE: This is a test-only keystore with no security value.
// The private key is intentionally committed to the repository.
echo "Created: fixtures/test.p12 (password: test, valid for 10 years)\n";

// Generate a minimal valid PDF
$pdf = "%PDF-1.4\n"
    . "1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n"
    . "2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n"
    . "3 0 obj<</Type/Page/MediaBox[0 0 612 792]/Parent 2 0 R>>endobj\n"
    . "xref\n0 4\n"
    . "0000000000 65535 f \n"
    . "0000000009 00000 n \n"
    . "0000000058 00000 n \n"
    . "0000000115 00000 n \n"
    . "trailer<</Size 4/Root 1 0 R>>\n"
    . "startxref\n190\n%%EOF";

if (false === \file_put_contents($dir . '/sample.pdf', $pdf)) {
    echo "Failed to write sample.pdf.\n";
    exit(1);
}

echo "Created: fixtures/sample.pdf\n";

echo "Done.\n";
