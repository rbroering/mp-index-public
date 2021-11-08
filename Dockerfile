FROM ubuntu/apache2

RUN apt-get update && apt upgrade -y
RUN apt-get install -y software-properties-common
RUN add-apt-repository ppa:ondrej/php

RUN apt-get install -y php8.0 libapache2-mod-php8.0
RUN a2enmod php8.0 && a2enmod rewrite

RUN service apache2 restart
