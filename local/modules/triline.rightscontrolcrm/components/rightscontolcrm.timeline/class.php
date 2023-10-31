<?php

namespace Triline\RightControlCrm\RightsControlCrmTimeline;

class RightsControlCrmTimeline extends \CCrmTimelineComponent
{
    public function getCustomFilterForHistoryItem(): array
    {
        global $USER;
        $authorID = $USER->GetID();
        $authorAr = CUser::GetByID($authorID);
        $authorDeportment = array();

        foreach ($authorAr->arResult as $author)
        {
            $authorDeportment[] = $author['UF_DEPARTMENT'];
        }

        $arSubordinate = array($authorID);
        if(CModule::IncludeModule("intranet"))
        {
            $arUsers = \CIntranetUtils::GetSubordinateEmployees($authorID, true);
            while($User = $arUsers->GetNext())
            {
                $arSubordinate[] = $User['ID'];
            }
        }

        // Выбока комментариев где упомянули пользователя =>
//        $commentIdForUser = array();
//        $arComments = Bitrix\Crm\Timeline\Entity\TimelineTable::getList(array(
//            'order' => array("ID" => "DESC"),
//            'filter' => array()
//        ));
//        while($ar = $arComments->Fetch())
//        {
//            if (strstr($ar['COMMENT'], "[USER=$authorID]"))
//            {
//                echo '<pre>';
//                print_r($ar);
//                $commentIdForUser[] = $ar['ID'];
//            }
//        }
        // <= Выбока комментариев где упомянули пользователя

        if (!$USER->IsAdmin())
        {
            if (!in_array(1, $authorDeportment))
            {
                $userFilter = array(
                    'AUTHOR_ID' => $arSubordinate,
//                    'CREATED' => $arSubordinate,
                    'RESPONSIBLE_ID' => $authorID,
//                    'COMMENT_ID' => $commentIdForUser,
//                    'CLIENT' => $arSubordinate
                );
                $this->historyFilter = array_merge($this->historyFilter, $userFilter); // Добавляется пользовательский фильтр
            }
        }

        return $this->historyFilter;
    }
}