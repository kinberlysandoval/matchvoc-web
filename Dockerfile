FROM ubuntu:22.04

ENV DEBIAN_FRONTEND=noninteractive

RUN apt-get update && apt-get install -y \
    apache2 \
    php8.1 \
    php8.1-mysqli \
    php8.1-pdo \
    libapache2-mod-php8.1 \
    && apt-get clean

RUN a2enmod php8.1 rewrite

RUN rm -rf /var/www/html/*

COPY . /var/www/html/

RUN chown -R www-data:www-data /var/www/html

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

RUN sed -i "s/Listen 80/Listen \${PORT}/" /etc/apache2/ports.conf && \
    sed -i "s/*:80>/*:\${PORT}>/" /etc/apache2/sites-enabled/000-default.conf

EXPOSE 80

CMD ["/bin/bash", "-c", "source /etc/apache2/envvars && apache2 -D FOREGROUND"]