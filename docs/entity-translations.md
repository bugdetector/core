# Single-locale entity editing

The admin entity edit URL accepts `_translation_locale`. Without it, editing uses `Translation::getDefaultLanguage()`. Language links reload the same page; the admin UI language stays unchanged.

The default locale uses the original form and normal save behavior. Other locales use `EntityTranslationForm`: only saved parent/child text fields are editable. Create/delete, ordering, prices and other shared fields remain in the default-locale editor. New records must be saved there first. TreeForm edits the default language only; use the record's edit link to translate it.

The examples below assume a default locale of `tr`, with `en` also configured as an available language. Replace table names, IDs and locales with your project's values.

## Enable translations on an entity

The main table keeps default-language values. Create a `product_translations` table with `object_id` referencing `products`, `locale`, `field` and nullable `content` columns. See the entity translation schema example in [AGENTS.md](../AGENTS.md#entity-translations-translatableinterface--entitytranslation).

```php
namespace App\Entity\Product;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\TranslatableInterface;
use Src\Entity\EntityTranslation;
use Src\Traits\TranslatableTrait;

class Product extends Model implements TranslatableInterface
{
    use TranslatableTrait;

    public static function getTableName(): string
    {
        return 'products';
    }

    public static function getTranslatableFields(): array
    {
        return ['title', 'description'];
    }

    public static function getTranslationClass(): string
    {
        return ProductTranslation::class;
    }
}

// Put this class in its own ProductTranslation.php file.
class ProductTranslation extends EntityTranslation
{
    public static function getTableName(): string
    {
        return 'product_translations';
    }
}
```

Register the parent, child and their translation classes in `config/entity_config.yml`. The child classes are defined below. Merge these entries with any existing product relationships:

```yaml
products:
  class: App\Entity\Product\Product
  oneToMany:
    product_info:
      foreignKey: product
product_translations:
  class: App\Entity\Product\ProductTranslation
product_info:
  class: App\Entity\Product\ProductInfo
product_info_translations:
  class: App\Entity\Product\ProductInfoTranslation
```

## Read translations and select an object locale

```php
use App\Entity\Product\Product;

$product = Product::get(42); // An existing product.
if (!$product) {
    throw new RuntimeException('Product not found.');
}

// Use the current site language, with fallback to the main table.
$title = $product->getTranslatedField('title');

// Select a language for this instance's translation methods.
$product->setTranslatableLocale('en');
$title = $product->getTranslatedField('title');
$locale = $product->getTranslatableLocale(); // 'en'

// An explicit argument takes priority over the instance locale.
$sourceTitle = $product->getTranslatedField('title', 'tr');

// Editing: show an empty input when an English translation is missing.
$englishInput = $product->getTranslatedField('title', 'en', false);

// Normal field values are never replaced by selecting a locale.
$originalTitle = $product->title->getValue();
```

Without an instance locale or explicit argument, translation methods use the current site language. Setting the object locale does not change the admin UI language or propagate automatically to other objects.

Twig uses the same read method:

```twig
{{ product.getTranslatedField('title') }}
{{ product.getTranslatedField('description', 'en') }}
```

## Write one field

```php
// An existing product: non-default translations are written immediately.
$product->setTranslatableLocale('en');
$product->setTranslatedField('title', 'Shoes');

// Or specify the locale directly.
$product->setTranslatedField('description', 'Comfortable shoes.', 'en');

// Clear English content without deleting the translation row.
$product->setTranslatedField('description', '', 'en');

// The default language writes the main field; save the product afterwards.
$product->setTranslatedField('title', 'Ayakkabı', 'tr');
$product->save();
```

Changing ordinary fields and calling `save()` still runs the entity's normal business rules. Selecting `en` does not make `$product->title->setValue(...)` an English translation write; use the translation methods.

## Stage several fields or create a new entity

`setTranslations()` validates and stages a locale map. It accepts non-default locales only. `saveTranslations()` writes the staged values without calling the product's `save()`:

```php
$product->setTranslations([
    'en' => [
        'title' => 'Shoes',
        'description' => 'Comfortable shoes.',
    ],
]);
$product->saveTranslations();
```

For a new entity, fill its default-language fields first. Non-default translations wait until the entity has an ID:

```php
$product = new Product();
$product->title->setValue('Ayakkabı');
$product->description->setValue('Rahat ayakkabı.');
// Fill any other required fields defined by your project.
$product->setTranslatedField('title', 'Shoes', 'en');
$product->save(); // Saves the main record and flushes queued translations.
```

Supply a complete pending batch in one `setTranslations()` call: a subsequent call replaces the pending batch. Fields not included in the batch keep their existing database translations. Empty/null content clears a translation, and the string `"0"` is preserved. Unsupported locales, fields and value types are rejected.

## Nested entities

A parent does not need `TranslatableInterface` for its children to be translated. Each child declares its own fields and translation class. The admin translation form discovers saved children from the source form's visible collection cards and presents only their translatable fields.

For the configuration above, create `product_info` with a required `product` table reference to `products`, a `title` short-text field and a `description` long-text field. Create `product_info_translations` with the same translation schema as `product_translations`, but point `object_id` at `product_info`.

```php
namespace App\Entity\Product;

use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\Model;
use CoreDB\Kernel\TranslatableInterface;
use Src\Entity\EntityTranslation;
use Src\Traits\TranslatableTrait;

class ProductInfo extends Model implements TranslatableInterface
{
    use TranslatableTrait;

    public TableReference $product;
    public ShortText $title;
    public LongText $description;

    public static function getTableName(): string
    {
        return 'product_info';
    }

    public static function getTranslatableFields(): array
    {
        return ['title', 'description'];
    }

    public static function getTranslationClass(): string
    {
        return ProductInfoTranslation::class;
    }
}

// Put this class in its own ProductInfoTranslation.php file.
class ProductInfoTranslation extends EntityTranslation
{
    public static function getTableName(): string
    {
        return 'product_info_translations';
    }
}
```

Put these classes alongside `Product` and `ProductTranslation` under `App/Entity/Product/`, each in its own file.

Programmatic writes target the child itself:

```php
use App\Entity\Product\ProductInfo;

$info = ProductInfo::get(73);
if (!$info) {
    throw new RuntimeException('Product info not found.');
}
$info->setTranslatableLocale('en');
$info->setTranslatedField('title', 'Materials');
$info->setTranslatedField('description', 'Cotton.');
```

## Custom controllers

Core admin entity editing integrates the selector automatically. A custom admin controller can compose it with its existing form:

```php
use App\Entity\Product\Product;
use Src\Form\EntityTranslationForm;
use Src\Theme\View;

// Properties and methods inside your admin controller.
private View $content;

public function preprocessPage()
{
    $product = Product::get(42); // Resolve the authorized record for this request.
    if (!$product) {
        throw new RuntimeException('Product not found.');
    }
    $sourceForm = $product->getForm();
    $this->content = EntityTranslationForm::select($sourceForm, $product);
}

public function echoContent()
{
    return $this->content;
}
```

`select()` processes the selected form; do not also call `$sourceForm->processForm()`. The same URL with `?_translation_locale=en` shows the English translation editor. Language links preserve the other query parameters.

Translation requests use `translations[entityName][objectId][field]`, plus `_translation_locale` and CSRF fields. The server reconstructs permitted targets from the visible source form and rejects unknown IDs, hidden fields and malformed values. Ordinary ORM relation arrays are unchanged.

All submitted translations are validated before writing. The translation form owns one transaction and writes translations directly, without running parent/child `save()` business rules. On failure it rolls back, invalidates affected caches and retains the submitted text.

Custom themes that filter core assets must include `assets/js/forms/translatable_form.js` for unsaved-change warnings. Their normal form template must render the supplied fields. Data type eligibility is controlled by the trait's protected `isTranslatableType()` method. No JSON migration or additional package is required.

## Try nested editing with Product and ProductInfo

A `Product` has a `product_info` collection. Both the product and each `ProductInfo` can translate `title` and rich-text `description`. Their non-default values are stored in `product_translations` and `product_info_translations`, respectively.

After creating the schema files and model classes above, import the schema in your development environment:

```sh
docker compose exec app php bin/console.php config:import
```

1. Open an existing Product's **Edit** page, for example `/admin/entity/insert/products/42`.
2. In the default language, add two **Product info** entries, such as “Materials” and “Care instructions”, and save the product.
3. Select **English** on the same page. The product and each saved ProductInfo have their own translation group.
4. Enter different English titles and descriptions for the two child records and save.
5. Switch back to the default language to verify that the product and child records retain their original values.

The example requires these project-specific product tables and classes; they are not built into an empty CoreDB installation. Ordinary collection arrays remain unchanged: `products[product_info][1][title]` is a default-language form input, while a saved child translation is submitted as `translations[product_info][73][title]`.

Language links show the configured `language_icon` next to each language. Changing language reloads the page: untouched forms navigate immediately, while edited forms ask whether to switch without saving or stay. Text, rich text and nested collection changes are tracked. Dismissing the dialog preserves the form; confirmed switches do not show a second browser prompt. Reloading or closing a dirty page still uses the browser warning. Failed validation leaves the form marked as unsaved. Custom asset pipelines must also include `assets/css/components/translatable_form.css`.
