# CoreDB Framework — Agent Instructions

CoreDB is a PHP MVC framework with its own ORM, convention-based routing, YAML-defined database schema,
auto-generated admin CRUD and Twig theming. This repository is the framework core plus an almost empty
`App/` layer: projects are started from it and put their own code under `App/`.

## Ground rules

- PHP **8.4** exactly: `Pdo\Mysql` needs ≥ 8.4 and `kreait/firebase-php` supports ≤ 8.4.
  `composer.json` pins `config.platform.php` to 8.4 so the lock file always matches the Docker image.
- Run PHP and console commands **inside the container**: `docker compose exec app <command>`.
- Code style is PSR-12. `vendor/bin/phpcs` checks `App/` by default (see `phpcs.xml`).
- Never commit secrets. Only `.env` is tracked, and it holds public development defaults.
- After changing tables or data in the admin UI, run `config:export` so the YAML in `config/` stays the source of truth.

## Configuration (environment variables)

There is no `config/config.php` anymore. Configuration comes from environment variables and `.env` files,
loaded by `Kernel/Environment.php` (Symfony Dotenv) at the start of `bootstrap.php`.

| File | Tracked | Purpose |
|---|---|---|
| `.env` | yes | Blueprint with working defaults for local Docker. No real secrets. |
| `.env.local` | no | Overrides for one machine, all environments. |
| `.env.{APP_ENV}.local` | no | Overrides for one environment, e.g. `.env.prod.local`. |

Load order, later wins: `.env` → `.env.local` → `.env.{APP_ENV}` → `.env.{APP_ENV}.local` → real environment variables.
Real variables (from docker compose or CI) override every file. `$_ENV += getenv()` in `Environment::load()`
exists because Apache does not expose container variables to Dotenv on its own.

`Environment::load()` turns the variables into the global constants the framework uses
(`DB_SERVER`, `HASH_SALT`, `LANGUAGE`, `THEME`, `PWA_MANIFEST`, …). Rules:

- `APP_ENV` is `dev`, `staging` or `prod`; anything else stops the application. Use `Environment::isDev()` /
  `Environment::isProd()` instead of comparing strings.
  - `dev`: Twig debug, no Twig/view cache, mails go to the test address.
  - `staging`: caches on, mails go to the test address.
  - `prod`: caches on, mails go to real recipients.
- `DB_SERVER`, `DB_NAME`, `DB_USER` and `HASH_SALT` are required.
- `HASH_SALT` encrypts `EncryptedModel` fields and JWT tokens. Changing it makes existing encrypted data unreadable.
  With `APP_ENV=prod` the app refuses to start if it still equals the public value in `.env`.
- Booleans accept `true/false/1/0`. Lists are comma separated (`LOGIN_POLICY_ROLES=Admin,User`).
  Relative paths (`FIREBASE_CREDENTIALS_PATH`) resolve from the project root.
- Quote values containing spaces, `#` or `$` (`PWA_THEME_COLOR="#fff"`).

To add a setting: add it with a comment to `.env`, define the constant in `Environment::load()`
(with the right type helper), then use the constant in code.

## Docker

| File | Purpose |
|---|---|
| `Dockerfile` | `php:8.4-apache`; targets `dev` (code bind-mounted) and `prod` (code and vendor baked in) |
| `compose.yml` | Shared `app` service, port `${APP_PORT:-8000}` |
| `compose.override.example.yml` | Local only: bind mount, MySQL 8.4, phpMyAdmin, first admin user. Copy to `compose.override.yml`. |
| `compose.prod.yml` | Production: `APP_ENV=prod`, MySQL on the host via `host.docker.internal`, mounts `.env.prod.local` |
| `docker/entrypoint.sh` | Installs vendor if missing, waits for the database, installs it when empty, starts Apache |
| `docker/apache/vhost.conf` | Document root `public_html`; uses `.htaccess` if present, otherwise `.htaccess_example` |
| `docker/php/app.ini` | PHP limits (memory, upload size, …) |

Local: `cp compose.override.example.yml compose.override.yml && docker compose up`.
App on http://localhost:8000 (`admin` / `admin`), phpMyAdmin on http://localhost:8080, MySQL on host port 3307.

