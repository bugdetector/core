<?php

namespace Src\Form;

use CoreDB\Kernel\Model;
use CoreDB\Kernel\TranslatableInterface;
use Src\Entity\Translation;
use Src\Form\Widget\CollapsableWidgetGroup;
use Src\Form\Widget\InputWidget;
use Src\Form\Widget\TextareaWidget;
use Src\Theme\View;
use Src\Views\CollapsableCard;
use Src\Views\Link;
use Src\Views\TextElement;
use Src\Views\ViewGroup;

class EntityTranslationForm extends Form
{
    public string $method = 'POST';
    private array $targets = [];
    private array $submitted = [];

    public function __construct(Form $source, Model $object, private string $locale)
    {
        parent::__construct();
        $this->collect($source, $object);
        $this->addField(InputWidget::create('_translation_locale')->setType('hidden')->setValue($locale));
        foreach ($this->targets as $rows) {
            foreach ($rows as $target) {
                $group = ViewGroup::create('fieldset', 'bg-light border border-gray-200 rounded p-4 mb-4');
                $group->addField(
                    ViewGroup::create('legend', 'float-none w-auto px-2 mb-3 fs-6 fw-semibold text-gray-800')
                    ->addField(TextElement::create(Translation::getTranslation($target['object']->entityName)))
                    ->addField(TextElement::create('#' . $target['object']->ID->getValue())
                        ->addClass('ms-2 fs-7 fw-normal text-gray-700'))
                );
                foreach ($target['fields'] as $field) {
                    $group->addField(ViewGroup::create('div', 'mb-3')->addField($field));
                }
                $this->addField($group);
            }
        }
        $this->addField(InputWidget::create('save')->setType('submit')
            ->setValue(Translation::getTranslation('save'))->removeClass('form-control')->addClass('btn btn-primary'));
    }

    public static function selectedLocale(): string
    {
        $locale = $_GET['_translation_locale'] ?? Translation::getDefaultLanguage();
        if (!is_string($locale) || !in_array($locale, Translation::getAvailableLanguageList(), true)) {
            http_response_code(400);
            throw new \InvalidArgumentException('Invalid translation locale.');
        }
        return $locale;
    }

    public static function select(Form $source, Model $object): View
    {
        $locale = self::selectedLocale();
        if (!$object->ID->getValue()) {
            if ($locale !== Translation::getDefaultLanguage()) {
                http_response_code(400);
                throw new \InvalidArgumentException('Save the record in the default language first.');
            }
            $source->processForm();
            return $source;
        }
        $editor = new self($source, $object, $locale);
        if (!$editor->targets) {
            if ($locale !== Translation::getDefaultLanguage()) {
                http_response_code(400);
                throw new \InvalidArgumentException('Save a translatable record in the default language first.');
            }
            $source->processForm();
            return $source;
        }
        \CoreDB::controller()->addJsFiles('assets/js/forms/translatable_form.js');
        \CoreDB::controller()->addCssFiles('assets/css/components/translatable_form.css');
        foreach (
            ['translation_unsaved_title', 'translation_unsaved_message',
            'translation_discard_switch', 'translation_stay'] as $key
        ) {
            \CoreDB::controller()->addFrontendTranslation($key);
        }
        $view = ViewGroup::create('div', 'entity-translation-editor');
        $toolbar = ViewGroup::create('div', 'bg-light border border-dashed border-gray-300 rounded p-4 mb-4');
        $toolbar->addField(TextElement::create(Translation::getTranslation('translation_language'))
            ->setTagName('div')->addClass('fw-semibold text-gray-800 mb-1'));
        $toolbar->addField(TextElement::create(Translation::getTranslation('translation_language_hint'))
            ->setTagName('div')->addClass('text-gray-700 fs-7 mb-3'));
        $links = ViewGroup::create('nav', 'trans-lang-nav d-flex flex-wrap gap-2')
            ->addAttribute('aria-label', Translation::getTranslation('translation_language'));
        $icons = Translation::getTranslationIcons();
        foreach (Translation::getAvailableLanguageList() as $language) {
            $query = $_GET;
            $query['_translation_locale'] = $language;
            $label = ViewGroup::create('span', 'd-inline-flex align-items-center gap-2');
            if (!empty($icons[$language])) {
                $label->addField(ViewGroup::create('span', 'trans-lang-icon d-inline-flex align-items-center')
                    ->addAttribute('aria-hidden', 'true')
                    ->addField(TextElement::create($icons[$language])->setIsRaw(true)));
            }
            $label->addField(TextElement::create(Translation::getTranslation($language)));
            $link = Link::create('?' . http_build_query($query), $label)
                ->addClass('trans-lang-link btn btn-sm ' . ($language === $locale
                    ? 'btn-primary active' : 'btn-light-secondary text-gray-700'));
            if ($language === $locale) {
                $link->addAttribute('aria-current', 'page');
            }
            $links->addField($link);
        }
        $view->addField($toolbar->addField($links));
        $selected = $locale === Translation::getDefaultLanguage() ? $source : $editor;
        $selected->addAttribute('data-translation-editor', 'true');
        $selected->processForm();
        if ($selected->errors) {
            $selected->addAttribute('data-translation-dirty', 'true');
        }
        return $view->addField($selected);
    }

