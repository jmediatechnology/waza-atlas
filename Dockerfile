# syntax=docker/dockerfile:1.7

# Waza Atlas on FrankenPHP (Caddy + PHP in one process).
#   dev  target: source is bind-mounted, debug on, vendor installed at start-up
#   prod target: code and vendor baked in, opcache locked, runs as a non-root user

ARG PHP_VERSION=8.4

######## base ########
FROM dunglas/frankenphp:1-php${PHP_VERSION} AS frankenphp_base

WORKDIR /app

# acl lets www-data and root share var/; file/git/unzip are what Composer needs.
RUN apt-get update && apt-get install -y --no-install-recommends \
        acl \
        file \
        libcap2-bin \
        git \
        unzip \
    && rm -rf /var/lib/apt/lists/*

# pdo_sqlite ships with the official PHP images. pdo_pgsql and pdo_mysql are here so
# DATABASE_URL can point at a server database without rebuilding the image.
RUN set -eux; \
    install-php-extensions \
        apcu \
        intl \
        opcache \
        pdo_mysql \
        pdo_pgsql \
        zip \
    ;

ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --link --from=composer/composer:2-bin /composer /usr/bin/composer

COPY --link docker/frankenphp/conf.d/10-app.ini $PHP_INI_DIR/conf.d/
COPY --link --chmod=755 docker/frankenphp/docker-entrypoint.sh /usr/local/bin/docker-entrypoint
COPY --link docker/frankenphp/Caddyfile /etc/frankenphp/Caddyfile

ENTRYPOINT ["docker-entrypoint"]

# Caddy's admin endpoint answers whatever SERVER_NAME is. For an app-level check that
# includes the database, point your monitoring at https://<host>/healthz.
HEALTHCHECK --start-period=60s --interval=30s --timeout=5s --retries=3 \
    CMD curl -fsS http://localhost:2019/metrics >/dev/null || exit 1

CMD ["frankenphp", "run", "--config", "/etc/frankenphp/Caddyfile"]

######## dev ########
FROM frankenphp_base AS frankenphp_dev

ENV APP_ENV=dev
RUN mv "$PHP_INI_DIR/php.ini-development" "$PHP_INI_DIR/php.ini"
COPY --link docker/frankenphp/conf.d/20-app.dev.ini $PHP_INI_DIR/conf.d/

# The project is bind-mounted and opcache revalidates on every request, so edits show up on refresh.
# The database stays in ./var/data.db and uploads in ./var/motions, next to your code.

######## prod ########
FROM frankenphp_base AS frankenphp_prod

ENV APP_ENV=prod \
    DATABASE_URL="sqlite:////srv/storage/app.db" \
    MOTION_DIR=/srv/storage/motions
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
COPY --link docker/frankenphp/conf.d/20-app.prod.ini $PHP_INI_DIR/conf.d/

# Dependencies first, so editing app code does not reinstall them.
COPY --link composer.* ./
RUN set -eux; \
    composer install --no-cache --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress

COPY --link . ./
RUN set -eux; \
    rm -rf docker tests phpunit.dist.xml .env.test; \
    mkdir -p var/cache var/log; \
    composer dump-autoload --classmap-authoritative --no-dev; \
    APP_SECRET=build-only-placeholder php bin/console cache:clear --no-warmup; \
    APP_SECRET=build-only-placeholder php bin/console cache:warmup; \
    chmod +x bin/console; sync

# Database and uploaded motions live here; compose mounts a named volume on it.
RUN mkdir -p /srv/storage/motions

# FrankenPHP needs to bind to 80/443 and write Caddy's data as a non-root user.
RUN set -eux; \
    useradd --no-create-home --uid 10001 --user-group app; \
    setcap CAP_NET_BIND_SERVICE=+eip /usr/local/bin/frankenphp; \
    chown -R app:app /data/caddy /config/caddy /srv/storage var
USER app
