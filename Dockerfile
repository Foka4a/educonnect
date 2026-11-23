# EduConnect: PHP 8.3 + Apache servindo o projeto inteiro (frontend/ e backend/ na mesma origem).
FROM php:8.3-apache

# pdo_mysql para o MySQL do docker compose (pdo_sqlite já vem na imagem)
RUN docker-php-ext-install pdo_mysql \
    && a2enmod rewrite

# Apache lê os .htaccess do projeto (bloqueio de config, lib, acoes e storage)
RUN sed -ri 's#AllowOverride None#AllowOverride All#g' /etc/apache2/apache2.conf

COPY docker/php.ini /usr/local/etc/php/conf.d/educonnect.ini

WORKDIR /var/www/html
COPY . .

# storage/ precisa ser gravável pelo Apache (uploads e, sem MySQL, o banco SQLite)
RUN mkdir -p backend/storage/uploads && chown -R www-data:www-data backend/storage
