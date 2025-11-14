# CoreDB Framework - AI Coding Agent Instructions

## Architecture Overview

CoreDB is a PHP-based MVC framework with custom ORM and routing. Key architectural patterns:

- **Dual namespace system**: `Src\` (core) and `App\` (application-specific overrides)
- **Convention-based routing**: URL paths map to controller namespaces automatically
- **Entity-centric ORM**: Database tables defined via YAML configs, auto-generated Models
- **Twig-based theming**: Template inheritance with theme overrides
- **Configuration-driven**: YAML files control entities, commands, extensions
- **Admin UI generation**: Auto-generated CRUD interfaces for entities via web UI
- **Viewable Queries**: Custom SQL queries exposed as searchable models

## Table Creation Workflow

### Via Admin Interface
1. Navigate to "Tablolar" → "Yeni tablo" in admin
2. Use `snake_case` for table/column naming
3. Select data types: Integer, Decimal, Checkbox, Short Text, Text, Long Text/HTML, Date, DateTime, Time, File, Related Table, List
4. Export as PHP class: "Tabloyu sınıf olarak dışa aktar" button
5. Place generated model in `App/Entity/` directory

### Data Type System
CoreDB uses a comprehensive data type system located in `Kernel/Database/DataType/`:

**Basic Types:**
- `UnsignedBigInteger` - Auto-incrementing primary keys
- `Integer` - Standard integers
- `FloatNumber` - Decimal/floating point numbers
- `ShortText` - VARCHAR fields (requires `length` property)
- `Text` - TEXT fields for medium content
- `LongText` - LONGTEXT fields for large content/HTML
- `Checkbox` - Boolean fields

**Date/Time Types:**
- `Date` - Date only (Y-m-d)
- `DateTime` - Full timestamp (Y-m-d H:i:s)
- `Time` - Time only (H:i:s)

**Specialized Types:**
- `EnumaratedList` - Enum with predefined values (generates constants)
- `TableReference` - Foreign key to another table
- `File` - File upload/reference (extends TableReference to 'files' table)

**Data Type Features:**
- Auto-generate form widgets via `getWidget()` method
- Search widget support via `getSearchWidget()` method
- Value validation and sanitization
- Translation support for labels/descriptions
- Automatic constraint handling

## Controller Patterns

### Routing Convention
URLs map to controllers via namespace transformation:
- `/profile` → `App\Controller\ProfileController` → `Src\Controller\ProfileController` (fallback)
- `/admin/users` → `App\Controller\Admin\UsersController`
- `/api/data` → `App\Controller\Api\DataController`

Always extend `BaseController` (pages) or `ServiceController` (APIs).

### Required Controller Methods
```php
public function checkAccess(): bool  // Authorization logic
public function preprocessPage()     // Setup data, forms, titles  
public function echoContent()       // Return main content
public function getTemplateFile(): string // Twig template name
```

## Entity & Database Patterns

### Entity Configuration
Entities are defined in `config/entity_config.yml`:
```yaml
users:
  class: Src\Entity\User
  manyToMany:
    roles:
      mergeTable: "users_roles"
      selfKey: "user_id" 
      foreignKey: "role_id"
```

### Relationship Types
- **One-to-One**: `oneToOne` with `foreignKey`
- **One-to-Many**: `oneToMany` with `foreignKey`  
- **Many-to-Many**: `manyToMany` with `mergeTable`, `selfKey`, `foreignKey`, `createIfNotExist`
- **Note**: N-1 relationships not supported (use 1-N instead to avoid circular dependencies)

### Model Inheritance
All entities extend `Model` class:
- Auto-generated fields based on table structure
- Built-in CRUD via `save()`, `delete()`, `get()`, `getAll()`
- Form generation via `getForm()` method
- Search/filter support through `SearchableInterface`
- Export as PHP: Generated models include data type imports and constants for enums

### Admin Entity Management
- Auto-generated list/edit screens at "Varlıklar" → `<entity_name>`
- Add user-friendly names via translation keys matching entity names

## Form & Validation System

### Form Creation Pattern
```php
// In controller preprocessPage():
$this->form = new SomeForm();
$this->form->processForm(); // Handles submit, validation, redirect
```

### Form Structure (extends `\Src\Form\Form`)
Required methods:
```php
abstract public function getFormId(): string;    // Unique form ID for CSRF
abstract public function validate(): bool;       // Validate submitted data
abstract public function submit();               // Handle successful submission
public function getTemplateFile(): string;       // Twig template (optional override)
```

### Form Features
- CSRF protection automatically handled
- Validation via `validate()` method  
- Success handling via `submit()` method
- Field widgets (InputWidget, TextareaWidget, etc.)
- Error handling: `setError($field, $message)`
- Request data: `$this->request[$field_name]`

### FormWidget Components
- Create widgets: `InputWidget::create("field_name")->setType("email")`
- Chain methods: `->setLabel()->addClass()->addAttribute()`
- HTML Editor: `addClass("html-editor")` for TinyMCE integration

## Theme Architecture

### Theme Hierarchy
1. App-specific theme: `App\Theme\AppTheme`
2. Base theme: `Src\BaseTheme\BaseTheme`
3. Template lookup: `templates/` directory in theme

### Template Structure
- `page.twig` - Main page wrapper
- `page-login.twig` - Login-specific layout  
- `forms/` - Form templates
- `widgets/` - Form field templates
- `views/` - Component templates

## Key Development Commands

```bash
# Install dependencies
composer install

