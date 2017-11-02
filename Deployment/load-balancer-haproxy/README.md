#Installation
##New node setup
####Hardware requirement
    debian 9
    1 core 1G memory
####Install haproxy
    apt-get update & apt-install -y haproxy
####Install certbot
    apt-get install certbot
    systemctl stop haproxy
    certbot certonly --standalone --preferred-challenges http --http-01-port 80 -d orocampus.com.au -d www.orocampus.com.au --email=zhex900@gmail.com --agree-tos --non-interactive
    mkdir -p /etc/haproxy/certs
    DOMAIN='orocampus.com.au' bash -c 'cat /etc/letsencrypt/live/$DOMAIN/fullchain.pem /etc/letsencrypt/live/$DOMAIN/privkey.pem > /etc/haproxy/certs/$DOMAIN.pem'
    chmod -R go-rwx /etc/haproxy/certs
    echo '
    #!/usr/bin/env bash
    SITE=orocampus.com.au
    
    # move to the correct let's encrypt directory
    cd /etc/letsencrypt/live/$SITE
    
    # cat files to make combined .pem for haproxy
    cat fullchain.pem privkey.pem > /etc/haproxy/certs/$SITE.pem
    
    # reload haproxy
    service haproxy reload ' > /usr/local/bin/cert_renew.sh
    chmod u+x /usr/local/bin/cert_renew.sh
    echo '
    SHELL=/bin/sh
    PATH=/usr/local/sbin:/usr/local/bin:/sbin:/bin:/usr/sbin:/usr/bin
    
    30 2 * * * /usr/bin/certbot renew --renew-hook "/usr/local/bin/cert_renew.sh" >> /var/log/le-renewal.log' > /etc/cron.d/cert_renew
Reference
https://www.digitalocean.com/community/tutorials/how-to-secure-haproxy-with-let-s-encrypt-on-ubuntu-14-04
####Add configure
    echo '
    frontend ft_web
      bind 0.0.0.0:80
      default_backend orocampus
    backend orocampus
      mode http
      balance roundrobin
      option httpchk HEAD /app.php HTTP/1.1\r\nHost:\ orocampus.com.au
      cookie SERVERID insert indirect nocache
      server web1 app1.orocampus.com.au:80 check cookie web1
      server web2 app2.orocampus.com.au:80 check cookie web2
    listen stats
       bind *:1936
       mode http
       stats enable
       stats scope http
       stats scope orocampus
       stats realm Haproxy\ Statistics
       stats uri /
       stats auth admin:fheman ' >> /etc/haproxy/haproxy.cfg
####Restart haproxy
    systemctl restart haproxy  
    
    
    
    