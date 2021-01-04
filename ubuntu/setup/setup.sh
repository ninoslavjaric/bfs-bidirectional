#!/bin/bash

(
  echo '[client]'
  echo 'user=root'
  echo "password=$MYSQL_ROOT_PASSWORD"
  echo 'host=mysql'
) > ~/.my.cnf

DB_CONFIG_TEMPLATE=/var/www/html/config/database.php
DB_CONFIG=/var/www/html/config/database.local.php

if [ ! -f $DB_CONFIG ]; then
    echo $DB_CONFIG
    cat $DB_CONFIG_TEMPLATE | \
      sed 's/hostname_param/mysql/g' | \
      sed 's/port_param/3306/g' | \
      sed "s/database_param/$DB_NAME/g" | \
      sed "s/username_param/$DB_USER/g" | \
      sed "s/password_param/$DB_PASSWORD/g" > $DB_CONFIG;
fi

/tmp/setup/wait-for-it.sh mysql:3306 -t 10
sleep 3

RESULT=`mysqlshow $DB_NAME | grep -v Wildcard | grep -o $DB_NAME`

if [ "$RESULT" != $DB_NAME ]; then
  mysql -e "CREATE DATABASE $DB_NAME CHARSET utf8 COLLATE utf8_unicode_ci";
  mysql -e "CREATE USER '$DB_USER'@'%' IDENTIFIED BY '$DB_PASSWORD'"
  mysql -e "GRANT ALL PRIVILEGES ON $DB_NAME.* TO '$DB_USER'@'%'"

  (
    echo '[client]'
    echo "user=$DB_USER"
    echo "password=$DB_PASSWORD"
    echo "host=mysql"
  ) > ~/.my.cnf

  mysql $DB_NAME < /tmp/setup/db.sql

  /var/www/html/setup/setup_admin.php
fi

(cd /var/www/html/; ./composer.phar install)


/usr/sbin/apache2ctl -D FOREGROUND
