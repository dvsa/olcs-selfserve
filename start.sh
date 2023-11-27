#!/bin/bash
rm -rf /opt/dvsa/olcs-frontend/data/cache/module-config-cache.application.config.cache.php

# start clamd
clamd start

# Start PHP-FPM
/usr/local/sbin/php-fpm -F --nodaemonize &

# Start Nginx
nginx -g 'daemon off;'