<?php

namespace Src\Controller;

use CoreDB\Kernel\ServiceController;
use Exception;
use Src\Entity\Translation;
use Src\Theme\CoreRenderer;

class SearchController extends ServiceController
{
    public function checkAccess(): bool
    {
        return true;
    }

    public function getNextPage()
    {
        $autoLoadToken = @$_POST["token"];
        $autoLoadData = @$_SESSION["autoload"][$autoLoadToken];
        if (!$autoLoadToken || !$autoLoadData) {
            throw new Exception(Translation::getTranslation("invalid_operation"));
        }
        $formClass = $autoLoadData["form"];
        $object = unserialize($autoLoadData["object"]);
        $themeClass = $autoLoadData["theme"];
        CoreRenderer::getInstance(new $themeClass());
        /** @var SearchForm */
        $form = $formClass::createByObject($object);
        if (!$form->data && !$form->getCache()) {
            return [
                "status" => false
            ];
        } else {
            ob_start();
            $form->render();
            $render = ob_get_contents();
            ob_end_clean();
            return [
                "status" => true,
                "render" => $render
            ];
        }
    }
}
