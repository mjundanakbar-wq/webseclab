# WebSecLab - Dockerfile
# Image PHP + Apache dengan ekstensi mysqli (koneksi database) dan gd
# (dibutuhkan modul File Upload level Impossible untuk re-encode gambar).

FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libpng-dev \
        libjpeg62-turbo-dev \
        libwebp-dev \
    && docker-php-ext-configure gd --with-jpeg --with-webp \
    && docker-php-ext-install -j"$(nproc)" gd mysqli \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Aktifkan mod_rewrite & mod_authz_core (dipakai .htaccess proyek ini)
RUN a2enmod rewrite authz_core

# Supaya file .htaccess ("Require local") benar-benar dibaca Apache
RUN sed -ri -e 's!AllowOverride None!AllowOverride All!' /etc/apache2/apache2.conf

WORKDIR /var/www/html
