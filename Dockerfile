FROM php:8.4.6-apache AS base

RUN a2enmod rewrite headers
RUN docker-php-ext-install pdo_mysql
RUN sed -i "s/AllowOverride None/AllowOverride All/g" /etc/apache2/apache2.conf
RUN echo "display_errors = Off\nlog_errors = On\nerror_log = /proc/1/fd/2" > /usr/local/etc/php/conf.d/errors.ini


USER www-data
COPY src/cdn/ /var/www/html/cdn/
USER root
RUN chown -R www-data:www-data /var/www/html/cdn/
RUN chmod -R 777 /var/www/html/cdn/

USER www-data
COPY src/web/ /var/www/html/web/
COPY src/web/.htaccess.disabled /var/www/html/web/.htaccess
RUN ln -s /var/www/html/web/assets/ /var/www/html/assets
RUN find /var/www/html/web/ -name "*.html" -exec ln -s {} /var/www/html/ \;
USER root
RUN chown -R www-data:www-data /var/www/html/web/
RUN chmod -R 777 /var/www/html/web/

USER www-data
COPY src/server-emulator/ /var/www/html/server-emulator/
USER root
RUN chown -R www-data:www-data /var/www/html/server-emulator/
RUN chmod -R 777 /var/www/html/server-emulator/


EXPOSE 80
