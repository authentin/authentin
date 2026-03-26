# authentin/dss

Community-maintained Docker image for [EU DSS (Digital Signature Service)](https://github.com/esig/dss) — the official European Commission library for creating and validating eIDAS-compliant electronic signatures.

> **Disclaimer**: This is an **unofficial** community image. It is **not** affiliated with, endorsed by, or maintained by the European Commission or the [esig/DSS](https://github.com/esig/dss) project. This image is built from the official open-source [esig/dss-demonstrations](https://github.com/esig/dss-demonstrations) repository. For official DSS information, refer to [github.com/esig/dss](https://github.com/esig/dss). Provided "as-is" under the [MIT license](LICENSE).

## Requirements

- ~2 GB RAM (Java application)
- ~60 seconds cold start

## Quick Start

```bash
docker pull authentin/dss
docker run -d -p 8080:8080 --name dss authentin/dss

# Wait for startup (~60s), then verify:
curl http://localhost:8080/services/rest/server-signing/keys
```

The DSS webapp starts on port 8080. The REST API is available at `http://localhost:8080/services/rest` after startup completes.

A built-in healthcheck monitors readiness automatically. All configuration is baked at build time — there are no runtime environment variables.

## Registries

```bash
# Docker Hub
docker pull authentin/dss

# GitHub Container Registry
docker pull ghcr.io/authentin/dss
```

## Available Tags

| Tag | Description |
|-----|-------------|
| `latest` | Latest stable DSS release |
| `6.4` | DSS 6.4 release |
| `6.4-20260326` | Dated build snapshot (format: `VERSION-YYYYMMDD`) |

## Docker Compose

```yaml
services:
    dss:
        image: authentin/dss:latest
        ports:
            - '8080:8080'
```

Use `docker compose up -d --wait` to block until the healthcheck passes and DSS is ready to accept requests.

## Building Locally

> **Note:** The build compiles DSS from source and may take 10+ minutes.

```bash
# Default version
docker build -t authentin/dss .

# Specific DSS version
docker build --build-arg DSS_VERSION=6.4 -t authentin/dss:6.4 .
```

## Links

- [EU DSS Project](https://github.com/esig/dss)
- [DSS Demonstrations](https://github.com/esig/dss-demonstrations)
- [Authentin](https://github.com/authentin)
- [authentin/eusig](https://github.com/authentin/eusig) — PHP client library for the DSS REST API

## License

MIT — see [LICENSE](LICENSE).
