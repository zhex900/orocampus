FROM webdevops/php-nginx:alpine-php7
# add git clone
# run composer
# add deny all for sub directories
# set write permissions for log and data
#/opt/docker/etc/nginx/ssl

COPY ./orocampus/signup /app
RUN chmod 777 /app/data && chmod 666 /app/data/data.json