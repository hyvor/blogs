###################################################
# Alias for deppendencies
FROM node:22.12.0 AS node
FROM composer:2.8.4 AS composer
FROM php:8.3-fpm AS php-fpm
FROM mlocati/php-extension-installer:2.7.13 AS php-extension-installer
#FROM caddy:2.8.4 AS caddy

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
FROM php-fpm AS backend-base

WORKDIR /app/backend

# install php and dependencies
COPY --from=composer /usr/bin/composer /usr/local/bin/composer
COPY --from=php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
RUN install-php-extensions bcmath intl pcntl zip pdo_pgsql

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
RUN composer install --no-interaction

# set up code and install composer packages
COPY backend /app/backend/

# link the internal package locally
COPY internal /app/backend/packages/internal
RUN composer require hyvor/internal:@dev

EXPOSE 36202
CMD php artisan serve --host=0.0.0.0 --port=36202

###################################################
#FROM backend-base AS backend-prod
#
## supervisor & caddy
#RUN apt update && apt install -y supervisor
#COPY --from=caddy /usr/bin/caddy /usr/bin/caddy
#
## copy files
#COPY backend /app/backend
#COPY locales /app/locales
#COPY --from=frontend-prod /frontend/build /app/static
#
## install composer
#RUN cd backend && composer install --no-interaction --no-dev --optimize-autoloader
#
## copy configs
#COPY meta/image/Caddyfile /etc/caddy/Caddyfile
#COPY meta/image/php.ini /usr/local/etc/php/conf.d/app.ini
#COPY meta/image/php-fpm.conf /usr/local/etc/php-fpm.conf
#COPY meta/image/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
#COPY meta/image/run /app/run
#
## laravel directories
#RUN touch /app/backend/storage/logs/laravel.log \
#    && chown -R www-data:www-data /app/backend/storage /app/backend/bootstrap/cache /var/www \
#    && chmod -R 775 /app/backend/storage /app/backend/bootstrap/cache
#
#EXPOSE 80
#CMD ["/app/run"]
#
####################################################
#FROM backend-prod AS e2e-dev
#
## install dependencies
#COPY e2e/package.json e2e/package-lock.json e2e/playwright.config.ts /app/e2e/
#COPY internal /app/backend/packages/internal
#RUN cd /app/e2e \
#    && npm install \
#    && npm run install:chromium
#
## install all composer packages
## we need dev dependencies for testing
#RUN cd /app/backend  \
#    && composer install --no-interaction \
#    && composer require hyvor/internal:@dev
#
## copy code
#COPY e2e/tests /app/e2e/tests
#COPY e2e/e2e.supervisor.conf /etc/supervisor/conf.d/e2e.conf
#
## we need frontend code to run tests
#COPY --from=frontend-base /frontend /app/frontend
#RUN cd /app/frontend && npm install

#ENV DB_DATABASE=hyvor_e2e
#ENV E2E=true
#
#CMD ["/app/run"]
