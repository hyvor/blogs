###################################################
# Alias for deppendencies
FROM node:22.12.0 AS node
FROM composer:2.8.4 AS composer
FROM dunglas/frankenphp:1.11.2-php8.4 AS frankenphp

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
    frontend/.prettier* \
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

# install npm dependencies (shiki) for symfony
COPY symfony/package-lock.json symfony/package.json ./
RUN npm ci

# supervisor
RUN apt update && apt install -y supervisor && rm -rf /var/lib/apt/lists/*


###################################################
FROM backend-base AS backend-dev

# pcov for debugging
RUN install-php-extensions pcov
COPY symfony/composer.json symfony/composer.lock ./
RUN composer install --no-interaction

# set up code and install composer packages
COPY symfony /app/backend/
COPY meta/dev/php.dev.ini /usr/local/etc/php/conf.d/app.ini
COPY meta/dev/supervisord.dev.conf /etc/supervisor/conf.d/supervisord.conf
COPY meta/dev/Caddyfile.dev /etc/caddy/Caddyfile
COPY meta/dev/run.dev /app/run

EXPOSE 80
CMD ["/app/run"]

###################################################
FROM backend-base AS final

# copy files
COPY symfony ./
COPY --from=frontend-prod /app/frontend/build /app/static

# install composer
# create chef user
# set ownership for all runtime-writable directories
RUN composer install --no-interaction --no-dev --optimize-autoloader --classmap-authoritative && \
    composer clear-cache && \
    rm /usr/local/bin/composer && \
    useradd --system --home-dir /var/www --create-home --shell /usr/sbin/nologin chef && \
    mkdir -p /app/backend/var && \
    chown -R chef:chef /app/backend/var

# copy configs
COPY meta/image/Caddyfile /etc/caddy/Caddyfile
COPY meta/image/php.ini /usr/local/etc/php/conf.d/app.ini
COPY meta/image/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY meta/image/run /app/run

USER chef

HEALTHCHECK --interval=30s --timeout=5s --start-period=5s CMD curl -f http://localhost/api/health || exit 1

EXPOSE 80
EXPOSE 443

CMD ["/app/run"]
