#!/usr/bin/env bash
cd /var/www

git clone https://github.com/zhex900/orocampus.git
mv orocampus/src/CampusCRM src/
rm -rf orocampus

php app/console cache:clear --env=prod -vvv
chown -R www-data:www-data /var/www/ /srv/app-data/

php app/console oro:migration:load --show-queries --force --bundles="EventNameBundle"
php app/console oro:migration:load --show-queries --force --bundles="CampusCalendarBundle"
php app/console oro:migration:data:load

sed -i '' 's/session_handler/\#session_handler/g' /var/www/app/config/parameters.yml
echo '    session_handler:    'snc_redis.session.handler'' >> /var/www/app/config/parameters.yml
echo '    redis_dsn_cache:    'redis://redis:6379/0'' >> /var/www/app/config/parameters.yml
echo '    redis_dsn_session:  'redis://redis:6379/1'' >> /var/www/app/config/parameters.yml
echo '    redis_dsn_doctrine: 'redis://redis:6379/2'' >> /var/www/app/config/parameters.yml

php app/console cache:clear --env=prod -vvv
chown -R www-data:www-data /var/www/ /srv/app-data/



