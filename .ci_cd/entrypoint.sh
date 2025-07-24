#!/bin/bash

if [ ! -d /var/www/html/vendor ]; then
    echo "Instalando as dependências do PHP via Composer..."
    composer install --no-interaction --prefer-dist
fi

echo "Iniciando Apache..."
exec apache2-foreground