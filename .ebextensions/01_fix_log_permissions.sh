#!/bin/bash

set -xe

touch /var/app/current/storage/logs/laravel.log
chown webapp:webapp /var/app/current/storage/logs/laravel.log
chmod 0666 /var/app/current/storage/logs/laravel.log
