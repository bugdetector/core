<?php

namespace Src\Traits;

use CoreDB\Kernel\Database\DataType\DataTypeAbstract;
use CoreDB\Kernel\Database\DataType\LongText;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Text;
use Src\Entity\EntityTranslation;
use Src\Entity\Translation;

trait TranslatableTrait
{
    protected array $pendingTranslations = [];
    protected ?string $translatableLocale = null;

    public function setTranslatableLocale(string $locale): self
    {
        if (!in_array($locale, Translation::getAvailableLanguageList(), true)) {
            throw new \InvalidArgumentException(Translation::getTranslation('invalid_operation'));
        }
        $this->translatableLocale = $locale;
        return $this;
    }

    public function getTranslatableLocale(): string
    {
        return $this->translatableLocale ?? Translation::getLanguage();
    }

    protected function isTranslatableType(DataTypeAbstract $type): bool
    {
        return $type instanceof ShortText || $type instanceof Text || $type instanceof LongText;
    }

    public function setTranslations(array $data): void
    {
        $normalized = [];
        foreach ($data as $locale => $fields) {
            if (
                $locale === Translation::getDefaultLanguage() ||
                !in_array($locale, Translation::getAvailableLanguageList(), true) ||
                !is_array($fields)
            ) {
                throw new \InvalidArgumentException(Translation::getTranslation("invalid_operation"));
            }
            foreach ($fields as $field => $content) {
                if (
                    !in_array($field, static::getTranslatableFields(), true) ||
                    (!is_string($content) && $content !== null)
                ) {
                    throw new \InvalidArgumentException(Translation::getTranslation("invalid_operation"));
                }
                $type = clone $this->{$field};
                if (!($type instanceof DataTypeAbstract) || !$this->isTranslatableType($type)) {
                    throw new \InvalidArgumentException("Field type does not support translation.");
                }
                $value = $content === null ? null : trim($content);
                // Keep literal zero without changing the framework-wide cleanXSS behavior.
                if ($value !== '0') {
                    $type->setValue($value);
                }
                $normalized[$locale][$field] = $value === '' || $value === null
                    ? null : ($value === '0' ? '0' : (string) $type->getValue());
            }
        }
        $this->pendingTranslations = $normalized;
    }
    public function getTranslatedField(string $field, ?string $locale = null, bool $fallback = true): string
    {
        $locale = $locale ?: $this->getTranslatableLocale();
        $defaultLocale = Translation::getDefaultLanguage();
        $baseValue = (string) $this->{$field}->getValue();
        $fallbackValue = $fallback ? $baseValue : '';

        $identifier = $this->ID->getValue();
        if ($locale === $defaultLocale) {
            return $baseValue;
        }
        if (array_key_exists($field, $this->pendingTranslations[$locale] ?? [])) {
            $value = $this->pendingTranslations[$locale][$field];
            return $value === null || $value === "" ? $fallbackValue : $value;
        }
        if (!$identifier) {
            return $fallbackValue;
        }

        /** @var class-string<EntityTranslation> $transClass */
        $transClass = static::getTranslationClass();
        $val = $transClass::getFromCache($identifier, $field, $locale);

        return $val === null || $val === "" ? $fallbackValue : $val;
    }

    public function setTranslatedField(string $field, ?string $content, ?string $locale = null): void
    {
        $locale = $locale ?: $this->getTranslatableLocale();
        if (!in_array($field, static::getTranslatableFields(), true)) {
            throw new \InvalidArgumentException(Translation::getTranslation('invalid_operation'));
        }
        if ($locale === Translation::getDefaultLanguage()) {
            $this->{$field}->setValue($content);
            return;
        }

        $pending = $this->pendingTranslations;
        $this->setTranslations([$locale => [$field => $content]]);
        $content = $this->pendingTranslations[$locale][$field];
        $normalized = $this->pendingTranslations;
        $this->pendingTranslations = $pending;
        $identifier = $this->ID->getValue();
        if (!$identifier) {
            $this->pendingTranslations = array_replace_recursive($pending, $normalized);
            return;
        }
        /** @var class-string<EntityTranslation> $transClass */
        $transClass = static::getTranslationClass();
        $trans = $transClass::get([
            "object_id" => $identifier,
            "locale" => $locale,
            "field" => $field,
        ]) ?: new $transClass();

        $trans->object_id->setValue($identifier);
        $trans->locale->setValue($locale);
        $trans->field->setValue($field);
        $trans->content->setValue($content);
        if (!$trans->save()) {
            throw new \RuntimeException(Translation::getTranslation('invalid_operation'));
        }
        $transClass::setToCache($identifier, $field, $locale, $content);
    }

    public function saveTranslations(?array $data = null): void
    {
        if ($data !== null) {
            $this->setTranslations($data);
        }
        if (!$this->ID->getValue()) {
            return;
        }
        foreach ($this->pendingTranslations as $locale => $fields) {
            foreach ($fields as $field => $content) {
                $this->setTranslatedField($field, $content, $locale);
            }
        }
        $this->pendingTranslations = [];
    }

    public function deleteTranslations(): void
    {
        $identifier = $this->ID->getValue();
        /** @var class-string<EntityTranslation> $transClass */
        $transClass = static::getTranslationClass();
        foreach ($transClass::getAll(["object_id" => $identifier]) as $trans) {
            if (!$trans->delete()) {
                throw new \RuntimeException(Translation::getTranslation('invalid_operation'));
            }
        }
        $transClass::clearCache($identifier);
    }
}