Docker compose fills `${...}` in compose files from `.env` only, not from `.env.local`.
MySQL credentials for the local container therefore come from `.env`.

## Installation and database

There is no web installer. The entrypoint runs `config:import` only when the database has no `users` table,
then `user:add-admin` if `ADMIN_USERNAME` and `ADMIN_PASSWORD` are set (only in the local override).
`config:import` is not run on every start because it rewrites table structure from YAML and would drop
admin UI changes that were not exported yet. If the tables are missing, web requests return 503.

## Architecture

- **Namespaces**: `Src\` (core, in `Kernel/Src/`) and `App\` (project code, in `App/`).
  An `App\` class with the same relative name overrides the `Src\` one.
- **Routing by convention**: `/profile` → `App\Controller\ProfileController`, falling back to `Src\Controller\ProfileController`;
  `/admin/users` → `...\Controller\Admin\UsersController`; `/api/data` → `...\Controller\Api\DataController`.
- **ORM**: tables defined in `config/table_structure/*.yml`, models extend `Model`.
- **Admin UI**: auto-generated CRUD for every entity.
- **Viewable queries**: custom SQL exposed as searchable models.

### Directory layout

| Path | Contents |
|---|---|
| `public_html/` | Web root (`index.php`, assets, `files/` uploads) |
| `Kernel/` | Framework core (`CoreDB\Kernel\`) |
| `Kernel/Src/` | Default implementations (`Src\`): controllers, entities, forms, views, theme, commands |
| `App/` | Project-specific code and overrides (`App\`) |
| `config/` | YAML configuration, schema, dump data, translations |
| `bin/console.php` | Console entry point |
| `docker/` | Container configuration |

## Controllers

Extend `BaseController` for pages or `ServiceController` for APIs.

```php
public function checkAccess(): bool        // Authorization
public function preprocessPage()           // Set up data, forms, title
public function echoContent()              // Main content
public function getTemplateFile(): string  // Twig template
```

Access control examples: `\CoreDB::currentUser()->isLoggedIn()`, `\CoreDB::currentUser()->isAdmin()`.

## Entities and database

### Creating a table

1. Admin → "Tablolar" → "Yeni tablo". Use `snake_case` for table and column names.
2. Pick data types (see below).
3. "Tabloyu sınıf olarak dışa aktar" exports a PHP model; put it in `App/Entity/`.
4. Add relationships to `config/entity_config.yml` if needed.
5. Run `config:export`.

### Table structure YAML (`config/table_structure/{table}.yml`)

```yaml
table_name: users
table_comment: 'Contains site Users fundemantal data. Connected with User class.'
fields:
  username:
    type: short_text
    column_name: username
    primary_key: false
    autoIncrement: false
    isNull: false
    isUnique: true
    default: null
    comment: Username
    length: '20'
  status:
    type: enumarated_list
    column_name: status
    values:
      active: active
      blocked: blocked
      banned: banned
```

Field properties: `type`, `isNull`, `isUnique`, `primary_key`, `autoIncrement`, `default`, `comment`,
`length` (text), `values` (enumerated lists), `reference_table` (foreign keys).
The English `table_comment` is also a translation key in `config/translations/*.yml`; change them together.

### Data types (`Kernel/Database/DataType/`)

- Basic: `UnsignedBigInteger` (auto-increment keys), `Integer`, `FloatNumber`, `ShortText` (needs `length`), `Text`, `LongText`, `Checkbox`
- Date/time: `Date` (Y-m-d), `DateTime` (Y-m-d H:i:s), `Time` (H:i:s)
- Special: `EnumaratedList` (generates constants), `TableReference` (foreign key), `File` (reference to `files`)

Each type provides a form widget (`getWidget()`), a search widget (`getSearchWidget()`), validation and translated labels.

### Entity configuration (`config/entity_config.yml`)

```yaml
users:
  class: Src\Entity\User
  manyToMany:
    roles:
      mergeTable: "users_roles"
      selfKey: "user_id"
      foreignKey: "role_id"
```

Relationships: `oneToOne` / `oneToMany` with `foreignKey`; `manyToMany` with `mergeTable`, `selfKey`, `foreignKey`, `createIfNotExist`.
N-1 is not supported; model it as 1-N to avoid circular dependencies.

### Models

Models extend `Model`: `save()`, `delete()`, `get()`, `getAll()`, `getForm()`, search through `SearchableInterface`.

```php
$users = User::getAll(["status" => User::STATUS_ACTIVE]);
$user = User::get(["email" => $email]);
```

Admin list/edit screens are under "Varlıklar" → `<entity_name>`. Give entities readable names with translation keys matching the entity name.

### Viewable queries

1. "Varlıklar" → "Görüntülenebilir sorgular": define SQL, filters, result columns, page size and template.
2. Wrap it in a class:

```php
class BlogQuery extends ViewableQueries
{
    public static function getInstance()
    {
        return parent::getByKey("blog_records");
    }
}
```

## Forms

Extend `\Src\Form\Form` and implement `getFormId()` (also used for CSRF), `validate()` and `submit()`;
`getTemplateFile()` is optional. In the controller:

```php
$this->form = new SomeForm();
$this->form->processForm(); // submit, validation, redirect
```

- CSRF is handled automatically. Errors: `setError($field, $message)`. Input: `$this->request[$field]`.
- Widgets: `InputWidget::create("email")->setType("email")->setLabel(...)->addClass(...)->addAttribute(...)`.
- `addClass("html-editor")` turns a textarea into a TinyMCE editor.

## Views and themes

- Theme lookup: `App\Theme\AppTheme` → `Src\BaseTheme\BaseTheme`; the active theme is the `THEME` variable.
- Templates: `page.twig`, `page-login.twig`, `forms/`, `widgets/`, `views/`.
- Views extend `\Src\Theme\View` and return a template from `getTemplateFile()`.
  Compose them with `ViewGroup::create("div", "wrapper-class")->addField($view)`.

## Translations

- PHP: `Translation::getTranslation("key")`
- Twig: `{{ t("key") }}`
- JavaScript: `_t("key")` after `$this->addFrontendTranslation("key")` in the controller
- Stored in the `translations` table, exported to `config/translations/{lang}.yml`.
  Without database tables, translations are read from the YAML files.

## Console commands

Run with `docker compose exec app php bin/console.php <command>`.

| Command | Purpose |
|---|---|
| `config:import` | Create/update tables from `config/table_structure`, import `config/table_dump_data` and translations |
| `config:export` | Export database structure, dump tables and translations back to `config/` |
| `user:add-admin <username> <email> <name> <password>` | Create an admin user |
| `clear:cache` | Clear the cache table and the Twig cache directory |
| `clear:temporary-files` | Delete temporary uploads older than one hour |
| `image:compress` | Compress uploaded images |
| `notifications:send` / `notifications:test` | Send pending / test push notifications |
| `schedule:run` | Run jobs in `config/scheduled_jobs.yml`; must be triggered every minute (cron) |
| `cdb:serve` | PHP built-in server on localhost:8000 (not needed with Docker) |

## Workflows

**New feature**: create the table in the admin UI → export the model to `App/Entity/` → update `config/entity_config.yml`
→ add a controller in `App/Controller/` → add templates in `App/Theme/templates/` → test.

**Before committing**: `config:export`, then `vendor/bin/phpcbf` and `vendor/bin/phpcs` (PSR-12).

**Deploying** (production server):

```bash
git pull
docker compose -f compose.yml -f compose.prod.yml up -d --build
docker compose -f compose.yml -f compose.prod.yml exec app php bin/console.php config:import
docker compose -f compose.yml -f compose.prod.yml exec app php bin/console.php clear:cache
```

The Twig cache lives inside the container and starts empty with every new image.

## Security notes

- XSS: `CoreDB::cleanXSS()`. CSRF tokens are added to forms automatically. Queries go through the ORM's prepared statements.
- Sessions support "remember me" tokens and login policies (`LOGIN_POLICY`).
- Uploads live in `public_html/files/` and are served through `index.php`.
- `HTTP_AUTH_ENABLED` protects the whole site with basic auth (useful for staging).
