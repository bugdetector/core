<?php

namespace CoreDB\Kernel;

use Src\Entity\EntityTranslation;

interface TranslatableInterface
{
    /**
     * List of translatable field names (e.g. ['title', 'description'])
     */
    public static function getTranslatableFields(): array;

    /**
     * Corresponding translation model class name (e.g. PageTranslation::class)
     * @return class-string<EntityTranslation>
     */
    public static function getTranslationClass(): string;

    public function setTranslatableLocale(string $locale): self;

    public function getTranslatableLocale(): string;

    public function getTranslatedField(string $field, ?string $locale = null, bool $fallback = true): string;

    public function setTranslatedField(string $field, ?string $content, ?string $locale = null): void;

    public function setTranslations(array $data): void;

    public function saveTranslations(?array $data = null): void;

    public function deleteTranslations(): void;
}
