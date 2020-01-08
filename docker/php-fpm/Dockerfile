FROM php:7.2-fpm

ADD php.ini /usr/local/etc/php/conf.d/php.ini
ADD added.conf /usr/local/etc/php-fpm.d/added.conf

RUN apt-get update \
  && apt-get install -y \
  libmemcached-dev zlib1g-dev vim libmcrypt-dev \
  libfreetype6-dev \
  libjpeg62-turbo-dev \
  libpng-dev \
  libexif-dev \
  cron \
  default-mysql-client \
  dos2unix
RUN apt-get install -y build-essential 
RUN apt-get install -y autoconf 
RUN pecl install mcrypt-1.0.2 
RUN docker-php-ext-install -j$(nproc) pdo_mysql mysqli mbstring exif gd iconv
RUN docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ 
RUN docker-php-ext-install -j$(nproc) gd zip 
# RUN pecl install memcached-2.2.0 
# RUN docker-php-ext-enable memcache
# RUN pecl install xdebug-2.5.5 
# RUN docker-php-ext-enable xdebug mcrypt
RUN docker-php-ext-enable mcrypt

RUN \
  apt-get autoremove && \
  apt-get autoclean && \
  apt-get clean && \
  rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

#Composer install
COPY --from=composer /usr/bin/composer /usr/bin/composer

ENV COMPOSER_ALLOW_SUPERUSER 1
ENV COMPOSER_HOME /composer
ENV PATH $PATH:/composer/vendor/bin


ADD supervisord.conf /etc/supervisor/conf.d/cron.conf

RUN echo "export LANG=ja_JP.UTF-8" >> /root/.bashrc
