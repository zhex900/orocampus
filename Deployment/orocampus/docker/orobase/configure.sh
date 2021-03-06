#!/usr/bin/env bash
localedef -c -f UTF-8 -i en_US en_US.UTF-8
export DEBIAN_FRONTEND=noninteractive

WWW_USER=${WWW_USER-"www-data"}
WWW_GROUP=${WWW_GROUP-"www-data"}
UPLOAD_LIMIT=${UPLOAD_LIMIT-"256"}

# configure php cli
sed -i -e "s/;date.timezone\s=/date.timezone = UTC/g" /etc/php/7.0/cli/php.ini
sed -i -e "s/short_open_tag\s=\s*.*/short_open_tag = Off/g" /etc/php/7.0/cli/php.ini
sed -i -e "s/memory_limit\s=\s.*/memory_limit = -1/g" /etc/php/7.0/cli/php.ini
sed -i -e "s/max_execution_time\s=\s.*/max_execution_time = 0/g" /etc/php/7.0/cli/php.ini

# configure php fpm
sed -i -e "s/;cgi.fix_pathinfo=1/cgi.fix_pathinfo=0/g" /etc/php/7.0/fpm/php.ini
sed -i -e "s/;date.timezone\s=/date.timezone = UTC/g" /etc/php/7.0/fpm/php.ini
sed -i -e "s/short_open_tag\s=\s*.*/short_open_tag = Off/g" /etc/php/7.0/fpm/php.ini

sed -i -e "s/upload_max_filesize\s*=\s*2M/upload_max_filesize = 1G/g" /etc/php/7.0/fpm/php.ini
sed -i -e "s/memory_limit\s=\s.*/memory_limit = -1/g" /etc/php/7.0/fpm/php.ini
sed -i -e "s/post_max_size\s*=\s*8M/post_max_size = 1G/g" /etc/php/7.0/fpm/php.ini
sed -i -e "s/max_execution_time\s=\s.*/max_execution_time = 300/g" /etc/php/7.0/fpm/php.ini

# php-fpm config
sed -i -e "s/;daemonize\s*=\s*yes/daemonize = no/g" /etc/php/7.0/fpm/php-fpm.conf
sed -i -e "s/;catch_workers_output\s*=\s*yes/catch_workers_output = yes/g" /etc/php/7.0/fpm/pool.d/www.conf
sed -i -e "s/;catch_workers_output\s*=\s*yes/catch_workers_output = yes/g" /etc/php/7.0/fpm/pool.d/www.conf
sed -i -e "s/listen\s=\s\/run\/php\/php7.0-fpm.sock/listen = \/var\/run\/php-fpm.sock/g" /etc/php/7.0/fpm/pool.d/www.conf

# Fix old style for comments
find /etc/php/7.0/cli/conf.d/ -name "*.ini" -exec sed -i -re 's/^(\s*)#(.*)/\1;\2/g' {} \;

# Configure nginx
sed -i -e"s/keepalive_timeout\s*65/keepalive_timeout 2/" /etc/nginx/nginx.conf || exit 1
sed -i -e"s/keepalive_timeout 2/keepalive_timeout 2;\n\tclient_max_body_size ${UPLOAD_LIMIT}m/" /etc/nginx/nginx.conf || exit 1
echo "daemon off;" >> /etc/nginx/nginx.conf
# Remove defaults
#rm /etc/nginx/conf.d/default.conf
rm -rf /var/www

# Confgire ssh server
sed -i -e "s/#AuthorizedKeysFile/AuthorizedKeysFile/g" 
mkdir ~/.ssh
echo "ssh-rsa AAAAB3NzaC1yc2EAAAADAQABAAABAQDH0UGuqwvvXdModpPt4RNUs2djExlk4zmW/P0z0LSO7w8rfA3Ww2FDmaUF7P6mFyqJ05A/iUUYfE30Ivxv4gI2AXCHkneY90bGUOHc3SMosRsKnNVbSMcn2LG1HnqWl2UsEcVUVtBEQMmNj3hnvrTaYhexaYnUN0y2GnZ6dgf+/+4stAS7tetqNabem8YeoRU81puqE2XFPuKQ7DS+qHHXrMYeEMhamL58NftiXCfeJC+v3rkyoY/ie+MfcQoTIZl5YjPs66n4URfsRnoaJ8x9dahLl+86pe6eUYvUQRi7j7uCTXRzLrQABlMccEuevfbuuat2U6AylsOaf21BSADF zhex900@Macintosh-4.local" > ~/.ssh/authorized_keys
chmod 600 ~/.ssh/authorized_keys
 
# Create data folder
mkdir -p /srv/app-data
mkdir -p /var/www

mkdir -p /var/run/php
ln -s /usr/sbin/php-fpm7.0 /usr/sbin/php-fpm

chown ${WWW_USER}:${WWW_GROUP} /srv/app-data
chown ${WWW_USER}:${WWW_GROUP} /var/www

apt-get -qq clean
rm -rf /var/lib/apt/lists/*
