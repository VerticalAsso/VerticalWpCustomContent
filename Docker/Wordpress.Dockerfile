# Start from the official WordPress image
FROM wordpress:latest AS wordpress-vertical

# Copy your wp-content folder into the image
COPY BackedUpContent/wp-content     /var/www/html/wp-content
COPY BackedUpContent/wp-config.php  /var/www/html/wp-config.php

# Set proper permissions (optional but recommended)
RUN chown -R www-data:www-data /var/www/html/wp-content