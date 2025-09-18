#!/bin/bash

# Script de entrypoint para o container PHP
# Garante que as permissões sejam aplicadas corretamente

set -e

echo "==> Configurando permissões..."

# Criar diretórios necessários se não existirem
mkdir -p /var/www/html/storage/framework/views
mkdir -p /var/www/html/storage/framework/cache  
mkdir -p /var/www/html/storage/framework/sessions
mkdir -p /var/www/html/storage/logs
mkdir -p /var/www/html/bootstrap/cache

# Aplicar permissões corretas
chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
chmod -R 775 /var/www/html/storage
chmod -R 775 /var/www/html/bootstrap/cache

# Garantir que diretórios sejam executáveis e arquivos sejam legíveis
find /var/www/html/storage -type d -exec chmod 775 {} \;
find /var/www/html/storage -type f -exec chmod 664 {} \;
find /var/www/html/bootstrap/cache -type d -exec chmod 775 {} \;
find /var/www/html/bootstrap/cache -type f -exec chmod 664 {} \;

echo "==> Permissões configuradas com sucesso!"

# Executar o comando original
exec "$@"