FROM php:7.2-apache

RUN docker-php-ext-install pdo_mysql

RUN a2enmod rewrite
RUN a2enmod headers
RUN service apache2 restart

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf
