FROM maven:3.6.3-openjdk-8
ENV MAXWELL_VERSION=1.23.2 KAFKA_VERSION=1.0.0

COPY ./maxwell-$MAXWELL_VERSION /usr/local/maxwell
RUN cd /usr/local/maxwell \
    && chmod +x bin/maxwell* \
    && echo "$MAXWELL_VERSION" > /REVISION

ENV MAXWELL_OPTIONS="--config=/usr/local/maxwell/config.properties"


WORKDIR /app

ENTRYPOINT [ "/usr/local/maxwell/bin/maxwell-docker" ]
