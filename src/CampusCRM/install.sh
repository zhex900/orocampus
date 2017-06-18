#!/usr/bin/env bash

#first install orocrm databse and assets
chown -R www-data:www-data /var/www/ /srv/app-data/
php /var/www/app/console oro:install --env=prod --force --user-name=admin --timeout=3000 --user-email=zhex900@gmailcom --user-firstname=Jake --user-lastname=He --user-password=Fheman123 --sample-data=n --organization-name=OROCAMPUS --application-url=http://orocampus.tk

#set ssh private key
if [ ! -d /root/.ssh ]
then
    mkdir -p /root/.ssh
fi

echo "Host github.com\n\tStrictHostKeyChecking no\n" >> ~/.ssh/config

# copy private key
echo '-----BEGIN RSA PRIVATE KEY-----
MIIEogIBAAKCAQEAx9FBrqsL713TKHaT7eETVLNnYxMZZOM5lvz9M9C0ju8PK3wN
1sNhQ5mlBez+phcqidOQP4lFGHxN9CL8b+ICNgFwh5J3mPdGxlDh3N0jKLEbCpzV
W0jHJ9ixtR56lpdlLBHFVFbQREDJjY94Z7602mIXsWmJ1DdMthp2enYH/v/uLLQE
u7XrajWm3pvGHqEVPNabqhNlxT7ikOw0vqhx16zGHhDIWpi+fDX7Ylwn3iQvr965
MqGP4nvjH3EKEyGZeWIz7Oup+FEX7EZ6GifMfXWoS5fvOqXunlGL1EEYu4+7gk10
cy60AAZTHHBLnr327rmrdlOgMpbDmn9tQUgAxQIDAQABAoIBACFudHPZ8GxDIXIy
rLtvHgHc5l5gMq57igYmG+MQdzU28C3RWqtlEx/xU/fy2ARH+fkHaaoHuITJP22q
cNvzT3VjtkUoj1QLg07o93ExmpFTWHflF5lnStLy4YCxMceCWw4Nhxt+Tugsgsxp
hbat5KppIRew1buo6O/K66m/l8TlFw8NFSIhcBV74Pzujcx/80A30Q1EdV0G5nRc
nLv3pOewf4eKZUcDaRGQc1o5+Ci0TWJD45VzBsRPZq4oT43X6f/AWDcvMOee2pTL
Uaj8YQMUQBPrqWRJmgOpwV4Sdl1MQ87VLCelfL66XmZcx/tFCKsTaTo7+ps0dkGD
2faMSf0CgYEA+gvrDHkx/GRaoNL/m1TCxbEw0xHnrfvF/De1A6F7oxzyKTR7XQ3K
3NxHrJy/Tk7bqYIXkdKz01bktJidrwVGxkAkmykMD8gAM+fkxKun+4CSWcpyOATw
yxmRTezxUZJoaA2OSn0nyb90FfkD03iwYdkhxmhGPvszrD3qGceTShsCgYEAzJMu
nkx+mwWrbm8sIZCyingssxz64nQ55fgFOhzE8I0rZbIi4giLewPkA3w/tajcYPNP
hvkMJxHg72gZ5MxDGLZNczvwCVkn0Y7O9k5NhuWmyZv6qA8Lift/zae35TzAGzvx
o/QS/+nJqWGjp1Tu+dL7gmYQ0MObjpJxPmg2jp8CgYB8YGdHqhVSHTzzWGEqi6vi
mDPYGcTrRxyBS9hveOi+BwzxsBhY/h5VZAEG/GUwd4tOMta0g2FNk6BpsKxmvbIp
tQhAYdeNFgf7ybKCnXwOXzLtFQVhlhuMeyhK4bxbvf7PG0cXCFA9S01cDKR5kUPz
OAVT4tRmSZ+3YojKz/oU1wKBgC9Fs3gQ5RCddsfGGMSI0zj8HBgnkjHR+a8SaOLM
0xVjCawuRbFFmDWM6JuFkpM/ue0NTEs8MXq1vuyTeahNKxQCzFLcftMqLvFVtq1j
2rZZSvk7eehr0ZbI7vdu0ie+qgWhDG1+cQWB2H+zeEWqcq53+nZfb3NOQBvp6xEo
s7KxAoGAc0k1/JGxcxc8XmBB8FSk8HsMOWQg6NsdAjnW0P/EnPpXaGF6QtnGI6sj
KJpGUn4DyASh5J5k35cX+Bw3tOFNfKkgoqvHHmO5MrfgPPN3xZp5PVReMYUMn5AM
l3VPRWiHmPs2q2nEEobwFGybNWK2YWoUX8YkxjoXxLNhP45NTrU=
-----END RSA PRIVATE KEY-----' > ~/.ssh/id_rsa
chmod 600 ~/.ssh/id_rsa

# clone the orocampus source code
git clone git@github.com:zhex900/orocampus.git /tmp/oro

mv /tmp/oro/src/CampusCRM /var/www/src/
rm -rf /tmp/oro

php /var/www/app/console cache:clear --env=prod -vvv
chown -R www-data:www-data /var/www/ /srv/app-data/

# install the orocampus.
cd /var/www
php /var/www/app/console oro:migration:load --show-queries --force --bundles="EventNameBundle"
php /var/www/app/console oro:migration:load --show-queries --force --bundles="CampusCalendarBundle"
php /var/www/app/console oro:migration:load --show-queries --force --bundles="CampusUserBundle"
php /var/www/app/console oro:migration:load --show-queries --force --bundles="CampusContactBundle"
#php app/console oro:migration:load --show-queries --force
php /var/www/app/console oro:migration:data:load

php /var/www/app/console cache:clear --env=prod -vvv

# set redis configuration
sed -i 's/session_handler/\#session_handler/g' /var/www/app/config/parameters.yml
echo '    session_handler:    'snc_redis.session.handler'' >> /var/www/app/config/parameters.yml
echo '    redis_dsn_cache:    'redis://redis:6379/0'' >> /var/www/app/config/parameters.yml
echo '    redis_dsn_session:  'redis://redis:6379/1'' >> /var/www/app/config/parameters.yml
echo '    redis_dsn_doctrine: 'redis://redis:6379/2'' >> /var/www/app/config/parameters.yml

php /var/www/app/console cache:clear --env=prod -vvv
chown -R www-data:www-data /var/www/ /srv/app-data/


# Check if this is the first run. First run means application is not
# installed. Both database and application are not built.
		# run install script

# Check if the application is built. This happens when the container is destroyed
# and start a fresh. Database remains the same. Only the source code remains.
# Cache and assets all needs to be rebuild.
		# rebuid application

