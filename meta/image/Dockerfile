FROM ubuntu:22.04


RUN apt-get update && \
    apt-get install -y software-properties-common && \
    rm -rf /var/lib/apt/lists/*
RUN add-apt-repository -y ppa:ondrej/php


RUN DEBIAN_FRONTEND=noninteractive TZ=Etc/UTC \
    apt-get install -y \
    git \
    curl \
    openssh-client \
    ca-certificates \
    php8.3 \
    php8.3-bcmath \
    php8.3-ctype \
    php8.3-curl \
    php8.3-dom \
    php8.3-fpm \
    php8.3-gd \
    php8.3-intl \
    php8.3-mbstring \
    php8.3-mysqli \
    php8.3-pgsql \
    php8.3-opcache \
    php8.3-phar \
    php8.3-xml \
    php8.3-xmlreader \
    php8.3-pdo \
    php8.3-simplexml \
    php8.3-intl \
    php8.3-fileinfo \
    php8.3-tokenizer \
    php8.3-xmlwriter \
    php8.3-posix \
    php8.3-zip \
    supervisor \
    make

# install caddy
RUN apt install -y debian-keyring debian-archive-keyring apt-transport-https && \
    curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/gpg.key' | gpg --dearmor -o /usr/share/keyrings/caddy-stable-archive-keyring.gpg && \
    curl -1sLf 'https://dl.cloudsmith.io/public/caddy/stable/debian.deb.txt' | tee /etc/apt/sources.list.d/caddy-stable.list && \
    apt update && \
    apt install caddy

COPY meta/image/Caddyfile /etc/caddy/Caddyfile

COPY meta/image/php-fpm.conf /etc/php/8.3/fpm/pool.d/www.conf
COPY meta/image/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# update PHP ini
RUN sed -i 's/upload_max_filesize = 2M/upload_max_filesize = 128M/g' /etc/php/8.3/fpm/php.ini && \
    sed -i 's/post_max_size = 8M/post_max_size = 140M/g' /etc/php/8.3/fpm/php.ini && \
    sed -i 's/memory_limit = 128M/memory_limit = 256M/g' /etc/php/8.3/fpm/php.ini

COPY ./ /var/www/app

### BACKEND

# install composer
RUN curl -sS https://getcomposer.org/installer -o /tmp/composer-setup.php && \
    php /tmp/composer-setup.php --install-dir=/usr/bin --filename=composer && \
    rm /tmp/composer-setup.php

# composer install
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --optimize-autoloader --no-dev --no-interaction --no-progress --working-dir=/var/www/app/backend


# laravel directories
RUN touch /var/www/app/backend/storage/logs/laravel.log
RUN chmod -R 777 /var/www/app/backend/storage /var/www/app/backend/bootstrap/cache

### FRONTEND

# install node
RUN curl -sL https://deb.nodesource.com/setup_18.x | bash -
RUN apt-get install -y nodejs

# build front-end
ENV NODE_OPTIONS="--max-old-space-size=4096" 
RUN npm install --prefix /var/www/app/frontend && \
    npm run build --prefix /var/www/app/frontend

# backend npm packages (shiki)
RUN npm install --prefix /var/www/app/backend

# delete all in /var/www/app/frontend except build folder
RUN find /var/www/app/frontend -mindepth 1 -maxdepth 1 ! -name build -exec rm -rf {} \;

EXPOSE 80 443

CMD ["/usr/bin/supervisord"]
