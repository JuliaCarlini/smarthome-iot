FROM php:8.2-apache

# Esta linha força o Apache a passar todas as variáveis de ambiente (como o APP_USERS) para o PHP
RUN sed -i 's/VariablesOrder = "GPCS"/VariablesOrder = "EGPCS"/g' "$PHP_INI_DIR/php.ini-production" \
    && cp "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

COPY . /var/www/html/

# Dar permissões para a API conseguir criar as pastas dos sensores no Render
RUN chown -W www-data:www-data /var/www/html/

EXPOSE 80