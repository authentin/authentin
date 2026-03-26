ARG DSS_VERSION=6.4

# TODO: pin base images by SHA256 digest for reproducible builds
FROM maven:3.9.12-eclipse-temurin-25 AS build

ARG DSS_VERSION

RUN set -eux; \
    echo "${DSS_VERSION}" | grep -qE '^[0-9]+\.[0-9]+(\.[0-9]+)?$' \
    || { echo "Invalid DSS_VERSION: ${DSS_VERSION}"; exit 1; }; \
    git clone --depth 1 --branch "${DSS_VERSION}" https://github.com/esig/dss-demonstrations.git /build

WORKDIR /build

RUN mvn clean package \
    --strict-checksums \
    -pl dss-standalone-app,dss-standalone-app-package,dss-demo-webapp \
    -P quick,linux \
    -DskipTests

# TODO: pin base image by SHA256 digest for reproducible builds
FROM tomcat:11.0.18-jdk25-temurin

LABEL org.opencontainers.image.source="https://github.com/authentin/dss" \
      org.opencontainers.image.description="Community Docker image for EU DSS (Digital Signature Service)" \
      org.opencontainers.image.licenses="MIT"

COPY --from=build /build/dss-demo-webapp/target/dss-demo-webapp-*.war /usr/local/tomcat/webapps/ROOT.war

EXPOSE 8080

HEALTHCHECK --interval=10s --timeout=5s --retries=30 --start-period=60s \
    CMD wget -q --spider http://localhost:8080/services/rest/server-signing/keys || exit 1
