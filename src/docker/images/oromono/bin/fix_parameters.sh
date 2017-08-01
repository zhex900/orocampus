#!/usr/bin/env bash

PARAMETERS='/var/www/app/config/parameters.yml'

sed -i 's/session_handler.*//g' ${PARAMETERS}
sed -i 's/redis_dsn_.*//g' ${PARAMETERS}
echo '    session_handler:    'snc_redis.session.handler'' >> ${PARAMETERS}
echo '    redis_dsn_cache:    'redis://redis:6379/0'' >> ${PARAMETERS}
echo '    redis_dsn_session:  'redis://redis:6379/1'' >> ${PARAMETERS}
echo '    redis_dsn_doctrine: 'redis://redis:6379/2'' >> ${PARAMETERS}
sed -i 's/websocket_frontend_port.*//g' ${PARAMETERS}
echo '    websocket_frontend_port: 3088' >> ${PARAMETERS}