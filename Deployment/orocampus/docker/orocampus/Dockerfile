FROM zhex900/orobase

COPY bin/run.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/run.sh

COPY conf/nginx.conf          /etc/nginx/nginx.conf
COPY conf/nginx-bap.conf      /etc/nginx/sites-enabled/bap.conf
COPY ["conf/supervisord-1.x.conf", "conf/supervisord-2.x.conf", "/etc/"] 

#VOLUME ["/var/www/app/cache", "/var/www/web/uploads", "/var/www/web/media", "/var/www/app/attachment"]

EXPOSE 443 80 8080

# HTTPS or SSH
# If you want to use ssh don't forget to provide ssh key via build arg directive
ARG GIT_URI="https://github.com/orocrm/crm-application.git"

# branch name or tag 
# master - for master branch
# tags/1.9.1 - for 1.9.1 tag 
ARG GIT_REF="tags/2.3.3"
COPY bin/install-application.sh          /usr/local/bin/install-application.sh
COPY bin/install-oro.sh                  /usr/local/bin/install-oro.sh
COPY bin/download-orocampus.sh           /usr/local/bin/download-orocampus.sh
COPY bin/rebuild.sh                      /usr/local/bin/rebuild.sh
COPY bin/fix_parameters.sh               /usr/local/bin/fix_parameters.sh
COPY bin/install-orocampus.sh            /usr/local/bin/install-orocampus.sh

RUN install-application.sh

CMD ["run.sh"]

ENV "SYMFONY_ENV=prod"