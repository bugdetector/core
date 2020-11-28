<?php

namespace Src\Form\Widget;

use Src\Entity\Translation;
use Src\JWT;

class SelectWidget extends FormWidget
{
    public $options = [];
    public ?OptionWidget $null_element;

    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->addClass("selectpicker");
        $this->addAttribute("data-container", "container");
        $this->null_element = new OptionWidget("", Translation::getTranslation("none"));
        \CoreDB::controller()->addJsFiles("dist/select/select.js");
        \CoreDB::controller()->addCssFiles("dist/select/select.css");
    }

    public static function create(string $name): SelectWidget
    {
        return new SelectWidget($name);
    }

    public function getTemplateFile(): string
    {
        return "select_widget.twig";
    }

    public function setOptions(array $options): SelectWidget
    {
        $this->options = [];
        foreach ($options as $key => $option) {
            if (!($option instanceof OptionWidget)) {
                $this->options[$key] = new OptionWidget($key, $option);
            } else {
                $this->options[$option->value] = $option;
            }
        }
        return $this;
    }

    public function setNullElement(string $null_element = null): SelectWidget
    {
        if ($null_element) {
            $this->null_element = new OptionWidget("", $null_element);
        } else {
            $this->null_element = null;
        }
        return $this;
    }

    public function render()
    {
        if (
            isset($this->value)
            && isset($this->options[$this->value])
            && $this->options[$this->value] instanceof OptionWidget
        ) {
            $this->options[$this->value]->setSelected(true);
        }
        parent::render();
    }

    public function setValue($value): SelectWidget
    {
        if (is_array($value)) {
            foreach ($value as $key) {
                if (isset($this->options[$key])) {
                    $this->options[$key]->setSelected(true);
                }
            }
        } else {
            $this->value = $value;
        }
        return $this;
    }

    public function setAutoComplete($referenceTable, $referenceColumn): SelectWidget
    {
        $this->addClass("autocomplete");
        $autoCompleteJWT = new JWT();
        $autoCompleteJWT->setPayload([
            "autocomplete" => "autocomplete-" . $referenceTable . random_int(0, 100)
        ]);
        $autoCompleteToken = $autoCompleteJWT->createToken();
        $_SESSION["autocomplete"][$autoCompleteToken] = [
            "referenceTable" => $referenceTable,
            "referenceColumn" => $referenceColumn
        ];
        $this->addAttribute("data-live-search", "true");
        $this->addAttribute("data-autocomplete-token", $autoCompleteToken);
        return $this;
    }

    public function createIfNotExist(bool $create_if_not_exist): SelectWidget
    {
        if ($create_if_not_exist) {
            $this->addClass("create-if-not-exist");
        } else {
            $this->removeClass("create-if-not-exist");
        }
        return $this;
    }
}
