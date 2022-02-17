<?php

namespace Src\Views;

use Src\Theme\View;

class BasicCard extends View
{
    public $background_class;
    public $href;
    public $title;
    public $description;
    public $icon_class;

    public static function create(): BasicCard
    {
        return new BasicCard();
    }

    public function setBackgroundClass(string $background_class): BasicCard
    {
        $this->background_class = $background_class;
        return $this;
    }
    public function setHref(string $href): BasicCard
    {
        $this->href = $href;
        return $this;
    }
    public function setTitle(string $title): BasicCard
    {
        $this->title = $title;
        return $this;
    }
    public function setDescription($description): BasicCard
    {
        $this->description = $description;
        return $this;
    }
    public function setIconClass(string $class_name): BasicCard
    {
        $this->icon_class = $class_name;
        return $this;
    }

    public function getTemplateFile(): string
    {
        return "basic_card.twig";
    }
}
