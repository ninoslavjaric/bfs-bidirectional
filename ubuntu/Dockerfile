FROM ubuntu:20.10

ENV TZ=Europe/Belgrade

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt update && apt upgrade -y

RUN apt install -y apache2 php curl php-curl php-mysql mysql-client php-xdebug php-dom php-mbstring
RUN echo "xdebug.remote_enable=on" >> /etc/php/7.4/mods-available/xdebug.ini && echo "xdebug.remote_connect_back=1" >> /etc/php/7.4/mods-available/xdebug.ini
RUN a2enmod headers && a2enmod rewrite

COPY ./setup /tmp/setup

CMD /tmp/setup/setup.sh
