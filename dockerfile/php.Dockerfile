FROM php:7.2-fpm

#切换阿里源
RUN cp /etc/apt/sources.list /etc/apt/sources.list.bak
COPY ./sources.list /etc/apt/sources.list

RUN apt-get update

# 安装以及库
RUN apt-get install -y \
        git \
        wget \
        zip \
        unzip \
        net-tools \
        vim \
        iputils-ping \
        telnet \
        tcpdump \
        procps


# 安装supervisor
RUN apt-get install -y supervisor

# 部署crontab
RUN apt-get install -y cron

# php扩展
RUN docker-php-ext-install -j$(nproc) exif calendar bcmath gettext mysqli pcntl pdo_mysql shmop sockets sysvmsg sysvsem sysvshm

# zip 扩展
RUN apt-get install -y libzip-dev \
    && docker-php-ext-install zip


# mcrypt 扩展
RUN apt-get install -y  libmcrypt-dev \
    && yes "" | pecl install mcrypt-1.0.2 \
    && docker-php-ext-enable mcrypt

# gd 扩展
RUN	apt-get install -y libfreetype6-dev libjpeg62-turbo-dev libpng-dev \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

# Memcached 扩展
RUN apt-get install -y zlib1g-dev libmemcached-dev  \
	&& yes "" | pecl install memcached-3.1.3  \
	&& docker-php-ext-enable memcached


#mysql client
RUN apt-get install -y default-mysql-client


#添加中文支持
RUN apt-get install -y locales
RUN sed -ie 's/# zh_CN.UTF-8 UTF-8/zh_CN.UTF-8 UTF-8/g' /etc/locale.gen
RUN locale-gen
ENV LANG zh_CN.UTF-8

#部署 rsyslog
RUN apt-get install -y rsyslog

#安装 traceath
RUN apt-get install -y iputils-tracepath


# 复制gmssl
COPY ./GmSSL/usr/bin/gmssl /usr/bin/gmssl
# 复制gamssl
COPY ./GmSSL/gamssl /gamssl
RUN chmod +x /usr/bin/gmssl





# 清除apt list
RUN apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*


COPY ./php.entrypoint.sh /usr/bin/php-entrypoint.sh
RUN chmod +x /usr/bin/php-entrypoint.sh


#设置php 网络权限
RUN setcap 'cap_net_raw+ep' $(readlink -f $(which php))
RUN setcap 'cap_net_raw+ep' $(readlink -f $(which php-fpm))

#连接 php到 /usr/bin下
RUN ln -s $(readlink -f $(which php)) /usr/bin/php

#composer
COPY ./composer /usr/bin/composer
RUN chmod +x /usr/bin/composer


WORKDIR /var/www


ENTRYPOINT ["/usr/bin/php-entrypoint.sh"]


