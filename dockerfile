FROM ${AWS_ACCOUNT_ID_SHAREDCOREECR}.dkr.ecr.${AWS_REGION}.amazonaws.com/php-base:7.4.33-fpm-alpine-f49382c
LABEL maintainer="shaun.hare@dvsa.gov.uk"
LABEL description="PHP Alpine base image with dependency packages"
LABEL Name="ssweb-vol-php-fpm:7.4.33-alpine-fpm"
LABEL Version="0.1"


# Expose ports
EXPOSE 80


RUN apk -U upgrade && apk add --no-cache \
    curl \
    nginx \
    clamav

    
    
#RUN rm /etc/nginx/conf.d/default.conf

COPY nginx/conf.d/frontend.conf /etc/nginx/nginx.conf

COPY php-fpm/php-fpm.d/www.conf /usr/local/etc/php-fpm.d/www.conf

# COPY config/php.ini /usr/local/etc/php/php.ini

# FROM registry.olcs.dev-dvsacloud.uk/k8s/php:7.4.22-fpm-alpine as intermediate

RUN mkdir -p /opt/dvsa/olcs-frontend/public/static /var/log/dvsa /tmp/Entity/Proxy && \
    touch /var/log/dvsa/frontend.log
    
ADD selfserve.tar.gz /opt/dvsa/olcs-frontend

COPY static /opt/dvsa/olcs-frontend/public/static

COPY start.sh /start.sh
RUN chmod +x /start.sh


# FROM registry.olcs.dev-dvsacloud.uk/k8s/php-baseline:7.4.22-fpm-alpine
    
# Tweak redis extension settings
#RUN echo 'session.save_handler = redis' >> /usr/local/etc/php/conf.d/50-docker-php-ext-redis.ini && \
    #echo 'session.save_path = "tcp://redis-master"' >> /usr/local/etc/php/conf.d/50-docker-php-ext-redis.ini

# update clamav database/library
RUN freshclam

RUN addgroup clamav nginx  && \
    rm -f /opt/dvsa/olcs-frontend/config/autoload/local* && \
    mkdir /var/nginx && \
    mkdir /var/tmp/nginx && \
    mkdir /run/clamav && chown clamav:clamav /run/clamav && chmod 1777 /run/clamav && \
    chown -R nginx:nginx /opt/dvsa /tmp/* /var/log/dvsa /var/nginx /var/tmp/nginx && \
    chmod u=rwx,g=rwx,o=r -R /opt/dvsa /tmp/* /var/log/dvsa /var/nginx /var/tmp/nginx && \
    touch /run/clamav/clamd.sock && touch /run/clamav/clamd.pid && \
    chmod 1777 /run/clamav/clamd.pid  /run/clamav/clamd.sock
    

CMD ["/start.sh"]
