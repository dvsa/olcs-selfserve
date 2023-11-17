#!/bin/bash

/opt/dvsa/olcs-frontend/vendor/bin/doctrine-module

# Start PHP-FPM
/usr/local/sbin/php-fpm -F --nodaemonize &

# Start Nginx
nginx -g 'daemon off;'