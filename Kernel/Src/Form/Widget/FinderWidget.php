<?php

namespace Src\Form\Widget;

use CoreDB\Kernel\SearchableInterface;
use Src\Form\Widget\FormWidget;
use Src\JWT;

class FinderWidget extends FormWidget
{

    /**
     * @property string $finderClass
     *  This class must be implements \CoreDB\Kernel\FilterableInterface
     *  and must produce a column has html class 'finder-select'
     */
    private string $finderClass;
    /**
     * @param string $name
     *  Name of input
     */
    public function __construct(string $name)
    {
        parent::__construct($name);
        $this->addClass("finder-input");
        \CoreDB::controller()->addJsFiles("assets/js/components/finder_widget.js");
    }

    public static function create(string $name): FinderWidget
    {
        return new FinderWidget($name);
    }

    public function setFinderClass(string $finderClass): FinderWidget
    {
        $this->finderClass = $finderClass;
        return $this;
    }

    public function getFinderClass(): string
    {
        return $this->finderClass;
    }

    public function getFinderClassEncoded(): string
    {
        $jwt = new JWT();
        $jwt->setPayload([
            "class" => $this->finderClass
        ]);
        return $jwt->createToken();
    }

    public function getTemplateFile(): string
    {
        return "finder-widget.twig";
    }

    public function getText(): ?string
    {
        if ($this->finderClass && $this->value) {
            /** @var SearchableInterface */
            $object = ($this->finderClass)::getInstance();
            $query = $object->getResultQuery();
            $query->having("ID = :finder_value")
            ->params([":finder_value" => $this->value]);
            $result = $query->execute()->fetch(\PDO::FETCH_NUM);
            return $result[1] . " - " . $result[2];
        }
        return null;
    }
}
