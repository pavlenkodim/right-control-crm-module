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
use Bitrix\Crm\Timeline\TimelineEntry;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Loader;
use Triline\RightControlCrm\CustomFilter;

if (Loader::includeModule('triline.rightscontrolcrm'))
{

}

class RightsControlCrmTimelineComponent extends CCrmTimelineComponent
{
//    public function getRepository(): \Triline\RightControlCrm\Repository
//    {
//        if (!$this->repository)
//        {
//            if ($this->entityID > 0)
//            {
//                $this->repository = new \Triline\RightControlCrm\Repository(
//                    new Crm\Service\Timeline\Context(
//                        new Crm\ItemIdentifier($this->entityTypeID, $this->entityID, $this->extras['CATEGORY_ID'] ?? 0),
//                        Crm\Service\Timeline\Context::DESKTOP,
//                    )
//                );
//            }
//            else
//            {
//                $this->repository = new Crm\Service\Timeline\Repository\NullRepository();
//            }
//        }
//        echo "<pre>";
//        var_dump($this->repository);
//        return $this->repository;
//    }

    public function prepareHistoryItems($offsetTime = null, $offsetID = 0)
    {
        $filter = $this->historyFilter;

        if (Loader::includeModule('triline.rightscontrolcrm'))
        {
            $uFilter = CustomFilter::getCustomFilter();
            $filter = array_merge($filter, $uFilter);
        }

        $entityFilter = $this->getHistoryFilter();
        $entityFilter->prepareListFilterParams($filter);

        $result = $this->getRepository()->getHistoryItemsPage(
            (new Crm\Service\Timeline\Repository\Query())
                ->setOffsetId((int)$offsetID)
                ->setOffsetTime($offsetTime ? DateTime::createFromUserTime($offsetTime) : null)
                ->setFilter($filter)
                ->setLimit(10)
        );

        $this->arResult['HISTORY_ITEMS'] = $result->getItems();

        $this->arResult['HISTORY_NAVIGATION'] = [
            'OFFSET_TIMESTAMP' => $this->getHistoryTimestamp($result->getOffsetTime()),
            'OFFSET_ID' => $result->getOffsetId(),
        ];
        echo "<pre>";
        print_r($this->arResult);
    }
}