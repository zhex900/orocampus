#!/usr/bin/env bash
# https://gist.github.com/cecilemuller/a26737699a7e70a7093d4dc115915de8

if [ "$#" -gt 0 ]; then
  HOST="$1"
else
  echo " => ERROR: You must specify the host URL (aws1.orocampus.com.au) as the first arguement to this scripts! <="
  exit 1
fi

echo '
location ^~ /.well-known/acme-challenge/ {
	default_type "text/plain";
	root /var/www/letsencrypt;
}' > /etc/nginx/snippets/letsencrypt.conf

echo '
ssl_session_timeout 1d;
ssl_session_cache shared:SSL:50m;
ssl_session_tickets off;

ssl_protocols TLSv1.2;
ssl_ciphers EECDH+AESGCM:EECDH+AES;
ssl_ecdh_curve secp384r1;
ssl_prefer_server_ciphers on;

ssl_stapling on;
ssl_stapling_verify on;

add_header Strict-Transport-Security "max-age=15768000; includeSubdomains; preload";
add_header X-Frame-Options DENY;
add_header X-Content-Type-Options nosniff;' > /etc/nginx/snippets/ssl.conf

mkdir -p /var/www/letsencrypt/.well-known/acme-challenge

# download nginx conf file
curl -O https://raw.githubusercontent.com/zhex900/orocampus/dev/Deployment/orocampus/bap.conf

# insert host url
sed -i s/HOST_URL/$HOST/g bap.conf

cp bap.conf /etc/nginx/sites-enabled/

certbot certonly --standalone --preferred-challenges http --http-01-port 80 -d $HOST --email=zhex900@gmail.com --agree-tos --non-interactive

supervisorctl restart nginx

# Automatic renewal using Cron
(crontab -l 2>/dev/null; echo "20 3 * * * certbot renew --noninteractive --renew-hook  /usr/local/bin/supervisorctl restart nginx") | crontab -