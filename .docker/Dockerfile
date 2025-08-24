FROM php:8.3-cli-alpine

ARG HOST_UID=1000
ARG HOST_GID=1000

RUN addgroup -g ${HOST_GID} cronjob && \
    adduser -u ${HOST_UID} -G cronjob -s /bin/ash -D cronjob

COPY --from=composer /usr/bin/composer /usr/bin/composer

WORKDIR /usr/src/myapp
RUN chown -R cronjob:cronjob .
USER cronjob

ENTRYPOINT [ "sleep", "infinity" ]