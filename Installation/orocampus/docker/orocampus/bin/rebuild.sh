#!/usr/bin/env bash

sed -i 's/session_handler.*//g' /var/www/app/config/parameters.yml
echo '    session_handler:    'session.handler.native_file'' >> /var/www/app/config/parameters.yml
sed -i 's/installed.*//g' /var/www/app/config/parameters.yml
echo '    installed:    'true'' >> /var/www/app/config/parameters.yml

cd /var/www

if [ ! -d /var/www/src/CampusCRM ]
then
    # download CampusCRM
    download-orocampus.sh
fi

cp -r /var/www/CampusCRM /var/www/src

if [ ! -d /var/www/app/import_export ]
then
    mkdir -p /var/www/app/import_export
fi

rm -rf /var/app/cache/*
#change installed to true
# rebuild assets
php /var/www/app/console oro:platform:update --force
#./app/console assets:install
#app/console assetic:dump
#chmod +x /usr/local/bin/fix_parameters.sh
#fix_parameters.sh

php /var/www/app/console cache:clear --env=prod -vvv
chown -R www-data:www-data /var/www/ /srv/app-data/