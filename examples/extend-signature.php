<?php

declare(strict_types=1);

/**
 * Extend a signature level (e.g. B-B → B-T by adding a timestamp).
 *
 * Prerequisites:
 *   - DSS running at http://localhost:8080 (docker compose up -d --wait)
 *   - A signed PDF at B-B level (run sign-pdf.php first)
 *
 * Usage: php examples/extend-signature.php [path-to-signed.pdf]
 *
 * Note: Extending to B-T requires DSS to have access to a TSA (Time Stamping Authority).
 * The DSS demo webapp includes a default TSA configuration. If the TSA is unreachable,
 * the extension will fail — this is expected in offline environments.
 */

require_once __DIR__ . '/../vendor/autoload.php';

use Authentin\Eusig\Dss\DssClient;
use Authentin\Eusig\Dss\DssSigningClient;
use Authentin\Eusig\Model\Document;
use Authentin\Eusig\Model\SignatureLevel;
use Authentin\Eusig\Model\SignatureParameters;
use Nyholm\Psr7\Factory\Psr17Factory;
use Symfony\Component\HttpClient\Psr18Client;

$dssBaseUrl = \getenv('DSS_BASE_URL') ?: 'http://localhost:8080/services/rest';
$pdfPath = $argv[1] ?? __DIR__ . '/output/signed.pdf';

if (!\file_exists($pdfPath)) {
    echo "File not found: {$pdfPath}\n";
    echo "Run sign-pdf.php first, or provide a path: php extend-signature.php /path/to/signed.pdf\n";
    exit(1);
}

// 1. Create the DSS signing client
$psr17 = new Psr17Factory();
$dssClient = new DssClient(new Psr18Client(), $psr17, $psr17, $dssBaseUrl);
$signingClient = new DssSigningClient($dssClient);

// 2. Load the signed document
$document = Document::fromLocalFile($pdfPath);

echo "Extending {$document->filename} from PAdES-BASELINE-B to PAdES-BASELINE-T...\n";

// 3. Extend to B-T (adds a trusted timestamp)
$params = new SignatureParameters(
    signatureLevel: SignatureLevel::PAdES_BASELINE_T,
);

try {
    $extended = $signingClient->extendDocument($document, $params);
    $extended->saveToFile(__DIR__ . '/output/extended.pdf');

    echo "Extended document saved to: examples/output/extended.pdf\n";
    echo "Size: " . \number_format(\strlen($extended->content)) . " bytes\n";
} catch (\Throwable $e) {
    echo "Extension failed: {$e->getMessage()}\n";
    echo "This is expected if the DSS instance cannot reach a TSA.\n";
    exit(1);
}
