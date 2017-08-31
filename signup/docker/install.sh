#!/usr/bin/env bash

echo '
server {
    listen 80 default_server;
    server_name signup.unswchristians.com docker;

    root "/app/orocampus/signup";
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

    root "/app/orocampus/signup";
    index login.php;

    include /opt/docker/etc/nginx/vhost.common.d/*.conf;
    include /opt/docker/etc/nginx/vhost.ssl.conf;
}' >  /opt/docker/etc/nginx/vhost.conf

certbot certonly -a webroot --webroot-path=/app --email=zhex900@gmail.com -d signup.unswchristians.com --agree-tos --non-interactive --text --rsa-key-size 4096
rm /opt/docker/etc/nginx/ssl/*
ln -s /etc/letsencrypt/live/signup.unswchristians.com/fullchain.pem /opt/docker/etc/nginx/ssl/server.crt
ln -s /etc/letsencrypt/live/signup.unswchristians.com/privkey.pem /opt/docker/etc/nginx/ssl/server.key
supervisorctl restart nginx:nginxd


