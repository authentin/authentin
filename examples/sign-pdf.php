<?php

declare(strict_types=1);

/**
 * Sign a PDF document using the eusig library.
 *
 * Prerequisites:
 *   - DSS running at http://localhost:8080 (docker compose up -d --wait)
 *   - Test fixtures generated (php examples/fixtures/generate-fixtures.php)
 *
 * Usage: php examples/sign-pdf.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Authentin\Eusig\Dss\DssClient;
use Authentin\Eusig\Dss\DssSigningClient;
use Authentin\Eusig\Model\Document;
use Authentin\Eusig\Model\SignatureLevel;
use Authentin\Eusig\Model\SignatureParameters;
use Authentin\Eusig\Signer;
use Authentin\Eusig\Token\Pkcs12Token;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Psr18Client;

$dssBaseUrl = \getenv('DSS_BASE_URL') ?: 'http://localhost:8080/services/rest';

// 1. Create the HTTP infrastructure
$psr17 = new Psr17Factory();
$dssClient = new DssClient(new Psr18Client(), $psr17, $psr17, $dssBaseUrl);

// 2. Load the signing token (PKCS#12 keystore)
$token = Pkcs12Token::fromFile(__DIR__ . '/fixtures/test.p12', 'test');

// 3. Create the signer (wires DSS client + token)
$signer = new Signer(new DssSigningClient($dssClient), $token);

// 4. Load the document and sign it
$document = Document::fromLocalFile(__DIR__ . '/fixtures/sample.pdf');
$params = new SignatureParameters(
    signatureLevel: SignatureLevel::PAdES_BASELINE_B,
);

echo "Signing {$document->filename}...\n";

try {
    $signed = $signer->sign($document, $params);
} catch (\Throwable $e) {
    echo "Signing failed: {$e->getMessage()}\n";
    echo "Is DSS running? Start it with: docker compose up -d --wait\n";
    exit(1);
}

$signed->saveToFile(__DIR__ . '/output/signed.pdf');

echo "Signed document saved to: examples/output/signed.pdf\n";
echo "Size: " . \number_format(\strlen($signed->content)) . " bytes\n";
