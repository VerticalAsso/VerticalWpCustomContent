# Start from the official WordPress image
FROM wordpress:latest

# Copy your wp-content folder into the image
COPY BackedUpContent/wp-content     /var/www/html/wp-content
COPY BackedUpContent/wp-config.php  /var/www/html/wp-config.php

# Set proper permissions (optional but recommended)
RUN chown -R www-data:www-data /var/www/html/wp-content

# We're going to use this path multile times. So save it in a variable.
ARG XDEBUG_INI="/usr/local/etc/php/conf.d/xdebug.ini"

# Install AND configure Xdebug
RUN pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && echo "[xdebug]" > $XDEBUG_INI \
    && echo "xdebug.mode = debug" >> $XDEBUG_INI \
    && echo "xdebug.start_with_request = trigger" >> $XDEBUG_INI \
    && echo "xdebug.client_port = 9003" >> $XDEBUG_INI \
    && echo "xdebug.client_host = 'host.docker.internal'" >> $XDEBUG_INI \
    && echo "xdebug.log = /tmp/xdebug.log" >> $XDEBUG_INI