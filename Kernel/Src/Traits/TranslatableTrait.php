<?php

namespace Src\Traits;

use Src\Entity\EntityTranslation;
use Src\Entity\Translation;

trait TranslatableTrait
{
    public function getTranslatedField(string $field, ?string $locale = null, bool $fallback = true): string
    {
        $locale = $locale ?: Translation::getLanguage();
        $defaultLocale = Translation::getDefaultLanguage();
        $baseValue = (string) $this->{$field}->getValue();
        $fallbackValue = $fallback ? $baseValue : '';

        $identifier = $this->ID->getValue();
        if ($locale === $defaultLocale || !$identifier) {
            return $fallbackValue;
        }

        /** @var class-string<EntityTranslation> $transClass */
        $transClass = static::getTranslationClass();
        $val = $transClass::getFromCache($identifier, $field, $locale);

        return $val ?: $fallbackValue;
    }

    public function setTranslatedField(string $field, ?string $content, ?string $locale = null): void
    {
        $locale = $locale ?: Translation::getLanguage();
        if ($locale === Translation::getDefaultLanguage()) {
            $this->{$field}->setValue($content);
            return;
        }

        $identifier = $this->ID->getValue();
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
        $trans->save();
        $transClass::setToCache($identifier, $field, $locale, $content);
    }

    public function saveTranslations(?array $data = null): void
    {
        $translations = $data ?? ($_POST['translations'] ?? null);
        if (!is_array($translations)) {
            return;
        }

        $translatableFields = static::getTranslatableFields();
        foreach ($translations as $locale => $fields) {
            if (!is_array($fields)) {
                continue;
            }
            foreach ($fields as $field => $content) {
                if (in_array($field, $translatableFields, true)) {
                    $contentStr = trim((string) $content);
                    $this->setTranslatedField($field, $contentStr ?: null, $locale);
                }
            }
        }
    }

    public function deleteTranslations(): void
    {
        $identifier = $this->ID->getValue();
        /** @var class-string<EntityTranslation> $transClass */
        $transClass = static::getTranslationClass();
        foreach ($transClass::getAll(["object_id" => $identifier]) as $trans) {
            $trans->delete();
        }
        $transClass::clearCache($identifier);
    }
}
