FROM ubuntu:16.04

ENV ACCEPT_EULA=y

ENV PATH="/opt/mssql-tools/bin:${PATH}"

RUN apt-get update -y && \
    DEBIAN_FRONTEND=noninteractive \
    apt-get install -y \
    software-properties-common curl \
    apt-transport-https locales xmlstarlet dos2unix

RUN echo "nb_NO.UTF-8 UTF-8" > /etc/locale.gen && \
    echo "en_US.UTF-8 UTF-8" >> /etc/locale.gen && \
    locale-gen

RUN curl https://packages.microsoft.com/keys/microsoft.asc | apt-key add -  && \
    curl https://packages.microsoft.com/config/ubuntu/16.04/prod.list > /etc/apt/sources.list.d/msprod.list && \
    echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> /root/.bashrc && \
    DEBIAN_FRONTEND=noninteractive  \
    apt-get update && apt-get install -y mssql-tools unixodbc-dev && \
    rm -rf /var/lib/apt/lists/*

COPY scripts /opt/scripts

# Fix line endings so that windows clients can properly use
RUN find /opt/ -type f -print0 | xargs -0 dos2unix
