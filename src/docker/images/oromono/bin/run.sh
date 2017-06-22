#!/usr/bin/env bash
APP_ROOT="/var/www"
DATA_ROOT="/srv/app-data"

function info {
    printf "\033[0;36m===> \033[0;33m${1}\033[0m\n"
}

# Check if the local usage
if [[ -z ${IS_LOCAL} ]]; then
    # Map environment variables
    info "Map parameters.yml to environment variables"
    composer-map-env.php ${APP_ROOT}/composer.json

    # Generate parameters.yml
    info "Run composer script 'post-install-cmd'"
    runuser -s /bin/sh -c "composer --no-interaction run-script post-install-cmd -n -d ${APP_ROOT}" www-data
fi

if [[ -z ${APP_DB_PORT} ]]; then
    if [[ "pdo_pgsql" = ${APP_DB_DRIVER} ]]; then
        APP_DB_PORT="5432"
    else
        APP_DB_PORT="3306"
    fi
fi

until nc -z ${APP_DB_HOST} ${APP_DB_PORT}; do
    info "Waiting database on ${APP_DB_HOST}:${APP_DB_PORT}"
    sleep 2
done

if [[ ! -z ${CMD_INIT_BEFORE} ]]; then
    info "Running pre init command: ${CMD_INIT_BEFORE}"
    sh -c "${CMD_INIT_BEFORE}"
fi

cd ${APP_ROOT}

# If already installed
if [[ -z ${APP_IS_INSTALLED} ]]
then
    if [[ ! -z ${CMD_INIT_CLEAN} ]]; then
        info "Running init command: ${CMD_INIT_CLEAN}"
        sh -c "${CMD_INIT_CLEAN}"
    fi
else
    info "Updating application..."
    if [[ -d ${APP_ROOT}/app/cache ]] && [[ $(ls -l ${APP_ROOT}/app/cache/ | grep -v total | wc -l) -gt 0 ]]; then
        rm -r ${APP_ROOT}/app/cache/*
    fi

    if [[ ! -z ${CMD_INIT_INSTALLED} ]]; then
        info "Running init command: ${CMD_INIT_INSTALLED}"
        sh -c "${CMD_INIT_INSTALLED}"
    fi

fi

if [[ ! -z ${CMD_INIT_AFTER} ]]; then
    info "Running post init command: ${CMD_INIT_AFTER}"
    sh -c "${CMD_INIT_AFTER}"
fi

#clear cache.
info "Rebuild cache"
php /var/www/app/console cache:clear --env=prod -vvv
info "Fix ownership for /var/www/ /srv/app-data/"
chown -R www-data:www-data /var/www/ /srv/app-data/

# add redis config to parameters.yml
# When docker container is restarted, the redis dns config always get deleted.
# This is a workaroud.
if grep -q redis_ds /var/www/app/config/parameters.yml; then
	info "Add redis_dsn config to parameters.yml"
	echo '    redis_dsn_cache:    'redis://redis:6379/0'' >> /var/www/app/config/parameters.yml
	echo '    redis_dsn_session:  'redis://redis:6379/1'' >> /var/www/app/config/parameters.yml
	echo '    redis_dsn_doctrine: 'redis://redis:6379/2'' >> /var/www/app/config/parameters.yml
fi

# update the database config
sed -i s/database_host:.*/database_host:\ ${APP_DB_HOST}/g /var/www/app/config/parameters.yml
sed -i s/database_name:.*/database_name:\ ${APP_DB_NAME}/g /var/www/app/config/parameters.yml
sed -i s/database_password:.*/database_password:\ ${APP_DB_PASSWORD}/g /var/www/app/config/parameters.yml
sed -i s/database_port:.*/database_port:\ ${APP_DB_PORT}/g /var/www/app/config/parameters.yml
sed -i s/database_user:.*/database_user:\ ${APP_DB_USER}/g /var/www/app/config/parameters.yml

# Starting services
if php -r 'foreach(json_decode(file_get_contents("'${APP_ROOT}'/composer.lock"))->{"packages"} as $p) { echo $p->{"name"} . ":" . $p->{"version"} . PHP_EOL; };' | grep 'platform:2' > /dev/null
then
  info "Starting supervisord for platform 2.x" 
  exec /usr/local/bin/supervisord -n -c /etc/supervisord-2.x.conf
else
  info "Starting supervisord for platform 1.x" 
  exec /usr/local/bin/supervisord -n -c /etc/supervisord-1.x.conf
fi


