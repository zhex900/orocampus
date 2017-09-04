# Deployment Architecture

![alt text](https://assets.digitalocean.com/articles/letsencrypt/haproxy-letsencrypt.png)

| Node # | Service       | Applications | Resources
| ------ |:-------------:| ------------:| ------------:|
| 1      | load balance  | haproxy      | 1GB 1 Core
| 2      | databases     | mysql, redis | 2GB 1 Core
| 3      | app server    | orocampus    | 1GB 1 Core
| 4      | app server    | orocampus    | 1GB 1 Core

# New node setup

#### Operating System
    debian 9

#### Install docker engine and docker-compose
    apt-get update && \ 
    apt-get install sudo && \
    apt-get install apt-transport-https dirmngr && \
    echo 'deb https://apt.dockerproject.org/repo debian-stretch main' >> /etc/apt/sources.list && \
    apt-key adv --keyserver hkp://p80.pool.sks-keyservers.net:80 --recv-keys 58118E89F3A912897C070ADBF76221572C52609D && \
    apt-get update && apt-get -y install docker-engine && \
    curl -L https://github.com/docker/compose/releases/download/1.15.0/docker-compose-`uname -s`-`uname -m` > /usr/local/bin/docker-compose && \
    chmod +x /usr/local/bin/docker-compose
#### Secure docker socket with tls

* Download setup script docker_tls.sh
    `sh docker_tls.sh`
* Copy client keys to host

# Workarounds

## Mobile version login rendering problem
    sed -i 's/height:\sauto\;//g' ./vendor/oro/platform/src/Oro/Bundle/UIBundle/Resources/public/css/less/mobile/layout.less
    supervisorctl stop app-cron mq-consumer stdout websocket-server nginx
    php app/console assets:install
    php app/console assetic:dump
    php app/console oro:requirejs:build
    supervisorctl start app-cron mq-consumer stdout websocket-server nginx