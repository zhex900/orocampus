#Sign-up page installation guide
##Hardware requirement
    debian 9
    1 core 1G memory
##New node setup
####Install docker engine and docker-compose
    apt-get update && \ 
    apt-get install sudo && \
    apt-get install apt-transport-https dirmngr && \
    echo 'deb https://apt.dockerproject.org/repo debian-stretch main' >> /etc/apt/sources.list && \
    apt-key adv --keyserver hkp://p80.pool.sks-keyservers.net:80 --recv-keys 58118E89F3A912897C070ADBF76221572C52609D && \
    apt-get update && apt-get -y install docker-engine && \
    curl -L https://github.com/docker/compose/releases/download/1.15.0/docker-compose-`uname -s`-`uname -m` > /usr/local/bin/docker-compose && \
    chmod +x /usr/local/bin/docker-compose
####Secure docker socket with tls
* Download setup script docker_tls.sh
    `sh docker_tls.sh`
* Copy client keys to host

##Docker