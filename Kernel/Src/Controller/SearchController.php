<?php

namespace Src\Controller;

use CoreDB\Kernel\ServiceController;
use Exception;
use Src\Entity\Translation;
use Src\Form\SearchForm;
use Src\Theme\CoreRenderer;

class SearchController extends ServiceController
{
    public function checkAccess(): bool
    {
        return true;
    }

    public function filter()
    {
        $asyncToken = @$_POST["token"];
        $asyncLoadData = @$_SESSION["search_asynch"][$asyncToken];
        if (!$asyncToken || !$asyncLoadData) {
            throw new Exception(Translation::getTranslation("invalid_operation"));
        }
        $formClass = $asyncLoadData["form"];
        $object = unserialize($asyncLoadData["object"]);
        $themeClass = $asyncLoadData["theme"];
        CoreRenderer::getInstance(new $themeClass());
        /** @var SearchForm */
        $form = $formClass::createByObject($object);
        if (!$form->data && !$form->getCache()) {
            return [
                "status" => false,
                "render" => CoreRenderer::getInstance()->renderView(
                    $form->noResultBehaviour()
                )
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
