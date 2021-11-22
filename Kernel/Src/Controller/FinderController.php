<?php

namespace Src\Controller;

use CoreDB\Kernel\ServiceController;
use Src\BaseTheme\BaseTheme;
use Src\Form\SearchForm;
use Src\JWT;
use Src\Theme\CoreRenderer;

class FinderController extends ServiceController
{
    
    public function checkAccess(): bool
    {
        return \CoreDB::currentUser()->isLoggedIn();
    }

    public function findData()
    {
        $this->response_type = self::RESPONSE_TYPE_RAW;
        $jwt = JWT::createFromString(@$_GET["className"] ?: "");
        $class = $jwt->getPayload()->class;
        $finderQuery = $class::getInstance();
        $form = SearchForm::createByObject($finderQuery);
        CoreRenderer::getInstance(BaseTheme::getTemplateDirectories());
        return $form;
    }
}
