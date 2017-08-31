#!/usr/bin/env bash

if [ ! -f /etc/letsencrypt/live/signup.unswchristians.com/fullchain.pem ]; then
    certbot certonly -a webroot --webroot-path=/app --email=zhex900@gmail.com -d signup.unswchristians.com --agree-tos --non-interactive --text --rsa-key-size 4096
    rm /opt/docker/etc/nginx/ssl/*
    ln -s /etc/letsencrypt/live/signup.unswchristians.com/fullchain.pem /opt/docker/etc/nginx/ssl/server.crt
    ln -s /etc/letsencrypt/live/signup.unswchristians.com/privkey.pem /opt/docker/etc/nginx/ssl/server.key
    supervisorctl restart nginx:nginxd
fi

