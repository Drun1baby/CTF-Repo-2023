FROM phusion/baseimage:focal-1.2.0
ENV DEBIAN_FRONTEND=noninteractive

# dir
COPY src /app
WORKDIR /app

RUN sed -i "s@http://.*archive.ubuntu.com@http://mirrors.bfsu.edu.cn@g" /etc/apt/sources.list && \
sed -i "s@http://.*security.ubuntu.com@http://mirrors.bfsu.edu.cn@g" /etc/apt/sources.list

RUN apt-get update \
	&& apt-get install -y man ca-certificates curl wget ca-certificates fonts-liberation libappindicator3-1 libasound2 libatk-bridge2.0-0 libatk1.0-0 libc6 libcairo2 libcups2 libdbus-1-3 libexpat1 libfontconfig1 libgbm1 libgcc1 libglib2.0-0 libgtk-3-0 libnspr4 libnss3 libpango-1.0-0 libpangocairo-1.0-0 libstdc++6 libx11-6 libx11-xcb1 libxcb1 libxcomposite1 libxcursor1 libxdamage1 libxext6 libxfixes3 libxi6 libxrandr2 libxrender1 libxss1 libxtst6 lsb-release xdg-utils net-tools iproute2 inetutils-ping tcpdump
RUN wget -O /tmp/node.tar.gz https://registry.npmmirror.com/-/binary/node/latest-v18.x/node-v18.13.0-linux-x64.tar.gz && tar -C /app/ -xzf /tmp/node.tar.gz && ln -sf /app/node-v18.13.0-linux-x64/bin/node /usr/local/bin/node && ln -sf /app/node-v18.13.0-linux-x64/bin/npm /usr/local/bin/npm

RUN mkdir -p /etc/service/bot/ &&\
    printf "#!/bin/sh\ncd /app && exec npm run start >> /app/log 2>&1" > /etc/service/bot/run &&\
    chmod +x /etc/service/bot/run &&\
    chmod 700 /etc/service/bot/run

RUN npm config set puppeteer_download_host=https://cdn.npmmirror.com/binaries && npm --registry https://registry.npmmirror.com install

ENV HOST=web PORT=4000
EXPOSE 3000