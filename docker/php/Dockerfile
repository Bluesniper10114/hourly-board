FROM php:7.2-apache-stretch
# FROM php:5.6-apache-stretch

ENV RUN_COMPOSER false

# Install apt tools
RUN apt-get update -y && \
    DEBIAN_FRONTEND=noninteractive \
    apt-get install -y \
    software-properties-common curl \
    apt-transport-https locales gnupg \
    wget git zip unzip zlib1g-dev xmlstarlet dos2unix

# install gd extension and configure
# Source: https://hub.docker.com/_/php/
RUN apt-get update && apt-get install -y \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
    && docker-php-ext-install -j$(nproc) iconv \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd

# RUN echo "nb_NO.UTF-8 UTF-8" > /etc/locale.gen && \
#    echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen && \
#    locale-gen

# Install dependencies
RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add - && \
    curl https://packages.microsoft.com/config/debian/9/prod.list > /etc/apt/sources.list.d/mssql-release.list && \
    apt-get update -yqq && \
    ACCEPT_EULA=Y apt-get install -y msodbcsql17 unixodbc-dev unixodbc && \
    rm -r /var/lib/apt/lists/*

# Install SQLSRV PHP Drivers
RUN pecl install pdo_sqlsrv && \
    /usr/local/bin/docker-php-ext-enable pdo_sqlsrv && \
    pecl install sqlsrv && \
    /usr/local/bin/docker-php-ext-enable sqlsrv

# Install PHP Extensions
# from: https://github.com/webworksnbg/sfdocker/blob/master/docker/php/Dockerfile
RUN docker-php-ext-install zip

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Enable apache modules
RUN a2enmod rewrite && \
    a2enmod speling && \
    a2enmod headers

# Add dumb-init
RUN wget -O /usr/bin/dumb-init https://github.com/Yelp/dumb-init/releases/download/v1.2.2/dumb-init_1.2.2_amd64 && \
    chmod +x /usr/bin/dumb-init

# Add startup script
COPY ./docker-run.sh /usr/bin/docker-run.sh

# Add startup script
COPY ./tests.sh /usr/bin/runtest

# Add default versions of config files
COPY ./default-config/app/local.config.php /tmp/config/local.config.php

# Fix line endings so that windows clients can properly use
RUN dos2unix /usr/bin/docker-run.sh /usr/bin/runtest /tmp/config/local.config.php

# Runtime
ENTRYPOINT ["/usr/bin/dumb-init", "--"]

CMD ["/bin/bash", "/usr/bin/docker-run.sh"]
