FROM ubuntu:22.04

RUN apt-get -y update
RUN DEBIAN_FRONTEND=noninteractive TZ=Etc/UTC \
    apt-get install -y \
    git \
    curl \
    openssh-client \
    ca-certificates \
    php8.1 \
    php8.1-bcmath \
    php8.1-ctype \
    php8.1-curl \
    php8.1-dom \
    php8.1-fpm \
    php8.1-gd \
    php8.1-intl \
    php8.1-mbstring \
    php8.1-pgsql \
    php8.1-opcache \
    php8.1-phar \
    php8.1-xml \
    php8.1-xmlreader \
    php8.1-pdo \
    php8.1-simplexml \
    php8.1-intl \
    php8.1-fileinfo \
    php8.1-tokenizer \
    php8.1-xmlwriter \
    php8.1-posix \
    php8.1-zip \
    supervisor \
    make

# install caddy
RUN apt install -y debian-keyring debian-archive-keyring apt-transport-https && \
    curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg && \
    curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | tee /etc/apt/sources.list.d/caddy-stable.list && \
    apt update && \
    apt install caddy

COPY docker/image/Caddyfile /etc/caddy/Caddyfile

COPY docker/image/php-fpm.conf /etc/php/8.1/fpm/pool.d/www.conf
COPY docker/image/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# update PHP ini
RUN sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 128M/g' /etc/php/8.1/fpm/php.ini && \
    sed -i 's/post_max_size = 8M/post_max_size = 140M/g' /etc/php/8.1/fpm/php.ini && \
    sed -i 's/memory_limit = 128M/memory_limit = 256M/g' /etc/php/8.1/fpm/php.ini

COPY ./ /var/www/app

### BACKEND

# install composer
RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php && \
    php /tmp/composer-setup.php --install-dir=/usr/bin --filename=composer && \
    rm /tmp/composer-setup.php

# composer install
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader --no-dev --no-interaction --no-progress --working-dir=/var/www/app/backend

# laravel directories
RUN chmod -R 777 /var/www/app/backend/storage /var/www/app/backend/bootstrap/cache


### FRONTEND

# install node
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs

# build front-end
ENV NODE_OPTIONS="--max-old-space-size=4096" 
RUN npm install --prefix /var/www/app/frontend && \
    npm run build --prefix /var/www/app/frontend

# delete all in /var/www/app/frontend except build folder
RUN find /var/www/app/frontend -mindepth 1 -maxdepth 1 ! -name build -exec rm -rf {} \;

EXPOSE 80

CMD ["/usr/bin/supervisord"]