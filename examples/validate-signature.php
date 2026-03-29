<?php

declare(strict_types=1);

/**
 * Validate a signed PDF document.
 *
 * Prerequisites:
 *   - DSS running at http://localhost:8080 (docker compose up -d --wait)
 *   - A signed PDF (run sign-pdf.php first, or provide your own)
 *
 * Usage: php examples/validate-signature.php [path-to-signed.pdf]
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Authentin\Eusig\Dss\DssClient;
use Authentin\Eusig\Dss\DssValidator;
use Authentin\Eusig\Model\Document;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Psr18Client;

$dssBaseUrl = \getenv('DSS_BASE_URL') ?: 'http://localhost:8080/services/rest';
$pdfPath = $argv[1] ?? __DIR__ . '/output/signed.pdf';

if (!\file_exists($pdfPath)) {
    echo "File not found: {$pdfPath}\n";
    echo "Run sign-pdf.php first, or provide a path: php validate-signature.php /path/to/signed.pdf\n";
    exit(1);
}

// 1. Create the DSS validator
$psr17 = new Psr17Factory();
$dssClient = new DssClient(new Psr18Client(), $psr17, $psr17, $dssBaseUrl);
$validator = new DssValidator($dssClient);

// 2. Load and validate
$document = Document::fromLocalFile($pdfPath);

echo "Validating {$document->filename}...\n\n";

$result = $validator->validateSignature($document);

// 3. Print results
echo "Signatures found: {$result->signaturesCount}\n";
echo "Valid signatures: {$result->validSignaturesCount}\n";
echo "Overall valid:    " . ($result->valid ? 'YES' : 'NO') . "\n\n";

foreach ($result->signatures as $i => $sig) {
    echo "Signature #" . ($i + 1) . ":\n";
    echo "  Indication: {$sig->indication}\n";

    if (null !== $sig->subIndication) {
        echo "  Sub-indication: {$sig->subIndication}\n";
    }

    if (null !== $sig->signedBy) {
        echo "  Signed by: {$sig->signedBy}\n";
    }

    if (null !== $sig->signatureLevel) {
        echo "  Level: {$sig->signatureLevel}\n";
    }

    if (null !== $sig->signingTime) {
        echo "  Signing time: " . $sig->signingTime->format('Y-m-d H:i:s') . "\n";
    }

    echo "\n";
}

// Note: Documents signed with a self-signed test certificate will show
// TOTAL_FAILED/SIG_CRYPTO_FAILURE. This is expected — DSS requires the
// signing certificate to be trusted by the EU Trusted Service List (TSL)
// for TOTAL_PASSED. The signature itself is cryptographically valid.
