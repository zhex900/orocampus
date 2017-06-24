#!/usr/bin/env bash

# install the orocampus. 
cd /var/www
mv /var/www/CampusCRM /var/www/src

php /var/www/app/console oro:migration:load --show-queries --force --bundles="EventNameBundle"
php /var/www/app/console cache:clear --env=prod -vvv

php /var/www/app/console oro:migration:load --show-queries --force
php app/console oro:migration:load --show-queries --force
php /var/www/app/console oro:migration:data:load

fix_parameters.sh

php /var/www/app/console cache:clear --env=prod -vvv
chown -R www-data:www-data /var/www/ /srv/app-data/