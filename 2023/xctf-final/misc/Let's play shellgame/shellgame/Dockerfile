FROM ubuntu:22.04 AS app
FROM pwn.red/jail
COPY --from=app / /srv

COPY shellgame /srv/app/run
RUN chmod +x /srv/app/run
RUN mkdir /srv/app/flag

ENV JAIL_PORT=11451
