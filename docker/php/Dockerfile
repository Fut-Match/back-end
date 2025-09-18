FROM php:8.3.6-fpm

# Definir variáveis de ambiente
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV COMPOSER_HOME=/composer
ENV PATH=$PATH:/composer/vendor/bin

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    zip \
    unzip \
    nginx \
    supervisor \
    libpq-dev \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libwebp-dev \
    libxpm-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        exif \
        pcntl \
        bcmath \
        gd \
        zip \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Configurar PHP
COPY ./docker/php/php.ini /usr/local/etc/php/conf.d/app.ini

# Configurar opcache para produção
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/app.ini \
    && echo "opcache.enable_cli=1" >> /usr/local/etc/php/conf.d/app.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/app.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/app.ini \
    && echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/app.ini \
    && echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/app.ini

# Definir diretório de trabalho
WORKDIR /var/www/html

# Copiar arquivos de dependências primeiro (para cache do Docker)
COPY composer.json composer.lock package.json ./

# Instalar dependências do PHP
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Instalar Node.js e npm para build dos assets
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Instalar dependências do Node.js
RUN npm install

# Copiar resto dos arquivos da aplicação
COPY . .

# Criar diretórios necessários se não existirem
RUN mkdir -p /var/www/html/storage/framework/views \
    && mkdir -p /var/www/html/storage/framework/cache \
    && mkdir -p /var/www/html/storage/framework/sessions \
    && mkdir -p /var/www/html/storage/logs \
    && mkdir -p /var/www/html/bootstrap/cache

# Ajustar permissões - mais robuste
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage \
    && chmod -R 775 /var/www/html/bootstrap/cache \
    && chmod -R 644 /var/www/html/storage/logs \
    && find /var/www/html/storage -type d -exec chmod 775 {} \;

# Executar scripts do composer
RUN composer dump-autoload --optimize

# Build dos assets (se necessário)
RUN npm run build

# Copiar e configurar entrypoint
COPY ./docker/php/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Expor porta 9000 para PHP-FPM
EXPOSE 9000

# Definir entrypoint e comando padrão
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
CMD ["php-fpm"]