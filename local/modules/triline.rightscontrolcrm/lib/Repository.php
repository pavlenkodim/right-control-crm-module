<?php

/**
 * Created by PhpStorm
 * User: Dmitry Pavlenko
 * e-mail: admin3@triline.kz
 * @ PKF Temir
 */

namespace Triline\RightControlCrm;

use Bitrix\Main\Loader;
use Triline\RightControlCrm\CustomFilter;

use Bitrix\Crm\Service\Container;
use Bitrix\Crm\Service\Timeline\Item\Compatible\Wait;
use Bitrix\Crm\Service\Timeline\Repository\IgnoredItemsRules;
use Bitrix\Crm\Service\Timeline\Repository\Query;
use Bitrix\Crm\Service\Timeline\Repository\Result;
use Bitrix\Crm\Timeline\Entity\NoteTable;
use Bitrix\Crm\Timeline\Entity\TimelineBindingTable;
use Bitrix\Crm\Timeline\Entity\TimelineTable;
use Bitrix\Crm\Timeline\TimelineManager;
use Bitrix\Main\Entity\ReferenceField;
use Bitrix\Main\Type\DateTime;

class Repository extends Bitrix\Crm\Service\Timeline\Repository
{
//    public function getScheduledItems(?Query $queryParams = null): Result
//    {
//        $filter = $queryParams ? $queryParams->getFilter() : [];
//
//        if (Loader::includeModule('triline.rightscontrolcrm'))
//        {
//            $uFilter = CustomFilter::getCustomFilter();
//            $filter = array_merge($filter, $uFilter);
//        }
//
//        $filter = array_merge($filter, [
//            'CHECK_PERMISSIONS' => 'N',
//            'COMPLETED' => 'N',
//            'BINDINGS' => [
//                [
//                    'OWNER_TYPE_ID' => $this->context->getEntityTypeId(),
//                    'OWNER_ID' => $this->context->getEntityId(),
//                ],
//            ],
//        ]);
//        if (!$this->context->canReadEntity())
//        {
//            return new Result();
//        }
//
//        $dbResult = \CCrmActivity::GetList(
//            [
//                'DEADLINE' => 'ASC',
//            ],
//            $filter,
//            false,
//            false,
//            [
//                'ID',
//            ],
//            [
//                'QUERY_OPTIONS' => [
//                    'LIMIT' => 100,
//                    'OFFSET' => 0,
//                ],
//            ]
//        );
//
//        $items = [];
//        $activityIds = [];
//        while ($fields = $dbResult->Fetch())
//        {
//            $activityIds[] = (int)$fields['ID'];
//        }
//
//        if (!empty($activityIds))
//        {
//            $dbResult = \CCrmActivity::GetList(
//                [],
//                [
//                    '@ID' => $activityIds,
//                    'CHECK_PERMISSIONS' => 'N',
//                ],
//                false,
//                false,
//                [
//                    'ID',
//                    'OWNER_ID',
//                    'OWNER_TYPE_ID',
//                    'TYPE_ID',
//                    'PROVIDER_ID',
//                    'PROVIDER_TYPE_ID',
//                    'ASSOCIATED_ENTITY_ID',
//                    'CALENDAR_EVENT_ID',
//                    'DIRECTION',
//                    'SUBJECT',
//                    'STATUS',
//                    'DESCRIPTION',
//                    'DESCRIPTION_TYPE',
//                    'CREATED',
//                    'DEADLINE',
//                    'RESPONSIBLE_ID',
//                    'PROVIDER_PARAMS',
//                    'PROVIDER_DATA',
//                    'SETTINGS',
//                    'RESULT_MARK',
//                    'ORIGIN_ID',
//                    'LAST_UPDATED',
//                    'END_TIME',
//                    'STORAGE_TYPE_ID',
//                    'STORAGE_ELEMENT_IDS',
//                    'IS_INCOMING_CHANNEL',
//                    'LIGHT_COUNTER_AT',
//                ]
//            );
//            $activities = [];
//            while ($fields = $dbResult->Fetch())
//            {
//                $activities[$fields['ID']] = $fields;
//            }
//            foreach ($activityIds as $activityId)
//            {
//                if (!isset($activities[$activityId]))
//                {
//                    continue;
//                }
//                $items[$activityId] = $activities[$activityId];
//            }
//        }
//
//        \Bitrix\Crm\Timeline\EntityController::loadCommunicationsAndMultifields(
//            $items,
//            $this->context->getUserPermissions()->getCrmPermissions()
//        );
//
//        $items = NoteTable::loadForItems($items, NoteTable::NOTE_TYPE_ACTIVITY);
//
//        $items = array_values($items);
//
//        foreach ($items as $key => $item)
//        {
//            $items[$key] = Container::getInstance()->getTimelineScheduledItemFactory()::createItem($this->context, $item);
//        }
//
//        $fields = \Bitrix\Crm\Pseudoactivity\WaitEntry::getRecentByOwner(
//            $this->context->getEntityTypeId(),
//            $this->context->getEntityId()
//        );
//        if (is_array($fields))
//        {
//            $items[] = new Wait(
//                $this->context,
//                (new Item\Compatible\Model())
//                    ->setData($fields)
//                    ->setId('WAIT_' . $fields['ID'])
//                    ->setIsScheduled(true)
//            );
//        }
//        $this->sortItems($items);
//
//        return (new Result())
//            ->setItems($items)
//        ;
//    }

