FROM php:7.1-fpm

# install the PHP extensions we need
RUN apt-get update \
    && apt-get install -y libpng12-dev libjpeg-dev zlib1g-dev \
    && apt-get install -y curl git unzip \
	&& docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr \
	&& docker-php-ext-install pdo pdo_mysql gd mysqli opcache zip

# install nodejs
RUN curl -sL https://deb.nodesource.com/setup_7.x | bash -
RUN apt-get install -y nodejs

# delete all apt lists
RUN rm -rf /var/lib/apt/lists/*

# mount web directory
VOLUME /var/www/html

COPY docker-entrypoint.sh /entrypoint.sh

# ENTRYPOINT resets CMD now
ENTRYPOINT ["/entrypoint.sh"]
CMD ["php-fpm"]
