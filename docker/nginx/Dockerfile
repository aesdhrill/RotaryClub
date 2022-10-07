ARG version=1.23.1

FROM nginx:${version}-alpine AS builder

ARG version

WORKDIR /root/

RUN touch /tmp/.nginx_args \
    && nginx -V 2>&1 | grep 'configure arguments:' | sed 's/configure arguments: //' > /tmp/.nginx_args \
    && cat /tmp/.nginx_args

RUN apk add --update --no-cache build-base git pcre-dev openssl-dev zlib-dev linux-headers \
    && wget https://nginx.org/download/nginx-${version}.tar.gz \
    && tar zxf nginx-${version}.tar.gz \
    && git clone https://github.com/google/ngx_brotli.git --recursive \
    && cd nginx-${version} \
    && CC_OPTS=$(cat /tmp/.nginx_args) \
    && ./configure --add-dynamic-module=../ngx_brotli --with-compat "$CC_OPTS" \
    && make modules

RUN ls -lah /usr/sbin/nginx

FROM nginx:${version}-alpine

ARG version

COPY --from=builder /root/nginx-${version}/objs/ngx_http_brotli_filter_module.so /usr/lib/nginx/modules/
COPY --from=builder /root/nginx-${version}/objs/ngx_http_brotli_static_module.so /usr/lib/nginx/modules/
