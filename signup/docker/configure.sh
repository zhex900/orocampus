#!/usr/bin/env bash

cd /app
git clone https://github.com/zhex900/orocampus.git
cd orocampus
git checkout signup
cd signup
composer update --no-dev
mkdir /app/orocampus/signup/log
chmod 777 /app/orocampus/signup/data /app/orocampus/signup/log

apk update && apk add certbot
