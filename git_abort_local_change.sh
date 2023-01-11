#!/bin/bash

git fetch
git reset --hard HEAD
git merge '@{u}'


chown -R librenms:librenms ./ ; chown apache:apache rrd ; chmod 777 -R bootstrap/cache storage logs rrd; chmod 755 *.php ; chmod 755 *.py ; chmod 755 cronic ;