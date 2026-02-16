############################################
# Base Image
############################################

# Learn more about the Server Side Up PHP Docker Images at:
# https://serversideup.net/open-source/docker-php/
FROM serversideup/php:8.4-frankenphp AS base

# Switch to root before installing our PHP extensions
USER root
RUN install-php-extensions bcmath gd

############################################
# Development Image
############################################
FROM base AS development

# We can pass USER_ID and GROUP_ID as build arguments
# to ensure the www-data user has the same UID and GID
# as the user running Docker.
ARG USER_ID=1000
ARG GROUP_ID=1000

# Switch to root so we can set the user ID and group ID
USER root
RUN docker-php-serversideup-set-id www-data $USER_ID:$GROUP_ID  && \
    docker-php-serversideup-set-file-permissions --owner $USER_ID:$GROUP_ID --service nginx

# Switch back to the unprivileged www-data user
USER www-data

############################################
# CI image
############################################
FROM base AS ci

# Sometimes CI images need to run as root
# so we set the ROOT user and configure
# the PHP-FPM pool to run as www-data
USER root
RUN echo "user = www-data" >> /usr/local/etc/php-fpm.d/docker-php-serversideup-pool.conf && \
    echo "group = www-data" >> /usr/local/etc/php-fpm.d/docker-php-serversideup-pool.conf

############################################
# Production Image
############################################
FROM base AS deploy

USER root

# Install Node.js for building assets
# RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - && \
#     apt-get install -y nodejs && \
#     apt-get clean && rm -rf /var/lib/apt/lists/*

COPY --chown=www-data:www-data . /var/www/html
COPY --chown=www-data:www-data .env.example /var/www/html/.env

# Copy extra configuration files
COPY docker/scripts/etc/entrypoint.d /etc/entrypoint.d
RUN chmod 755 /etc/entrypoint.d/49-laravel-automations.sh

# Switch to www-data user
USER www-data

# Install production dependencies and clean up
RUN composer install --optimize-autoloader