<?php
namespace Src\Controller;

use Src\Entity\Watchdog;
use Src\Theme\BaseTheme\BaseTheme;

class MainpageController extends BaseTheme{

    public function checkAccess() : bool {
        return TRUE;
    }

    public function preprocessPage() {}
    public function echoContent() {
    }
}