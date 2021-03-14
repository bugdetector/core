<?php

namespace Src\Controller;

use CoreDB\Kernel\Messenger;
use CoreDB\Kernel\ServiceController;
use CoreDB\Kernel\TableMapper;
use Exception;
use Src\BaseTheme\BaseTheme;
use Src\Entity\File;
use Src\Entity\Translation;
use Src\Form\Widget\CollapsableWidgetGroup;
use Src\Form\Widget\InputWidget;
use Src\JWT;

class AjaxController extends ServiceController
{

    public function autocompleteFilter()
    {
        $autocompleteToken = @$_POST["token"];
        $data = @$_POST["data"];
        if ($autocompleteToken && isset($_SESSION["autocomplete"][$autocompleteToken])) {
            $autocompleteData = $_SESSION["autocomplete"][$autocompleteToken];
            $referenceTable = $autocompleteData["referenceTable"];
            $referenceColumn = $autocompleteData["referenceColumn"];
            
            $data = "%{$data}%";
            $query = \CoreDB::database()->select($referenceTable)
                ->select($referenceTable, ["ID", $referenceColumn])
                ->condition($referenceColumn, $data, "LIKE")
                ->limit(20);
            return $query->execute()->fetchAll(\PDO::FETCH_KEY_PAIR);
        }
    }

    public function getEntityCard()
    {
        $entityName = @$_POST["entity"];
        $index = @$_POST["index"] + 1;
        $name = @$_POST["name"];
        $hiddenFields = @$_POST["hiddenFields"];
        $referenceClass = \CoreDB::config()->getEntityInfo($entityName)["class"];
        $object = new $referenceClass();

        $this->response_type = self::RESPONSE_TYPE_RAW;
        echo CollapsableWidgetGroup::getObjectCard(
            $object,
            $name,
            $index,
            $hiddenFields
        )->setOpened(true);
    }

    public function entityDelete()
    {
        $key = @$_POST["key"];
        try {
            $jwt = JWT::createFromString($key);
            $data = $jwt->getPayload();
            $referenceClass = \CoreDB::config()->getEntityInfo($data->entity)["class"];
            /** @var TableMapper */
            $object = $referenceClass::get($data->id);
            if (@$data->field) {
                $object->unsetField($data->field);
            } else {
                $object->delete();
            }
            $this->createMessage(
                Translation::getTranslation("record_removed"),
                Messenger::SUCCESS
            );
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }

    public function saveFile()
    {
        $key = @$_POST["key"];
        try {
            $jwt = JWT::createFromString($key);
            $data = $jwt->getPayload();
            $referenceClass = \CoreDB::config()->getEntityInfo($data->entity)["class"];
            /** @var TableMapper */
            $object = $referenceClass::get($data->id) ?: new $referenceClass();
            if (@$data->field) {
                $file = new File();
                $file->storeUploadedFile($object->getTableName(), $data->field, $_FILES["file"]);
                $file->status->setValue(File::STATUS_TEMPORARY);
                $file->save();
                $fileWidget = InputWidget::create(@$_POST["name"]);
                $fileWidget->addFileKey($object->entityName, $object->ID->getValue(), $data->field);
                $fileWidget->setLabel(@$_POST["label"]);
                $fileWidget->setType("file");
                $fileWidget->setValue($file->ID->getValue());
                $this->response_type = self::RESPONSE_TYPE_RAW;
                echo \Src\Theme\CoreRenderer::getInstance(
                    BaseTheme::getTemplateDirectories()
                )->renderWidget($fileWidget);
            }
        } catch (Exception $ex) {
            throw new Exception($ex->getMessage());
        }
    }
}