    private function loadHistoryItems(
        ?DateTime $offsetTime,
        ?DateTime &$nextOffsetTime,
        int $offsetId,
        int &$nextOffsetId,
        array $params = []
    ): array
    {
        $onlyFixed = isset($params['onlyFixed']) && $params['onlyFixed'] == true;
        $limit = (int)($params['limit'] ?? 0);
        $filter = (array)($params['filter'] ?? []);

        if (Loader::includeModule('triline.rightscontrolcrm'))
        {
            $uFilter = CustomFilter::getCustomFilter();
            $filter = array_merge($filter, $uFilter);
        }

        $isOffsetExist = isset($offsetTime) && $offsetId > 0;

        $bindingQuery = $this->prepareLoadHistoryBindingQuery($onlyFixed);
        $query = $this->prepareLoadHistoryQuery($limit, false, $bindingQuery, $filter, $offsetTime, $offsetId);
        $items = $this->fetchHistoryItems($offsetId, $query);

        $fetchDiff = $limit - count($items);
        if ($fetchDiff > 0 && $isOffsetExist)
        {
            $query = $this->prepareLoadHistoryQuery($fetchDiff, true, $bindingQuery, $filter, $offsetTime, $offsetId);
            $extraItems = $this->fetchHistoryItems($offsetId, $query);
            $items = array_merge($items, $extraItems);
        }

        $nextOffsetTime = null;
        if (!empty($items))
        {
            $item = $items[count($items) - 1];
            if (isset($item['CREATED']) && $item['CREATED'] instanceof DateTime)
            {
                $nextOffsetTime = $item['CREATED'];
                $nextOffsetId = (int)$item['ID'];
            }
        }

        $itemIDs = array_column($items, 'ID');
        $itemsMap = array_combine($itemIDs, $items);

        /*
         * @todo reorganize TimelineManager::prepareDisplayData and do not use it here
         */
        TimelineManager::prepareDisplayData(
            $itemsMap,
            0,
            null,
            true,
            ['type' => $this->context->getType()]
        );

        $itemsMap = array_values($itemsMap);

        foreach ($itemsMap as $key => $item)
        {
            $itemsMap[$key] = Container::getInstance()->getTimelineHistoryItemFactory()::createItem($this->context, $item);
        }

        return $itemsMap;
    }
}