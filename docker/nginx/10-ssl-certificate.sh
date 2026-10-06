#!/bin/sh
# Run by the nginx image entrypoint before nginx starts.
# Uses the certificate in /etc/nginx/certs when present. Otherwise, only when
# SSL_SELF_SIGNED=true (local development), generates a self-signed one.
set -e

cert=/etc/nginx/certs/cert.pem
key=/etc/nginx/certs/key.pem

if [ -f "$cert" ] && [ -f "$key" ]; then
    exit 0
fi

if [ "$SSL_SELF_SIGNED" != "true" ]; then
    echo "Missing $cert and $key. Mount your TLS certificate into /etc/nginx/certs." >&2
    exit 1
fi

echo "Generating a self-signed certificate for localhost"
openssl req -x509 -nodes -newkey rsa:2048 -days 825 \
    -subj "/CN=localhost" \
    -addext "subjectAltName=DNS:localhost,IP:127.0.0.1,IP:::1" \
    -keyout "$key" -out "$cert" 2>/dev/null