# Database operations  
php bin/console.php config:import   # Import DB structure
php bin/console.php config:export   # Export DB changes
php bin/console.php user:add-admin  # Create admin user

# Cache management
php bin/console.php clear:cache     # Clear system cache
php bin/console.php clear:temporary-files  # Clean temp files

# Image optimization
php bin/console.php image:compress  # Compress uploaded images

# Code standards (PSR-12)
phpcs --standard=PSR12 <file>
phpcbf --standard=PSR12 <file>

# Development server
php bin/console.php serve
```

## Complete Development Workflow

### For New Features
1. Create database table via admin "Tablolar" → "Yeni tablo"
2. Export as PHP model: "Tabloyu sınıf olarak dışa aktar"
3. Place model in `App/Entity/` directory
4. Update `config/entity_config.yml` if relationships needed
5. Create controller in `App/Controller/`
6. Add templates in `App/Theme/templates/`
7. Test functionality

### Before Git Commit
1. Export config changes: `php bin/console.php config:export`
2. Run code standards: `phpcbf --standard=PSR12 App/` then `phpcs --standard=PSR12 App/`
3. Commit changes

### Deployment Workflow
```bash
git pull
php bin/console.php clear:cache
php bin/console.php config:import
php bin/console.php clear:temporary-files
rm -r cache/
```

## Configuration System

### Environment Settings (`config/config.php`)
- `ENVIRONMENT`: "development" | "staging" | "production"
- `THEME`: Theme class constant
- `LANGUAGE`: Default language code
- `TRUSTED_HOSTS`: Security configuration

### Key Configuration Files
- `config/entity_config.yml` - Entity relationships
- `config/commands.yml` - Console command registration
- `config/translations/` - Multi-language support
- `config/table_structure/` - Database schema definitions
- `config/dump_tables.yml` - Tables exported as config data
- `config/xmlsitemap_config.yml` - XML sitemap entity definitions

### Table Structure Configuration
Database schemas are defined in `config/table_structure/{table_name}.yml` files:

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

**Field Properties:**
- `type`: Data type (see Data Types section)
- `isNull`: Allow NULL values
- `isUnique`: Unique constraint
- `primary_key`: Primary key field
- `autoIncrement`: Auto-incrementing field
- `default`: Default value
- `comment`: Field description
- `length`: For text fields
- `values`: For enumerated lists
- `reference_table`: For foreign keys

## Common Patterns

### Override Core Functionality
Create `App\` equivalent of any `Src\` class to override behavior.

### Access Control
```php
public function checkAccess(): bool {
    return \CoreDB::currentUser()->isLoggedIn();
    // or \CoreDB::currentUser()->isAdmin();
}
```

### Database Queries
```php
$users = User::getAll(['status' => User::STATUS_ACTIVE]);
$user = User::get(['email' => $email]);
```

### Translations
```php
Translation::getTranslation("key_name");
// Defined in config/translations/{lang}.yml
```

### Multi-language Support
- **PHP**: `Translation::getTranslation("translation_key")`
- **Twig**: `{{ t("translation_key") }}`
- **JavaScript**: `_t("translation_key")` (after publishing: `$this->addFrontendTranslation("key")`)

## Views & Components

### View Creation (extends `\Src\Theme\View`)
```php
class CustomView extends View {
    public function getTemplateFile(): string {
        return "custom-view.twig"; // Located in templates/views/
    }
}
```

### ViewGroup for Layouts
```php
$group = ViewGroup::create("div", "wrapper-class");
$group->addField($someView);
$group->addField($anotherView);
```

## Viewable Queries (Custom Reports)

### Creating SQL-based Models
1. Go to "Varlıklar" → "Görüntülenebilir sorgular"
2. Define custom SQL with filters and result columns
3. Set pagination limit and display template (Table/Custom Card)
4. Create wrapper class:
```php
class BlogQuery extends ViewableQueries {
    public static function getInstance() {
        return parent::getByKey("blog_records");
    }
}
```

### File Structure
- `public_html/` - Web root (assets, index.php)
- `Kernel/` - Framework core
- `Src/` - Default implementations  
- `App/` - Project-specific overrides
- `config/` - Configuration files
- `bin/` - Console tools

## Security Considerations
- XSS protection via `CoreDB::cleanXSS()`
- CSRF tokens in forms automatically
- SQL injection prevention through ORM
- Session management with "remember me" tokens
- File upload restrictions in `files/` directory

## Development Workflow
1. Create controller in `App\Controller\`
2. Define entity in `config/entity_config.yml` if needed
3. Create forms extending base `Form` class
4. Add templates in theme's `templates/` directory
5. Run code standards check before commit
6. Export config changes: `php bin/console.php config:export`