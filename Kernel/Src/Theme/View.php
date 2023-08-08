<?php

namespace Src\Theme;

use Src\Entity\Cache;
use Src\Entity\Translation;
use Src\Theme\CoreRenderer;

abstract class View
{
    public $classes = [];
    public $attributes = [];
    public bool $cachable = false;

    abstract public function getTemplateFile(): string;

    public function render()
    {
        if ($this->cachable) {
            $cache = $this->getCache();
            if (!$cache) {
                $render = CoreRenderer::getInstance()
                        ->renderView($this);
                Cache::set("view_render", $this->getCacheKey(), $render);
                echo $render;
            } else {
                echo $cache->value;
            }
        } else {
            echo CoreRenderer::getInstance()
                ->renderView($this);
        }
    }

    public function addClass(string $class_name): View
    {
        if (!$this->hasClass($class_name)) {
            $this->classes = array_merge($this->classes, explode(" ", $class_name));
        }
        return $this;
    }

    public function hasClass(string $class_name): bool
    {
        return in_array($class_name, $this->classes);
    }

    public function removeClass(string $class_name)
    {
        $class_index = array_search($class_name, $this->classes);
        if ($class_index !== false) {
            unset($this->classes[$class_index]);
        }
        return $this;
    }

    public function addAttribute(string $attribute_name, string $attribute_value)
    {
        $this->attributes[$attribute_name] = $attribute_value;
        return $this;
    }

    public function removeAttribute(string $attribute_name)
    {
        unset($this->attributes[$attribute_name]);
        return $this;
    }

    public function renderAttributes()
    {
        $render = "";
        foreach ($this->attributes as $name => $value) {
            $render .= " $name='$value' ";
        }
        return $render;
    }

    public function getCache(): ?Cache
    {
        return Cache::getByBundleAndKey("view_render", $this->getCacheKey());
    }

    protected function getCacheKey(): string
    {
        return Translation::getLanguage() . static::class;
    }

    public function setCachable(bool $cachable)
    {
        $this->cachable = $cachable;
        return $this;
    }

    public function __toString()
    {
        return $this->render() ? : "";
    }
}
