<?php

/**
 * Created by PhpStorm
 * User: Dmitry Pavlenko
 * e-mail: admin3@triline.kz
 * @ PKF Temir
 */

\Bitrix\Main\Loader::registerAutoLoadClasses('triline.rightscontrolcrm', array(
    'Triline\RightControlCrm\CustomFilter' => '/lib/CustomFilter.php',
    'Triline\RightControlCrm\Repository' => '/lib/Repository.php',
));