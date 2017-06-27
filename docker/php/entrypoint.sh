#!/bin/sh

# Change www-data's uid & guid to be the same as directory in host or the configured one
sed -ie "s/`id -u www-data`/`stat -c %u /srv`/g" /etc/passwd

