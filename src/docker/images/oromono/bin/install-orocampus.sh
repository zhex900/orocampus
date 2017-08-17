#!/usr/bin/env bash

# This a install script for orocampus.
# Run this only when the database is empty.
#
#
if [ ! -d /var/www/app/import_export ]
then
    mkdir -p /var/www/app/import_export
fi

# install the orocampus. 
cd /var/www 
php /var/www/app/console oro:migration:load --show-queries --force --bundles="EventNameBundle"
php /var/www/app/console cache:clear --env=prod -vvv

php /var/www/app/console oro:migration:load --show-queries --force
php app/console oro:migration:load --show-queries --force
php /var/www/app/console oro:migration:data:load
#php app/console cache:clear -–env=prod -vvv
#php app/console assets:install
#php app/console assetic:dump
#php app/console oro:requirejs:build
#app/console oro:workflow:definitions:load --workflows contact_followup
#app/console oro:workflow:definitions:load --workflows contact_feedback
#app/console oro:translation:load
chmod +x /usr/local/bin/fix_parameters.sh
fix_parameters.sh

php /var/www/app/console cache:clear --env=prod -vvv
chown -R www-data:www-data /var/www/ /srv/app-data/