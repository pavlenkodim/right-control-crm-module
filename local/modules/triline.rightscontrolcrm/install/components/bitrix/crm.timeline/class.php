<?php

/**
 * Created by PhpStorm
 * User: Dmitry Pavlenko
 * e-mail: admin3@triline.kz
 * @ PKF Temir
 */

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)die();

require_once('../bitrix/components/bitrix/crm.timeline/class.php');

use Bitrix\Crm;
use Bitrix\Main\Loader;
use Bitrix\Crm\Timeline\TimelineEntry;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Triline\RightControlCrm\CustomFilter;

class RightsControlCrmTimelineComponent extends CCrmTimelineComponent
{
    public function prepareHistoryItems($offsetTime = null, $offsetID = 0)
    {
        if (Loader::includeModule('triline.rightscontrolcrm'))
        {

            CustomFilter::getCustomFilter();
        }
        else
        {
            echo "Module not included!";
        }

        parent::prepareHistoryItems($offsetTime = null, $offsetID = 0);

        echo "<br>"."Hello!";
    }

//    public function getCustomFilterForHistoryItem(): array
//    {
//        global $USER;
//        $authorID = $USER->GetID();
//        $authorAr = CUser::GetByID($authorID);
//        $authorDeportment = array();
//
//        foreach ($authorAr->arResult as $author)
//        {
//            $authorDeportment[] = $author['UF_DEPARTMENT'];
//        }
//
//        $arSubordinate = array($authorID);
//        if(CModule::IncludeModule("intranet"))
//        {
//            $arUsers = \CIntranetUtils::GetSubordinateEmployees($authorID, true);
//            while($User = $arUsers->GetNext())
//            {
//                $arSubordinate[] = $User['ID'];
//            }
//        }
//
//        // Выбока комментариев где упомянули пользователя =>
////        $commentIdForUser = array();
////        $arComments = Bitrix\Crm\Timeline\Entity\TimelineTable::getList(array(
////            'order' => array("ID" => "DESC"),
////            'filter' => array()
////        ));
////        while($ar = $arComments->Fetch())
////        {
////            if (strstr($ar['COMMENT'], "[USER=$authorID]"))
////            {
////                echo '<pre>';
////                print_r($ar);
////                $commentIdForUser[] = $ar['ID'];
////            }
////        }
//        // <= Выбока комментариев где упомянули пользователя
//
//        if (!$USER->IsAdmin())
//        {
//            if (!in_array(1, $authorDeportment))
//            {
//                $userFilter = array(
//                    'AUTHOR_ID' => $arSubordinate,
////                    'CREATED' => $arSubordinate,
//                    'RESPONSIBLE_ID' => $authorID,
////                    'COMMENT_ID' => $commentIdForUser,
////                    'CLIENT' => $arSubordinate
//                );
//                $this->historyFilter = array_merge($this->historyFilter, $userFilter); // Добавляется пользовательский фильтр
//            }
//        }
//
//        return $this->historyFilter;
//    }
}