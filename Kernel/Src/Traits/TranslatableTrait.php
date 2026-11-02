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

        $objectId = $this->ID->getValue();
        if ($locale === $defaultLocale) {
            return $fallbackValue;
        }

        /** @var class-string<EntityTranslation> $transClass */
        $transClass = static::getTranslationClass();

        if ($transClass::hasInCache($objectId, $field, $locale)) {
            $cached = $transClass::getFromCache($objectId, $field, $locale);
            return $cached ?: $fallbackValue;
        }

        $trans = $transClass::get([
            "object_id" => $objectId,
            "locale" => $locale,
            "field" => $field,
        ]);
        $val = $trans ? $trans->content->getValue() : null;
        $transClass::setToCache($objectId, $field, $locale, $val);

        return $val ?: $fallbackValue;
    }

    public function setTranslatedField(string $field, ?string $content, ?string $locale = null): void
    {
        $locale = $locale ?: Translation::getLanguage();
        if ($locale === Translation::getDefaultLanguage()) {
            $this->{$field}->setValue($content);
            return;
        }

        $objectId = $this->ID->getValue();
        /** @var class-string<EntityTranslation> $transClass */
        $transClass = static::getTranslationClass();
        $trans = $transClass::get([
            "object_id" => $objectId,
            "locale" => $locale,
            "field" => $field,
        ]) ?: new $transClass();

        $trans->object_id->setValue($objectId);
        $trans->locale->setValue($locale);
        $trans->field->setValue($field);
        $trans->content->setValue($content);
        $trans->save();
        $transClass::setToCache($objectId, $field, $locale, $content);
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
        $objectId = $this->ID->getValue();
        /** @var class-string<EntityTranslation> $transClass */
        $transClass = static::getTranslationClass();
        foreach ($transClass::getAll(["object_id" => $objectId]) as $trans) {
            $trans->delete();
        }
        $transClass::clearCache($objectId);
    }
}
