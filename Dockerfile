###################################################
# Alias for deppendencies
FROM node:22.12.0 AS node
FROM composer:2.8.4 AS composer
FROM dunglas/frankenphp:1.10.0-php8.4.15 AS frankenphp

###################################################
################  FRONTEND STAGES  ################
###################################################

###################################################
FROM node AS frontend-base
WORKDIR /app/frontend
# install dependencies
COPY frontend/package.json frontend/package-lock.json \
    frontend/svelte.config.js \
    frontend/vite.config.ts \
    frontend/tsconfig.json /app/frontend/
# copy code
COPY frontend/src /app/frontend/src
COPY frontend/static /app/frontend/static

###################################################
FROM frontend-base AS frontend-dev
EXPOSE 36201
RUN npm install
RUN if [ -d "src/design" ]; then cd src/design && npm link && cd ../.. && npm link @hyvor/design; fi
CMD npm run dev

###################################################
FROM frontend-base AS frontend-prod
# build the frontend
RUN  npm install \
    && npm run build \
    && find . -maxdepth 1 -not -name build -not -name . -exec rm -rf {} \;


###################################################
################  BACKEND STAGES  #################
###################################################


###################################################
FROM frankenphp AS backend-base

WORKDIR /app/backend

# install php and dependencies
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
RUN install-php-extensions bcmath intl pcntl zip pdo_pgsql gd opcache apcu

# install npm and dependencies
COPY --from=node /usr/local/include/node /usr/local/include/node
COPY --from=node /usr/local/lib/node_modules /usr/local/lib/node_modules
COPY --from=node /usr/local/bin/node /usr/local/bin/node
RUN ln -s /usr/local/lib/node_modules/npm/bin/npm-cli.js /usr/local/bin/npm

# install npm dependencies (shiki)
COPY backend/package.json backend/package-lock.json /app/backend/
RUN npm install


###################################################
FROM backend-base AS backend-dev

# pcov for debugging
RUN install-php-extensions pcov
COPY backend/composer.json backend/composer.lock /app/backend/
RUN composer install --no-interaction \
    && touch ../.env # needed in CI

# set up code and install composer packages
COPY backend /app/backend/
COPY meta/dev/php.dev.ini /usr/local/etc/php/conf.d/app.ini

# use local internal library
RUN if [ -d "packages/internal" ]; then composer require hyvor/internal:@dev; fi

EXPOSE 80
CMD php artisan octane:frankenphp --workers=1 --max-requests=1 --host=0.0.0.0 --port=80

###################################################
FROM backend-base AS final

# supervisor
RUN apt update && apt install -y supervisor

# copy files
COPY backend /app/backend
COPY --from=frontend-prod /app/frontend/build /app/static

# install composer
RUN composer install --no-interaction --no-dev --optimize-autoloader --classmap-authoritative

# copy configs
COPY meta/image/CaddyfileOctane /etc/caddy/Caddyfile
COPY meta/image/php.ini /usr/local/etc/php/conf.d/app.ini
COPY meta/image/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY meta/image/run /app/run

# laravel directories
RUN touch /app/backend/storage/logs/laravel.log \
    && chown -R www-data:www-data /app/backend/storage /app/backend/bootstrap/cache /var/www \
    && chmod -R 775 /app/backend/storage /app/backend/bootstrap/cache

EXPOSE 80
CMD ["/app/run"]
