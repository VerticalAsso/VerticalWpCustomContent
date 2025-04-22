FROM php:8.2-cli AS base

# https://www.digitalocean.com/community/tutorials/how-to-install-and-use-composer-on-debian-10
RUN apt-get update -y
RUN apt-get install -y      \
            curl            \
            # php-cli         \
            # php-mbstring    \
            git             \
            unzip
WORKDIR /tmp
RUN curl -sS https://getcomposer.org/installer -o composer-setup.php
RUN php composer-setup.php --install-dir=/usr/local/bin --filename=composer

# Install rector : https://github.com/rectorphp/rector
RUN composer global require rector/rector

# Make Composer's global bin directory available in PATH
ENV PATH="/root/.composer/vendor/bin:${PATH}"

WORKDIR /usr/src/themes
CMD [ "bash" ]
