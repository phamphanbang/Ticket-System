#!/bin/bash
# Start cron in foreground
cron -f &
# Start php-fpm in foreground (default)
php-fpm