# authentin/authentin

Monorepo for the **eusig** PHP libraries — eIDAS-compliant electronic signatures via the [EU DSS](https://github.com/esig/dss) REST API.

| Package | Description | Packagist |
|---------|-------------|-----------|
| [eusig](packages/eusig/) | Standalone PHP library | [![Latest Version](https://img.shields.io/packagist/v/authentin/eusig.svg)](https://packagist.org/packages/authentin/eusig) |
| [eusig-bundle](packages/eusig-bundle/) | Symfony bundle (DI, config, autowiring) | [![Latest Version](https://img.shields.io/packagist/v/authentin/eusig-bundle.svg)](https://packagist.org/packages/authentin/eusig-bundle) |

Read-only split repos: [authentin/eusig](https://github.com/authentin/eusig) and [authentin/eusig-bundle](https://github.com/authentin/eusig-bundle).

## Development

### Requirements

- PHP 8.2+
- Docker (for the EU DSS instance)

### Setup

```bash
git clone https://github.com/authentin/authentin.git
cd authentin
docker compose up -d --wait
composer install
```

### Running tests

```bash
# All tests (unit + integration, requires DSS running)
vendor/bin/phpunit

# Static analysis
vendor/bin/phpstan analyse

# Code style
vendor/bin/php-cs-fixer fix --dry-run --diff
```

### Running examples

Generate test fixtures and run the example scripts:

```bash
php examples/fixtures/generate-fixtures.php
php examples/sign-pdf.php
php examples/validate-signature.php
php examples/extend-signature.php
```

Examples require DSS running at `http://localhost:8080` (configurable via `DSS_BASE_URL` env var).

## Project structure

```
packages/
  eusig/            Standalone PHP library (split → authentin/eusig)
  eusig-bundle/     Symfony bundle (split → authentin/eusig-bundle)
examples/           Standalone usage examples (monorepo only, not shipped with packages)
```

## Contributing

1. Fork this monorepo (`authentin/authentin`)
2. Create a feature branch
3. Make your changes — all development happens here
4. Ensure tests pass: `vendor/bin/phpunit`
5. Ensure code style: `vendor/bin/php-cs-fixer fix`
6. Ensure static analysis: `vendor/bin/phpstan analyse`
7. Submit a PR against `main`

Changes are automatically split to the read-only package repositories on merge.

> Do **not** submit PRs directly to `authentin/eusig` or `authentin/eusig-bundle` — they are read-only mirrors.

## License

MIT
