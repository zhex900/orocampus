#!/usr/bin/env bash

php /var/www/app/console fos:js-routing:dump --process-isolation
php /var/www/app/console oro:localization:dump
php /var/www/app/console oro:assets:install
php /var/www/app/console assetic:dump --process-isolation
php /var/www/app/console oro:translation:dump --process-isolation
php /var/www/app/console oro:requirejs:build --process-isolation

php /var/www/app/console cache:clear --env=prod -vvv
chown -R www-data:www-data /var/www/ /srv/app-data/