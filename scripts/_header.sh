which docker > /dev/null

TAG=php80-xdebug

if [[ ! -z $(docker images -q $TAG) ]]
then
    return
fi

echo "
FROM php:8.0.11-zts-buster

RUN apt update
RUN apt install libffi-dev
RUN docker-php-ext-configure ffi
RUN docker-php-ext-install ffi

RUN apt install -y curl git unzip
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# RUN pecl install pcov
# RUN docker-php-ext-enable pcov
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug
" | docker build -t $TAG -
