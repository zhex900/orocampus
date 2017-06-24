#!/usr/bin/env bash

# This a install script for orocampus.
# Run this only when the database is empty.
#
#
if [ ! -d /var/www/app/import_export ]
then
    mkdir -p /var/www/app/import_export
fi

#first install orocrm databse and assets
rm -r /var/www/app/cache/*
chown -R www-data:www-data /var/www/ /srv/app-data/
php /var/www/app/console oro:install --env=prod --user-name=admin --timeout=3000 --user-email=zhex900@gmail.com --user-firstname=Jake --user-lastname=He --user-password=Fheman123 --sample-data=n --organization-name=OROCAMPUS --application-url=http://orocampus.tk

php /var/www/app/console cache:clear --env=prod -vvv
chown -R www-data:www-data /var/www/ /srv/app-data/

# install the orocampus. 
cd /var/www 
php /var/www/app/console oro:migration:load --show-queries --force --bundles="EventNameBundle"
php /var/www/app/console cache:clear --env=prod -vvv

php /var/www/app/console oro:migration:load --show-queries --force
php app/console oro:migration:load --show-queries --force
php /var/www/app/console oro:migration:data:load

chmod +x /usr/local/bin/fix_parameters.sh
fix_parameters.sh

php /var/www/app/console cache:clear --env=prod -vvv
chown -R www-data:www-data /var/www/ /srv/app-data/