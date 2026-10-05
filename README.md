# CoreDB

CoreDB is a PHP MVC framework with its own ORM, convention-based routing, YAML-defined database schema,
an auto-generated admin panel and Twig theming. Start a project from this repository and put your code under `App/`.

Architecture and coding conventions are documented in [AGENTS.md](AGENTS.md).

## Quick start (Docker)

Requirements: Docker with Compose v2. Ports 80 and 443 must be free (stop any local Apache/nginx).

```bash
cp compose.override.example.yml compose.override.yml   # once
docker compose up
```

| Service | URL | Credentials |
|---|---|---|
| Application | https://localhost | `admin` / `admin` |
| phpMyAdmin | http://localhost:8080 | `core` / `core` |
| MySQL (from the host) | `127.0.0.1:3307` | `core` / `core` |

On the first start the app container waits for MySQL, creates the tables (`config:import`) and the admin user.
Your working copy is mounted into the containers, so code changes are live.
http://localhost redirects to https://localhost.

Console commands run inside the app container:

```bash
docker compose exec app php bin/console.php list
```

phpMyAdmin and MySQL ports can be changed with `PMA_PORT` and `MYSQL_PORT`, e.g. `PMA_PORT=8081 docker compose up`.

### HTTPS certificate

On the first start nginx generates a self-signed certificate in `docker/nginx/certs/` (gitignored),
so the browser shows a warning once. For a certificate your browser trusts, use [mkcert](https://github.com/FiloSottile/mkcert):

```bash
brew install mkcert   # or see mkcert's README for Linux/Windows
mkcert -install
rm -f docker/nginx/certs/*.pem
mkcert -cert-file docker/nginx/certs/cert.pem -key-file docker/nginx/certs/key.pem localhost 127.0.0.1 ::1
docker compose restart web
```

## Configuration

Settings come from environment variables and `.env` files; there is no `config/config.php`.

| File | In git | Use it for |
|---|---|---|
| `.env` | yes | Defaults that work with the local Docker setup. Never put real secrets here. |
| `.env.local` | no | Your machine's overrides. |
| `.env.prod.local` | no | Production values (and `.env.staging.local` for staging). |

Later files override earlier ones, and real environment variables override all files.
Every variable is documented in [.env](.env).

## Production

Production uses the same images without the bundled MySQL and phpMyAdmin; the database runs on the host machine.
nginx serves HTTPS on 443 and redirects 80 to it.

1. Create `.env.prod.local` next to the compose files:

   ```dotenv
   # php -r 'echo bin2hex(random_bytes(32));'
   HASH_SALT=<random secret>
   DB_SERVER=host.docker.internal
   DB_NAME=core
   DB_USER=core
   DB_PASSWORD=<strong password>
   TRUSTED_HOSTS=example.com
   ```

   The application refuses to start in production with the public `HASH_SALT` from `.env`.
   Keep the salt stable: changing it makes encrypted data unreadable.

2. Prepare MySQL on the host so the container can reach it:

   ```sql
   CREATE DATABASE core CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   -- Containers connect from the Docker network, not from localhost.
   CREATE USER 'core'@'172.%' IDENTIFIED BY '<strong password>';
   GRANT ALL PRIVILEGES ON core.* TO 'core'@'172.%';
   ```

   - `172.%` covers Docker's default networks; check yours with `docker network inspect core_default`.
   - MySQL's `bind-address` must accept connections from the Docker bridge (not only `127.0.0.1`).
   - `TIMEZONE` uses named time zones, so load them once:
     `mysql_tzinfo_to_sql /usr/share/zoneinfo | mysql -u root mysql`

3. Put the TLS certificate (full chain) and its private key into `docker/nginx/certs/` as `cert.pem` and `key.pem`.
   With Let's Encrypt, for example:

   ```bash
   cp /etc/letsencrypt/live/example.com/fullchain.pem docker/nginx/certs/cert.pem
   cp /etc/letsencrypt/live/example.com/privkey.pem docker/nginx/certs/key.pem
   ```

   nginx refuses to start without them; production never falls back to a self-signed certificate.
   Port 80 only redirects, so obtain certificates with certbot's DNS challenge, or with `--standalone`
   while the `web` container is stopped. After a renewal, copy the files again and reload nginx:
   `docker compose -f compose.yml -f compose.prod.yml exec web nginx -s reload`

4. Start it:

   ```bash
   docker compose -f compose.yml -f compose.prod.yml up -d --build
   ```

   An empty database is installed automatically. Create the first admin user yourself:

   ```bash
   docker compose -f compose.yml -f compose.prod.yml exec app \
     php bin/console.php user:add-admin <username> <email> "<name>" <password>
   ```

5. Scheduled jobs (`config/scheduled_jobs.yml`) need a cron entry on the host:

   ```cron
   * * * * * cd /path/to/project && docker compose -f compose.yml -f compose.prod.yml exec -T app php bin/console.php schedule:run
   ```

6. Push notifications with Firebase need the service account file mounted into the container.
   Add it to the `volumes` of the `app` service in `compose.prod.yml`:

   ```yaml
   - ./config/firebase-service-account.json:/var/www/app/config/firebase-service-account.json:ro
   ```

Uploaded files are kept in the `uploads` Docker volume.

### Deploying an update

```bash
git pull
docker compose -f compose.yml -f compose.prod.yml up -d --build
docker compose -f compose.yml -f compose.prod.yml exec app php bin/console.php config:import
docker compose -f compose.yml -f compose.prod.yml exec app php bin/console.php clear:cache
```

## Development workflow

Database structure and default data live in `config/` as YAML. Before every commit:

1. Export changes made in the admin panel:
   `docker compose exec app php bin/console.php config:export`
2. Check the code style (PSR-12, `App/` by default):
   `docker compose exec app vendor/bin/phpcbf` then `docker compose exec app vendor/bin/phpcs`

## Running without Docker

Requirements: PHP 8.4 with `pdo_mysql`, `gd`, `exif`, `gmp`, `bcmath`, `intl`, `mbstring`, `zip`;
nginx with PHP-FPM, or Apache with `mod_rewrite`; MySQL 8; Composer.

1. `composer install`
2. Point the web server's document root to `public_html/`:
   - nginx: start from `docker/nginx/default.conf` (change `fastcgi_pass` and the certificate paths).
     If you use PHP-FPM with environment variables, set `clear_env = no` as in `docker/php/fpm-pool.conf`.
   - Apache: enable `AllowOverride All` and copy `public_html/.htaccess_example` to `public_html/.htaccess`.
3. Create `.env.local` with your database settings (`DB_SERVER=127.0.0.1`, `DB_NAME`, `DB_USER`, `DB_PASSWORD`).
4. `php bin/console.php config:import`
5. `php bin/console.php user:add-admin <username> <email> "<name>" <password>`
6. Make `cache/` and `public_html/files/` writable by the web server.

## License

See [LICENSE](LICENSE).
