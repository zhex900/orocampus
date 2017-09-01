#Installation guide for orocampus
#New node setup

#Enable https

## Setup SSL certificate
    add-apt-repository ppa:certbot/certbot -y && \
    apt-get update && \
    apt-get install -y python-certbot-nginx && \
    
reference https://gist.github.com/cecilemuller/a26737699a7e70a7093d4dc115915de8
    
#Clear cache
    # Free up more memory first
    supervisorctl stop app-cron mq-consumer stdout websocket-server && \
    php /var/www/app/console cache:clear --env=prod -vvv && \
    chown -R www-data:www-data /var/www/ /srv/app-data/ && \
    supervisorctl start app-cron mq-consumer stdout websocket-server 

#Install orocampus
    