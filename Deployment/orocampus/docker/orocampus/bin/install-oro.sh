#!/usr/bin/env bash

# install the orocampus. 
cd /var/www
PARAMETERS='/var/www/app/config/parameters.yml'
APP_DB_HOST='db'
APP_DB_NAME='orocrm'
APP_DB_PASSWORD='orocrm'
APP_DB_PORT='3306'
APP_DB_USER='orocrm'

# update the database config
sed -i s/database_host:.*/database_host:\ ${APP_DB_HOST}/g ${PARAMETERS}
sed -i s/database_name:.*/database_name:\ ${APP_DB_NAME}/g ${PARAMETERS}
sed -i s/database_password:.*/database_password:\ ${APP_DB_PASSWORD}/g ${PARAMETERS}
sed -i s/database_port:.*/database_port:\ ${APP_DB_PORT}/g ${PARAMETERS}
sed -i s/database_user:.*/database_user:\ ${APP_DB_USER}/g ${PARAMETERS}

#first install orocrm databse and assets
rm -r /var/www/app/cache/*
php /var/www/app/console cache:clear --env=prod -vvv

chown -R www-data:www-data /var/www/ /srv/app-data/
php /var/www/app/console oro:install --env=prod --user-name=admin --timeout=3000 --user-email=zhex900@gmail.com --user-firstname=Jake --user-lastname=He --user-password=Fheman123 --sample-data=n --organization-name=OROCAMPUS --application-url=http://app.orocampus.com.au

fix_parameters.sh

php /var/www/app/console cache:clear --env=prod -vvv
chown -R www-data:www-data /var/www/ /srv/app-data/