    private function collect($view, Model $object): void
    {
        if ($view instanceof CollapsableWidgetGroup) {
            foreach ($view->translationObjects as [$child, $card]) {
                if (in_array($card, $view->fieldGroup->fields, true)) {
                    $this->collect($card->content, $child);
                }
            }
            return;
        }
        if ($view instanceof InputWidget && $view->type === 'hidden') {
            return;
        }
        if (
            $object instanceof TranslatableInterface && $object->ID->getValue()
            && ($view instanceof InputWidget || $view instanceof TextareaWidget)
            && preg_match('/\[([^\[\]]+)\]$/', (string) $view->name, $match)
            && in_array($match[1], $object::getTranslatableFields(), true)
        ) {
            $field = $match[1];
            $entity = $object->entityName ?: $object::getTableName();
            $id = $object->ID->getValue();
            $copy = clone $view;
            $object->setTranslatableLocale($this->locale);
            $copy->setName("translations[{$entity}][{$id}][{$field}]")
                ->setValue($object->getTranslatedField($field, $this->locale, false))
                ->removeAttribute('id')->removeAttribute('required')
                ->addAttribute('dir', Translation::getDirection($this->locale));
            $this->targets[$entity][$id]['object'] = $object;
            $this->targets[$entity][$id]['fields'][$field] = $copy;
        }
        if ($view instanceof CollapsableCard) {
            $this->collect($view->content, $object);
        }
        foreach ($view->fields ?? [] as $child) {
            $this->collect($child, $object);
        }
    }

    public function getFormId(): string
    {
        return 'entity_translation_form';
    }

    protected function restoreValues()
    {
        // Nested values are restored during validation without touching the source objects.
    }

    public function validate(): bool
    {
        $this->submitted = [];
        try {
            if (($this->request['_translation_locale'] ?? null) !== $this->locale) {
                throw new \InvalidArgumentException('Translation locale changed.');
            }
            $data = $this->request['translations'] ?? [];
            if (!is_array($data)) {
                throw new \InvalidArgumentException('Invalid translation data.');
            }
            foreach ($data as $entity => $rows) {
                if (!is_array($rows)) {
                    throw new \InvalidArgumentException('Invalid translation rows.');
                }
                foreach ($rows as $id => $values) {
                    $target = $this->targets[$entity][$id] ?? null;
                    if (!$target || !is_array($values) || array_diff_key($values, $target['fields'])) {
                        throw new \InvalidArgumentException('Invalid translation target.');
                    }
                    foreach ($values as $field => $value) {
                        if (is_string($value) || $value === null) {
                            $target['fields'][$field]->setValue($value);
                        }
                    }
                    $target['object']->setTranslations([$this->locale => $values]);
                    $this->submitted[] = $target['object'];
                }
            }
        } catch (\InvalidArgumentException $error) {
            $this->setError('', $error->getMessage());
            return false;
        }
        return true;
    }

    public function submit()
    {
        $db = \CoreDB::database();
        $db->beginTransaction();
        try {
            foreach ($this->submitted as $object) {
                $object->saveTranslations();
            }
            $db->commit();
        } catch (\Throwable $error) {
            $db->rollback();
            foreach ($this->submitted as $object) {
                $class = $object::getTranslationClass();
                $class::clearCache($object->ID->getValue());
            }
            $this->setError('', $error->getMessage());
            return;
        }
        $this->setMessage(Translation::getTranslation('update_success'));
        $this->submitSuccess();
    }

    protected function submitSuccess(): void
    {
        \CoreDB::goTo(BASE_URL . \CoreDB::requestUrl() . '?' . http_build_query($_GET));
    }
}
