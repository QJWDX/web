#!/bin/bash

supervisord -c /etc/supervisor/supervisord.conf

cron

rsyslogd

php-fpm -R
