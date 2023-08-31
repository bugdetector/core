<?php

namespace Src\Views;

use Src\Theme\View;

class NoResult extends View
{
    public string $message;
    public function __construct(string $message)
    {
        $this->message = $message;
    }

    public function getTemplateFile(): string
    {
        return "no-result.twig";
    }
}
