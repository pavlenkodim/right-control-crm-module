<?php

/**
 * Created by PhpStorm
 * User: Dmitry Pavlenko
 * e-mail: admin3@triline.kz
 * @ PKF Temir
 */

namespace Triline\RightControlCrm;

use \Bitrix\Main\Loader;

class CustomFilter
{
    public static function getCustomFilter() : array
    {
        global $USER;
        $uFilter = [];
        $authorID = $USER->GetID();
        $authorAr = $USER->GetByID($authorID);
        $authorDeportment = array();

        foreach ($authorAr->arResult as $author)
        {
            $authorDeportment = $author['UF_DEPARTMENT'];
        }

        $arSubordinate = array($authorID);

//        Найти подчиненных
        require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/intranet/classes/general/utils.php');

        if (Loader::includeModule('intranet')) {
            $arUsers = \CIntranetUtils::getSubordinateEmployees($authorID, true);
            while($User = $arUsers->GetNext())
            {
                $arSubordinate[] = $User['ID'];
            }
        }

//        Главное условие выборки
        if (!$USER->IsAdmin())
        {
            if (!in_array(1, $authorDeportment))
            {
                $userFilter = array(
                    'AUTHOR_ID' => $arSubordinate,
                    'RESPONSIBLE_ID' => $authorID,
                );
                $uFilter = $userFilter; // Добавляется пользовательский фильтр
            }
        }
        return $uFilter;
    }
}