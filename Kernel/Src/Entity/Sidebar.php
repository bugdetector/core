<?php

namespace Src\Entity;

use CoreDB;
use CoreDB\Kernel\Database\DataType\EnumaratedList;
use CoreDB\Kernel\Database\DataType\ShortText;
use CoreDB\Kernel\Database\DataType\Integer;
use CoreDB\Kernel\Database\DataType\TableReference;
use CoreDB\Kernel\EntityReference;
use PDO;
use Src\Controller\Admin\AjaxController;
use Src\Form\Widget\SelectWidget;
use Src\Theme\View;

/**
 * Object relation with table sidebar
 * @author murat
 */

class Sidebar extends TreeEntityAbstract
{
    /**
    * Available for logged in and non logged in users.
    */
    public const AVAILABLE_FOR_BOTH = "both";
    /**
    * Available for only logged in users.
    */
    public const AVAILABLE_FOR_LOGGED_IN = "logged_in";
    /**
    *  Available for only non logged in users.
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

    public EntityReference $roles;
    
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

    /**
     * @inheritdoc
     */
    public static function getTableName(): string
    {
        return "sidebar";
    }

    public static function getTreeFieldName(): string
    {
        return "title";
    }

    public function getRemoveServiceUrl(): string
    {
        return AjaxController::getUrl() . "removeSidebarItem";
    }

    protected function getFieldWidget(string $field_name, bool $translateLabel): ?View
    {
        if (in_array($field_name, ["weight", "parent"])) {
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

    public static function getSidebarElements($parent = null)
    {
        $query = CoreDB::database()->select(static::getTableName(), "s")
        ->leftjoin("sidebar_roles", "sr", "sr.sidebar_id = s.ID")
        ->select("s", ["ID"])
        ->groupBy("s.ID");
        if ($parent) {
            $query->condition("s.parent", $parent);
        }
        $currentUser = CoreDB::currentUser();
        if ($currentUser->isLoggedIn()) {
            $availableCondition = CoreDB::database()->condition($query)
            ->condition("s.available_for", [
                self::AVAILABLE_FOR_BOTH,
                self::AVAILABLE_FOR_LOGGED_IN
            ], "IN");
            if ($currentUser->roles->getValue()) {
                $roleCondition = CoreDB::database()->condition($query)
                ->condition("sr.role_id", $currentUser->roles->getValue(), "IN")
                ->condition("sr.role_id", null, "IS", "OR");
            } else {
                $roleCondition = CoreDB::database()->condition($query)
                ->condition("sr.role_id", null);
            }
        } else {
            $availableCondition = CoreDB::database()->condition($query)
            ->condition("s.available_for", [
                self::AVAILABLE_FOR_BOTH,
                self::AVAILABLE_FOR_NON_LOGGED_IN
            ], "IN");
            $roleCondition = CoreDB::database()->condition($query)
            ->condition("sr.role_id", null);
        }
        $query->condition($roleCondition);
        $query->condition($availableCondition);
        $ids = $query->execute()->fetchAll(PDO::FETCH_COLUMN);
        return static::findAll([
            "ID" => $ids ?: null
        ], static::getTableName(), "weight");
    }
}
