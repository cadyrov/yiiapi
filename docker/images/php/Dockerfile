from ubuntu:18.04

ARG TZ
ARG CONTAINER_USER
ARG CONTAINER_UID
ARG CONTAINER_GID
ARG PHP_VERSION
ARG PHP_FPM_USER

ENV C_USER=$CONTAINER_USER \
    C_UID=$CONTAINER_UID \
    C_GID=$CONTAINER_GID

RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone && \ 
    apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get -y upgrade && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y php7.2-fpm php7.2-xml php7.2-mbstring php7.2-gd php7.2-pgsql php7.2-zip php7.2-pdo php7.2-sybase php7.2-curl php7.2-imap php7.2-mysql && \
    mkdir -p /run/php && \
    sed -i 's/^listen = .*/listen = 0.0.0.0:9000/' /etc/php/7.2/fpm/pool.d/www.conf && \
    sed -i "s/upload_max_filesize = 2M/upload_max_filesize = 400M/g" /etc/php/7.2/fpm/php.ini && \
    sed -i "s/post_max_size = 8M/post_max_size = 400M/g" /etc/php/7.2/fpm/php.ini && \
    sed -i "s/session.gc_maxlifetime = 1440/session.gc_maxlifetime = 86400/g" /etc/php/7.2/fpm/php.ini && \
    
    sed -i "s/memory_limit = 128M/memory_limit = 1024M/g" /etc/php/7.2/fpm/php.ini && \
    sed -i "s/^display_errors = .*/display_errors = On/" /etc/php/7.2/fpm/php.ini && \
    sed -i "s/max_execution_time = 30/max_execution_time = 0/g" /etc/php/7.2/fpm/php.ini

EXPOSE 9000

CMD ["/usr/sbin/php-fpm7.2", "-F"]
