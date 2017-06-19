#!/usr/bin/env bash
/usr/local/bin/waitinstall.sh
#file permissions
exec /bin/chown -R www-data:www-data /var/www/ /srv/app-data/
exec /sbin/runuser -s /bin/sh -c "exec /usr/bin/php /var/www/app/console --env=prod oro:message-queue:consume" www-data
