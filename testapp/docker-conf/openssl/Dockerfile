FROM alpine

RUN apk update && \
  apk add --no-cache openssl && \
  rm -rf /var/cache/apk/*

COPY gencerts /bin/gencerts

RUN chmod +x /bin/gencerts

ENTRYPOINT ["/bin/gencerts"]