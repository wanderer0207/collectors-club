FROM php:8.2-apache

# 必要な拡張機能（PostgreSQL用など）のインストール
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# ソースコードをApacheの公開ディレクトリにコピー
COPY . /var/www/html/

# 権限の設定
RUN chown -R www-data:www-data /var/www/html