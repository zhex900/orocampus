#!/usr/bin/env bash

sed -i 's/session_handler.*//g' /var/www/app/config/parameters.yml
echo '    session_handler:    'session.handler.native_file'' >> /var/www/app/config/parameters.yml

if [ ! -d /var/www/app/import_export ]
then
    mkdir -p /var/www/app/import_export
fi

# rebuild assets
php /var/www/app/console oro:platform:update --force

chmod +x /usr/local/bin/fix_parameters.sh
fix_parameters.sh

mv /tmp/oro/src/CampusCRM /var/www/src/

php /var/www/app/console cache:clear --env=prod -vvv
chown -R www-data:www-data /var/www/ /srv/app-data/