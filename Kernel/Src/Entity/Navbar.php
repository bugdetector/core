<?php

namespace Src\Entity;

use CoreDB;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\DataType\TableReference;
use PDO;
use Src\Controller\Admin\AjaxController;
use Src\Form\Widget\SelectWidget;
use Src\Theme\View;

/**
 * Object relation with table navbar
 * @author murat
 */

class Navbar extends TreeEntityAbstract
{
    /**
    * AVAILABLE_FOR_BOTH description.
    */
    public const AVAILABLE_FOR_BOTH = "both";
    /**
    * AVAILABLE_FOR_LOGGED_IN description.
    */
    public const AVAILABLE_FOR_LOGGED_IN = "logged_in";
    /**
    * AVAILABLE_FOR_NON_LOGGED_IN description.
    */
    public const AVAILABLE_FOR_NON_LOGGED_IN = "non_logged_in";

    /**
    * @var ShortText $title
    * Menu item title. Will be translated.
    */
    public ShortText $title;
    /**
    * @var ShortText $icon_class
    * Icon class of item.
    */
    public ShortText $icon_class;
    /**
    * @var ShortText $link_class
    * Classes for link item.
    */
    public ShortText $link_class;
    /**
    * @var ShortText $url
    * Url that redirected when click.
    */
    public ShortText $url;
    /**
    * @var EnumaratedList $available_for
    * Criteria for login status users.
    */
    public EnumaratedList $available_for;
    /**
    * @var Integer $weight
    * Order weight.
    */
    public Integer $weight;
    /**
    * @var TableReference $parent
    *
    */
    public TableReference $parent;


    public static function getTreeFieldName(): string
    {
        return "title";
    }

    public function getRemoveServiceUrl(): string
    {
        return AjaxController::getUrl() . "removeNavbarItem";
    }

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "navbar";
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        if (in_array($field_name, ["weight"])) {
            return null;
        } elseif ($field_name == "roles") {
            /** @var SelectWidget */
            $widget = parent::getFieldWidget($field_name, $translateLabel);
            $widget->setDescription(
                Translation::getTranslation("sidebar_roles_description")
            );
            return $widget;
        } else {
            return parent::getFieldWidget($field_name, $translateLabel);
        }
    }

    public static function getNavbarElements($parent = null)
    {
            $query = CoreDB::database()->select(static::getTableName(), "n")
            ->leftjoin("navbar_roles", "nr", "nr.navbar_id = n.ID")
            ->select("n", ["ID"])
            ->groupBy("n.ID");
            $query->condition("n.parent", $parent);
            $currentUser = CoreDB::currentUser();
        if ($currentUser->isLoggedIn()) {
            $availableCondition = CoreDB::database()->condition($query)
            ->condition("n.available_for", [
                self::AVAILABLE_FOR_BOTH,
                self::AVAILABLE_FOR_LOGGED_IN
            ], "IN");
            if ($currentUser->roles->getValue()) {
                $roleCondition = CoreDB::database()->condition($query)
                ->condition("nr.role_id", $currentUser->roles->getValue(), "IN")
                ->condition("nr.role_id", null, "IS", "OR");
            } else {
                $roleCondition = CoreDB::database()->condition($query)
                ->condition("nr.role_id", null);
            }
        } else {
            $availableCondition = CoreDB::database()->condition($query)
            ->condition("n.available_for", [
                self::AVAILABLE_FOR_BOTH,
                self::AVAILABLE_FOR_NON_LOGGED_IN
            ], "IN");
            $roleCondition = CoreDB::database()->condition($query)
            ->condition("nr.role_id", null);
        }
            $query->condition($roleCondition);
            $query->condition($availableCondition);
            $ids = $query->execute()->fetchAll(PDO::FETCH_COLUMN);
        if ($ids) {
            $elements = CoreDB::database()->select(static::getTableName())
            ->condition("ID", $ids)
            ->orderBy("weight")
            ->execute()->fetchAll(PDO::FETCH_OBJ);
        } else {
            $elements = [];
        }
            return $elements;
    }
}
