#!/bin/sh
set -e

cd /var/www/app

# Development bind-mounts the project, so vendor/ may not exist yet.
if [ ! -f vendor/autoload.php ]; then
    composer install --no-interaction --prefer-dist
fi

mkdir -p cache public_html/files/uploaded
chown www-data:www-data cache public_html/files/uploaded

# Wait for the database, then install it only when it is empty.
# config:import is not run on every start: it would overwrite table changes
# made in the admin UI that are not exported yet.
tries=0
while :; do
    code=0
    status=$(php docker/db-status.php 2>/tmp/db-status.err) || code=$?
    if [ "$code" -eq 0 ]; then
        break
    fi
    # Only connection errors (exit 1) are worth retrying; show anything else right away.
    tries=$((tries + 1))
    if [ "$code" -ne 1 ] || [ "$tries" -ge 30 ]; then
        cat /tmp/db-status.err >&2
        exit 1
    fi
    echo "Waiting for the database..."
    sleep 2
done

if [ "$status" = "empty" ]; then
    echo "Empty database, installing..."
    php bin/console.php config:import
    if [ -n "$ADMIN_USERNAME" ] && [ -n "$ADMIN_PASSWORD" ]; then
        php bin/console.php user:add-admin "$ADMIN_USERNAME" "$ADMIN_EMAIL" "$ADMIN_NAME" "$ADMIN_PASSWORD"
    fi
fi

exec "$@"
