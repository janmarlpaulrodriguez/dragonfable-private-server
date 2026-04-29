FROM php:8.4.6-apache AS base

RUN a2enmod rewrite headers
RUN docker-php-ext-install pdo_mysql
RUN sed -i "s/AllowOverride None/AllowOverride All/g" /etc/apache2/apache2.conf
RUN echo "display_errors = Off\nlog_errors = On\nerror_log = /proc/1/fd/2" > /usr/local/etc/php/conf.d/errors.ini


USER www-data
COPY src/cdn/ /var/www/html/cdn/
COPY src/web/ /var/www/html/
USER root
RUN rm -f /var/www/html/.htaccess.disabled
RUN chown -R www-data:www-data /var/www/html/
RUN chmod -R 777 /var/www/html/

USER www-data
COPY src/server-emulator/ /var/www/html/server-emulator/
USER root
RUN chown -R www-data:www-data /var/www/html/server-emulator/
RUN chmod -R 777 /var/www/html/server-emulator/


EXPOSE 80
