FROM php:8.2-apache

# Habilita o módulo mod_env do Apache para garantir a passagem de variáveis
RUN a2enmod env

COPY . /var/www/html/

# Garante que o servidor web consegue ler e escrever nos ficheiros dos sensores
RUN chown -R www-data:www-data /var/www/html/

EXPOSE 80