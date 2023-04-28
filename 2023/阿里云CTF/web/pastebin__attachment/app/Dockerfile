FROM phusion/baseimage:focal-1.2.0
ENV DEBIAN_FRONTEND=noninteractive

COPY ./ /app

RUN sed -i "s@http://.*archive.ubuntu.com@http://mirrors.bfsu.edu.cn@g" /etc/apt/sources.list && \
sed -i "s@http://.*security.ubuntu.com@http://mirrors.bfsu.edu.cn@g" /etc/apt/sources.list

RUN apt-get update \
	&& apt-get install -y man ca-certificates lsb-release curl build-essential wget software-properties-common mongodb

# install redis
RUN wget -O /tmp/redis.tar.gz https://download.redis.io/releases/redis-6.2.10.tar.gz && tar -C /app/ -xzf /tmp/redis.tar.gz && cd /app/redis-6.2.10 && make && make install PREFIX=/usr/local/redis && ln -sf /usr/local/redis/bin/redis-server /usr/local/bin/redis-server

# install node
RUN wget -O /tmp/node.tar.gz https://registry.npmmirror.com/-/binary/node/latest-v18.x/node-v18.13.0-linux-x64.tar.gz && tar -C /app/ -xzf /tmp/node.tar.gz && ln -sf /app/node-v18.13.0-linux-x64/bin/node /usr/local/bin/node && ln -sf /app/node-v18.13.0-linux-x64/bin/npm /usr/local/bin/npm

# install golang && caddy
RUN apt install -y debian-keyring debian-archive-keyring apt-transport-https && curl -1sLf 'https://dl.cloudsmith.io/public/caddy/xcaddy/gpg.key' | gpg --dearmor -o /usr/share/keyrings/caddy-xcaddy-archive-keyring.gpg && \
curl -1sLf 'https://dl.cloudsmith.io/public/caddy/xcaddy/debian.deb.txt' | tee /etc/apt/sources.list.d/caddy-xcaddy.list && \
apt update && apt install xcaddy

WORKDIR /app/caddy
RUN wget -O /tmp/golang.tar.gz https://go.dev/dl/go1.19.5.linux-amd64.tar.gz && tar -C /usr/local -xzf /tmp/golang.tar.gz

RUN export PATH=$PATH:/usr/local/go/bin && go env -w GO111MODULE=on && go env -w GOPROXY=https://goproxy.cn,direct && xcaddy build v2.6.4 --with github.com/caddyserver/cache-handler

# RUN export PATH=$PATH:/usr/local/go/bin && go env -w GO111MODULE=on && go env -w GOPROXY=https://mirrors.aliyun.com/goproxy/,direct && xcaddy build v2.6.4 --with github.com/caddyserver/cache-handler

RUN mkdir -p /etc/service/redis/ &&\
    printf "#!/bin/sh\nexec redis-server /app/redis/redis.conf >> /app/redis/log 2>&1" > /etc/service/redis/run &&\
    chmod +x /etc/service/redis/run &&\
    mkdir -p /etc/service/caddy/ &&\
    printf "#!/bin/sh\nexec /app/caddy/caddy run --config /app/caddy/Caddyfile >> /app/caddy/log 2>&1" > /etc/service/caddy/run &&\
    chmod +x /etc/service/caddy/run &&\
    mkdir -p /etc/service/mongod/ &&\
    printf "#!/bin/sh\nexec mongod --bind_ip 127.0.0.1 2>&1" > /etc/service/mongod/run &&\
    chmod +x /etc/service/mongod/run &&\
    mkdir -p /etc/service/node/ &&\
    printf "#!/bin/sh\ncd /app/src/ && exec npm run start >> /app/log 2>&1" > /etc/service/node/run &&\
    chmod +x /etc/service/node/run &&\
    chmod 700 /etc/service/node/run /etc/service/caddy/run /etc/service/redis/run /etc/service/mongod/run

RUN mkdir -p /data/db

WORKDIR /app/src/
RUN npm config set registry https://registry.npmmirror.com && npm install -g npm@9.4.1 && npm install

RUN find /app/ -maxdepth 1 -type f -delete

ENV LPORT=3000 BIND_ADDR=127.0.0.1
EXPOSE 3000
EXPOSE 4000