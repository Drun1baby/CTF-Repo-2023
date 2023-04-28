# redpwn/jail v0.3.1
# See https://github.com/redpwn/jail#competitor-faq
# tl;dr nothing special here, just a glorified xinetd
FROM pwn.red/jail@sha256:cacd8a66d681647ab1b6740b979f55e5788c135d6272f8c8ee5d62e8cfbc167c

COPY --from=gcr.io/distroless/cc-debian11 / /srv
COPY bin/snapshot_blob.bin /srv/app/snapshot_blob.bin
COPY bin/d8 /srv/app/run

ENV JAIL_MEM=64M
ENV JAIL_TMP_SIZE=32M
