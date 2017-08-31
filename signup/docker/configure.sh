#!/usr/bin/env bash

cd /app
git clone https://github.com/zhex900/orocampus.git
cd orocampus
git checkout signup
cd signup
composer update --no-dev
mkdir /app/log
chmod 777 /app/data /app/log

apk update && apk add certbot

rm -rf /tmp/orocampus

echo '
server {
    listen 80 default_server;
    server_name signup.unswchristians.com docker;

    root "/app";
    index login.php;
    return 301 https://$server_name$request_uri;
    include /opt/docker/etc/nginx/vhost.common.d/*.conf;
}

##############
# SSL
##############

server {
    listen 443 default_server;

    server_name  signup.unswchristians.com docker;

    root "/app";
    index login.php;

    include /opt/docker/etc/nginx/vhost.common.d/*.conf;
    include /opt/docker/etc/nginx/vhost.ssl.conf;
}' >  /opt/docker/etc/nginx/vhost.conf