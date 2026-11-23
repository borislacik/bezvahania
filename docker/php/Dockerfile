# Local development image: PHP-FPM 8.4 + Composer + Node LTS + Playwright (Chromium).
# Production (Websupport) does not use this image; it only mirrors the PHP version and extensions.
FROM php:8.4-fpm-bookworm

ARG NODE_MAJOR=26
ARG PLAYWRIGHT_VERSION=1.65.1

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    COMPOSER_HOME=/composer \
    PLAYWRIGHT_BROWSERS_PATH=/ms-playwright \
    TZ=Europe/Bratislava

# System packages
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        git unzip curl ca-certificates gnupg mariadb-client rsync openssh-client tzdata \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions (same set as required on the hosting)
COPY --from=mlocati/php-extension-installer:2 /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions intl zip gd bcmath pdo_mysql exif pcntl sockets pcov opcache

# Composer 2
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Node.js LTS
RUN curl -fsSL https://deb.nodesource.com/setup_${NODE_MAJOR}.x | bash - \
    && apt-get install -y --no-install-recommends nodejs \
    && rm -rf /var/lib/apt/lists/*

# Playwright Chromium + OS dependencies for Pest browser tests.
# Keep PLAYWRIGHT_VERSION in sync with the "playwright" version in package.json.
RUN npx -y playwright@${PLAYWRIGHT_VERSION} install --with-deps chromium \
    && rm -rf /var/lib/apt/lists/* /root/.npm

COPY php.ini /usr/local/etc/php/conf.d/zz-app.ini

WORKDIR /var/www/html

# The project is bind-mounted from Windows; let git inside the container work with it.
RUN git config --system --add safe.directory /var/www/html \
    && git config --system core.fileMode false
