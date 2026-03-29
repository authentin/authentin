# eusig-bundle

[![CI](https://github.com/authentin/authentin/actions/workflows/ci.yml/badge.svg)](https://github.com/authentin/authentin/actions/workflows/ci.yml)
[![Latest Version](https://img.shields.io/packagist/v/authentin/eusig-bundle.svg)](https://packagist.org/packages/authentin/eusig-bundle)
[![PHP Version](https://img.shields.io/packagist/php-v/authentin/eusig-bundle.svg)](https://packagist.org/packages/authentin/eusig-bundle)
[![License](https://img.shields.io/packagist/l/authentin/eusig-bundle.svg)](https://packagist.org/packages/authentin/eusig-bundle)

Symfony bundle for [authentin/eusig](https://github.com/authentin/eusig) — adds DI, configuration, and autowiring for eIDAS-compliant electronic signatures.

## Installation

```bash
composer require authentin/eusig-bundle
```

You also need a PSR-18 HTTP client and PSR-17 factories:

```bash
composer require symfony/http-client nyholm/psr7
```

### Prerequisites

A running [EU DSS](https://github.com/esig/dss) instance:

```bash
docker run -d -p 8080:8080 ghcr.io/authentin/dss:latest
```

## Configuration

```yaml
# config/packages/eusig.yaml
eusig:
    dss:
        base_url: '%env(DSS_BASE_URL)%'     # Required. e.g. http://localhost:8080/services/rest

    token:                                    # Optional. Omit if you only need validation.
        type: pkcs12                          # Currently supported: pkcs12
        path: '%env(PKCS12_PATH)%'            # Path to the .p12 keystore file
        password: '%env(PKCS12_PASSWORD)%'    # Keystore password (use env vars!)

    defaults:                                 # Optional. Sensible defaults are provided.
        signature_level: PAdES_BASELINE_B     # Any value from SignatureLevel enum
        digest_algorithm: SHA256              # Any value from DigestAlgorithm enum
```

## Autowired services

The bundle registers these services, available via autowiring:

| Interface | Service | Always available |
|-----------|---------|-----------------|
| `SigningClientInterface` | DSS signing client | Yes |
| `ValidatorInterface` | DSS validator | Yes |
| `TokenInterface` | PKCS#12 token | Only when `token` is configured |
| `SignerInterface` | Signer (signing client + token) | Only when `token` is configured |

## Usage

### Signing a PDF

```php
use Authentin\Eusig\Contract\SignerInterface;
use Authentin\Eusig\Model\Document;
use Authentin\Eusig\Model\SignatureLevel;
use Authentin\Eusig\Model\SignatureParameters;
use Symfony\Component\HttpFoundation\Response;

class SignController
{
    public function __invoke(SignerInterface $signer): Response
    {
        $signed = $signer->sign(
            Document::fromLocalFile('/path/to/document.pdf'),
            new SignatureParameters(signatureLevel: SignatureLevel::PAdES_BASELINE_B),
        );

        return new Response($signed->content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="signed.pdf"',
        ]);
    }
}
```

### Validating a signature

```php
use Authentin\Eusig\Contract\ValidatorInterface;
use Authentin\Eusig\Model\Document;

class ValidateController
{
    public function __invoke(ValidatorInterface $validator): Response
    {
        $result = $validator->validateSignature(
            Document::fromLocalFile('/path/to/signed.pdf'),
        );

        return $this->json([
            'valid' => $result->valid,
            'signatures' => $result->signaturesCount,
        ]);
    }
}
```

### Extending a signature

```php
use Authentin\Eusig\Contract\SigningClientInterface;
use Authentin\Eusig\Model\Document;
use Authentin\Eusig\Model\SignatureLevel;
use Authentin\Eusig\Model\SignatureParameters;

class ExtendController
{
    public function __invoke(SigningClientInterface $signingClient): Response
    {
        $extended = $signingClient->extendDocument(
            Document::fromLocalFile('/path/to/signed.pdf'),
            new SignatureParameters(signatureLevel: SignatureLevel::PAdES_BASELINE_T),
        );

        $extended->saveToFile('/path/to/extended.pdf');

        // ...
    }
}
```

## Custom token

To use a different signing backend (HSM, remote provider), implement `TokenInterface` and register it:

```yaml
# config/services.yaml
services:
    App\Signing\MyHsmToken:
        tags: ['authentin.eusig.token']

    Authentin\Eusig\Contract\TokenInterface:
        alias: App\Signing\MyHsmToken
```

Then omit the `token` section from `eusig.yaml` — the bundle will use your service.

## Standalone usage

For non-Symfony projects, use [authentin/eusig](https://github.com/authentin/eusig) directly.

## License

MIT
