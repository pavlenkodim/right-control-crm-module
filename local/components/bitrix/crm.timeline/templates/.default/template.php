<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true){
	die();
}

use Bitrix\Crm\Component\EntityDetails\TimelineMenuBar\MenuIdResolver;
use Bitrix\Crm\Integration;
use Bitrix\Crm\Integration\AI\AIManager;

/**
 * Bitrix vars
 * @global CUser $USER
 * @global CMain $APPLICATION
 * @global CDatabase $DB
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $component
 */

Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/activity.js');
Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/crm/message.js');

Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'ui.buttons',
	'ui.icons',
	'ui.selector',
	'ui.notification',
	'crm.zoom',
	'ui.timeline',
	'ui.forms',
	'crm.timeline',
	'sidepanel',
	'crm.restriction.bitrix24',
	'ui.hint',
	'ui.viewer',
	'applayout',
	'im.public',
]);

//HACK: Preloading files for prevent trembling of player afer load.
Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/crm/timeline_player/timeline_player.css');
Bitrix\Main\Page\Asset::getInstance()->addCss('/bitrix/js/calendar/planner.css');

if (\Bitrix\Main\Loader::includeModule('disk'))
{
	Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/components/bitrix/disk.uf.file/templates/.default/script.js');
	Bitrix\Main\UI\Extension::load([
		'ajax',
		'core',
		'disk_external_loader',
		'ui.tooltip',
		'ui.viewer',
		'ui.hint',
		'disk.document',
		'disk.viewer.document-item',
		'disk.viewer.actions',
	]);
}

$jsLibraries = [
	'ui.viewer',
	'player',
	//used in CRM_DOCUMENT activity item in inline edit to select new create date
	'date',
];

if (\Bitrix\Main\Loader::includeModule('voximplant'))
{
	$jsLibraries[] = 'voximplant_transcript';
}

$spotlightFastenShowed = true;
if (!$arResult['READ_ONLY'])
{
	$spotlight = new \Bitrix\Main\UI\Spotlight("CRM_TIMELINE_FASTEN_SWITCHER");
	if (!$spotlight->isViewed($USER->GetID()))
	{
		$jsLibraries[] = 'spotlight';
		$spotlightFastenShowed = false;
	}
}

CJSCore::Init($jsLibraries);

if (
	AIManager::isAiCallProcessingEnabled()
	&& in_array((int)($arResult['ENTITY_TYPE_ID'] ?? 0), AIManager::SUPPORTED_ENTITY_TYPE_IDS, true)
)
{
	echo (\Bitrix\Crm\Tour\CopilotInCall::getInstance())
		->setEntityTypeId($arResult['ENTITY_TYPE_ID'])
		->build()
	;
}

$guid = $arResult['GUID'];
$prefix = mb_strtolower($guid);
$listContainerID = "{$prefix}_list";
$editorContainerID = "{$prefix}_editor";

$activityEditorID = "{$prefix}_editor";
$scheduleItems = $arResult['SCHEDULE_ITEMS'];
$historyItems = $arResult['HISTORY_ITEMS'];
$fixedItems = $arResult['FIXED_ITEMS'];

if (!empty($arResult['ERRORS']))
{
	foreach ($arResult['ERRORS'] as $error)
	{
		ShowError($error);
	}

	return;
}

?>
<div class="crm-entity-stream-container-content">
	<div id="<?=htmlspecialcharsbx($listContainerID)?>" class="crm-entity-stream-container-list">
		<div id="<?=htmlspecialcharsbx($editorContainerID)?>" class="crm-entity-stream-section crm-entity-stream-section-new">
			<div class="crm-entity-stream-section-icon crm-entity-stream-section-icon-new"></div>
			<div class="crm-entity-stream-section-content">
				<?
				$mode = true;
				if ($arParams['ENTITY_CONFIG_SCOPE'] !== Bitrix\Crm\Entity\EntityEditorConfigScope::PERSONAL)
				{
					$mode = CCrmAuthorizationHelper::CheckConfigurationUpdatePermission();
				}

				$categoryId =  (isset($arResult['EXTRAS']['CATEGORY_ID']) && (int)$arResult['EXTRAS']['CATEGORY_ID'] >= 0)
					? (int)$arResult['EXTRAS']['CATEGORY_ID']
					: null
				;

				$menuId = MenuIdResolver::getMenuId($arResult['ENTITY_TYPE_ID'], $arResult['USER_ID'], $categoryId);
				$APPLICATION->IncludeComponent(
					'bitrix:crm.timeline.menubar',
					'',
					[
						'GUID' => $arResult['GUID'],
						'MENU_ID' => $menuId,
						'ALLOW_MOVE_ITEMS' => $mode,
						'ENTITY_TYPE_ID' => $arResult['ENTITY_TYPE_ID'],
						'ENTITY_ID' => $arResult['ENTITY_ID'],
						'ENTITY_CATEGORY_ID' => $categoryId,
						'READ_ONLY' => $arResult['READ_ONLY'] ?? false,
					]
				);
?>
			</div>
		</div>
	</div>
</div><?
$filterClassName = $arResult['IS_HISTORY_FILTER_APPLIED']
	? 'crm-entity-stream-section-filter-show' : 'crm-entity-stream-section-filter-hide';

?><div id="timeline-filter" class="crm-entity-stream-section crm-entity-stream-section-filter <?=$filterClassName?>">
	<div class="crm-entity-stream-section-content">
		<div>
			<div class="crm-entity-stream-filter-container">
				<?
				$APPLICATION->includeComponent(
					'bitrix:main.ui.filter',
					'',
					array(
						'FILTER_ID' => $arResult['HISTORY_FILTER_ID'],
						'COMMON_PRESETS_ID' => $arResult['HISTORY_FILTER_PRESET_ID'],
						'THEME' => 'ROUNDED',
						'FILTER' => $arResult['HISTORY_FILTER'],
						'FILTER_PRESETS' => $arResult['HISTORY_FILTER_PRESETS'],
						'DISABLE_SEARCH' => false,
						'ENABLE_LIVE_SEARCH' => false,
						'ENABLE_LABEL' => true,
						'RESET_TO_DEFAULT_MODE' => false,
						'CONFIG' => array('AUTOFOCUS' => false),
						'LAZY_LOAD' => array(
							'GET_LIST' => '/bitrix/components/bitrix/crm.timeline/filter.ajax.php?action=list&filter_id='.urlencode($arResult['HISTORY_FILTER_ID']).'&siteID='.SITE_ID.'&'.bitrix_sessid_get(),
							'GET_FIELD' => '/bitrix/components/bitrix/crm.timeline/filter.ajax.php?action=field&filter_id='.urlencode($arResult['HISTORY_FILTER_ID']).'&siteID='.SITE_ID.'&'.bitrix_sessid_get(),
						)
					)
				);
				?>
				<span class="crm-entity-stream-filter-close"></span>
			</div>
		</div>
	</div>
</div>

<script type="text/javascript">
	<?
	if (\Bitrix\Main\Loader::includeModule('intranet'))
	{
		$menuExtensions = \Bitrix\Intranet\Binding\Menu::getMenuItems(
			Integration\Intranet\BindingMenu\SectionCode::TIMELINE,
			Integration\Intranet\BindingMenu\CodeBuilder::getMenuCode((int)($arResult['ENTITY_TYPE_ID'] ?? null)),
			[
				'inline' => true,
				'context' => [
					'ENTITY_ID' => $arResult['ENTITY_ID']
				]
			]
		);

		if ($menuExtensions)
		{
			echo 'var IntranetExtensions = ' . \CUtil::phpToJSObject($menuExtensions) . ";\n\n";
		}
	}
	$finalSummaryPhraseCodes = [
		'summary' => 'CRM_TIMELINE_FINAL_SUMMARY_TITLE',
		'documents' => 'CRM_TIMELINE_FINAL_SUMMARY_DOCUMENTS_TITLE',
	];

	if ($arResult['ENTITY_TYPE_ID'] === \CCrmOwnerType::SmartInvoice)
	{
		$finalSummaryPhraseCodes = [
			'summary' => 'CRM_TIMELINE_FINAL_SUMMARY_INVOICE_TITLE',
			'documents' => 'CRM_TIMELINE_FINAL_SUMMARY_DOCUMENTS_INVOICE_TITLE',
		];
	}

	?>
	BX.ready(
		function()
		{
			if (BX.Currency)
			{
				BX.Currency.setCurrencies(<?=CUtil::PhpToJSObject($arResult['CURRENCIES'], false, true, true)?>);
			}

			BX.CrmSchedule.messages =
			{
				planned: "<?=GetMessageJS('CRM_TIMELINE_SCHEDULE_PLANNED_NEW')?>",
				stubTitle: "<?=GetMessageJS('CRM_TIMELINE_COMMON_SCHEDULE_STUB_TITLE')?>",
				stub: "<?=GetMessageJS('CRM_TIMELINE_SCHEDULE_STUB_DEFAULT')?>",
				leadStub: "<?=GetMessageJS('CRM_TIMELINE_SCHEDULE_STUB_LEAD')?>",
				dealStub: "<?=GetMessageJS('CRM_TIMELINE_SCHEDULE_STUB_DEAL')?>"
			};

			BX.CrmHistory.messages =
			{
				filterButtonCaption: "<?=GetMessageJS('CRM_TIMELINE_FILTER_BUTTON_CAPTION')?>",
				filterEmptyResultStub: "<?=GetMessageJS('CRM_TIMELINE_FILTER_EMPTY_RESULT_STUB')?>"
			};

			BX.CrmHistoryItemMark.messages =
			{
				entitySuccessMark: "<?= GetMessageJS('CRM_TIMELINE_MARK_ENTITY_SUCCESS_MARK') ?>",
				entityFailedMark: "<?= GetMessageJS('CRM_TIMELINE_MARK_ENTITY_FAILED_MARK') ?>",
				entityContentTemplate: "<?= GetMessageJS('CRM_TIMELINE_MARK_ENTITY_CONTENT_TEMPLATE') ?>",
				dealSuccessMark: "<?=GetMessageJS('CRM_TIMELINE_DEAL_SUCCESS_MARK')?>",
				dealFailedMark: "<?=GetMessageJS('CRM_TIMELINE_DEAL_FAILED_MARK')?>",
				orderSuccessMark: "<?=GetMessageJS('CRM_TIMELINE_ORDER_SUCCESS_MARK')?>",
				orderFailedMark: "<?=GetMessageJS('CRM_TIMELINE_ORDER_FAILED_MARK')?>",
				incomingEmailSuccessMark: "<?=GetMessageJS('CRM_TIMELINE_INCOMING_EMAIL_SUCCESSMARK')?>",
				outgoingEmailSuccessMark: "<?=GetMessageJS('CRM_TIMELINE_OUTGOING_EMAIL_SUCCESSMARK')?>",
				incomingEmailRenewMark: "<?=GetMessageJS('CRM_TIMELINE_INCOMING_EMAIL_RENEWMARK')?>",
				outgoingEmailRenewMark: "<?=GetMessageJS('CRM_TIMELINE_OUTGOING_EMAIL_RENEWMARK')?>",
				incomingCallSuccessMark: "<?=GetMessageJS('CRM_TIMELINE_INCOMING_CALL_SUCCESSMARK')?>",
				outgoingCallSuccessMark: "<?=GetMessageJS('CRM_TIMELINE_OUTGOING_CALL_SUCCESSMARK')?>",
				incomingCallRenewMark: "<?=GetMessageJS('CRM_TIMELINE_INCOMING_CALL_RENEWMARK')?>",
				outgoingCallRenewMark: "<?=GetMessageJS('CRM_TIMELINE_OUTGOING_CALL_RENEWMARK')?>",
				meetingSuccessMark: "<?=GetMessageJS('CRM_TIMELINE_MEETING_SUCCESSMARK')?>",
				meetingRenewMark: "<?=GetMessageJS('CRM_TIMELINE_MEETING_RENEWMARK')?>",
				taskSuccessMark: "<?=GetMessageJS('CRM_TIMELINE_TASK_SUCCESSMARK_2')?>",
				taskRenewMark: "<?=GetMessageJS('CRM_TIMELINE_TASK_RENEWMARK')?>",
				webformSuccessMark: "<?=GetMessageJS('CRM_TIMELINE_WEBFORM_SUCCESSMARK')?>",
				webformRenewMark: "<?=GetMessageJS('CRM_TIMELINE_WEBFORM_RENEWMARK')?>",
				requestSuccessMark: "<?=GetMessageJS('CRM_TIMELINE_REQUEST_SUCCESSMARK_1')?>",
				requestRenewMark: "<?=GetMessageJS('CRM_TIMELINE_REQUEST_RENEWMARK_1')?>"
			};

			BX.CrmHistoryItemCreation.messages =
			{
				lead: "<?=GetMessageJS('CRM_TIMELINE_LEAD_CREATION')?>",
				deal: "<?=GetMessageJS('CRM_TIMELINE_DEAL_CREATION')?>",
				deal_recurring: "<?=GetMessageJS('CRM_TIMELINE_RECURRING_DEAL_CREATION')?>",
				order: "<?=GetMessageJS('CRM_TIMELINE_ORDER_CREATION')?>",
				order_payment: "<?=GetMessageJS('CRM_TIMELINE_ORDER_PAYMENT_CREATION')?>",
				order_shipment: "<?=GetMessageJS('CRM_TIMELINE_ORDER_SHIPMENT_CREATION')?>",
				contact: "<?=GetMessageJS('CRM_TIMELINE_CONTACT_CREATION')?>",
				company: "<?=GetMessageJS('CRM_TIMELINE_COMPANY_CREATION')?>",
				quote: "<?=GetMessageJS('CRM_TIMELINE_QUOTE_CREATION_MSGVER_1')?>",
				invoice: "<?=GetMessageJS('CRM_TIMELINE_INVOICE_CREATION')?>",
				task: "<?=GetMessageJS('CRM_TIMELINE_TASK_CREATION')?>",
				activity: "<?=GetMessageJS('CRM_TIMELINE_ACTIVITY_CREATION')?>",
				dealOrderTitle: "<?=GetMessageJS('CRM_TIMELINE_DEAL_ORDER_TITLE')?>",
			};

			BX.CrmHistoryItemLink.messages =
			{
				title: "<?= GetMessageJS('CRM_TIMELINE_LINK_TITLE') ?>",
				contentTemplate: "<?= GetMessageJS('CRM_TIMELINE_LINK_CONTENT_TEMPLATE') ?>"
			};

			BX.CrmHistoryItemUnlink.messages =
			{
				title: "<?= GetMessageJS('CRM_TIMELINE_UNLINK_TITLE') ?>",
				contentTemplate: "<?= GetMessageJS('CRM_TIMELINE_LINK_CONTENT_TEMPLATE') ?>"
			};

			BX.CrmTimelineCallAction.messages =
			{
				telephonyNotSupported: "<?=GetMessageJS('CRM_TIMELINE_TELEPHONY_NOT_SUPPORTED')?>"
			};

			BX.CrmSchedulePostponeAction.messages =
			{
				postpone: "<?=GetMessageJS('CRM_TIMELINE_POSTPONE')?>",
				forOneHour: "<?=GetMessageJS('CRM_TIMELINE_POSTPONE_1H')?>",
				forTwoHours: "<?=GetMessageJS('CRM_TIMELINE_POSTPONE_2H')?>",
				forThreeHours: "<?=GetMessageJS('CRM_TIMELINE_POSTPONE_3H')?>",
				forOneDay: "<?=GetMessageJS('CRM_TIMELINE_POSTPONE_1D')?>",
				forTwoDays: "<?=GetMessageJS('CRM_TIMELINE_POSTPONE_2D')?>",
				forThreeDays: "<?=GetMessageJS('CRM_TIMELINE_POSTPONE_3D')?>"
			};

			BX.CrmSchedulePostponeController.messages =
			{
				title: "<?=GetMessageJS('CRM_TIMELINE_POSTPONE')?>",
				forOneHour: "<?=GetMessageJS('CRM_TIMELINE_POSTPONE_1H')?>",
				forTwoHours: "<?=GetMessageJS('CRM_TIMELINE_POSTPONE_2H')?>",
				forThreeHours: "<?=GetMessageJS('CRM_TIMELINE_POSTPONE_3H')?>",
				forOneDay: "<?=GetMessageJS('CRM_TIMELINE_POSTPONE_1D')?>",
				forTwoDays: "<?=GetMessageJS('CRM_TIMELINE_POSTPONE_2D')?>",
				forThreeDays: "<?=GetMessageJS('CRM_TIMELINE_POSTPONE_3D')?>"
			};

			BX.CrmTimelineItem.messages =
			{
				from: "<?=GetMessageJS('CRM_TIMELINE_FROM')?>",
				to: "<?=GetMessageJS('CRM_TIMELINE_TO')?>",
				reciprocal: "<?=GetMessageJS('CRM_TIMELINE_RECIPROCAL')?>",
				details: "<?=GetMessageJS('CRM_TIMELINE_DETAILS')?>",
				termless: "<?=GetMessageJS('CRM_TIMELINE_TERMLESS')?>",
				comment: "<?=GetMessageJS('CRM_TIMELINE_COMMENT')?>",
				incomingEmail: "<?=GetMessageJS('CRM_TIMELINE_INCOMING_EMAIL_TITLE')?>",
				outgoingEmail: "<?=GetMessageJS('CRM_TIMELINE_OUTGOING_EMAIL_TITLE')?>",
				emailSuccessMark: "<?=GetMessageJS('CRM_TIMELINE_EMAIL_SUCCESSMARK')?>",
				emailRenewMark: "<?=GetMessageJS('CRM_TIMELINE_EMAIL_RENEWMARK')?>",
				incomingCall: "<?=GetMessageJS('CRM_TIMELINE_INCOMING_CALL_TITLE')?>",
				outgoingCall: "<?=GetMessageJS('CRM_TIMELINE_OUTGOING_CALL_TITLE')?>",
				callSuccessMark: "<?=GetMessageJS('CRM_TIMELINE_CALL_SUCCESSMARK')?>",
				callRenewMark: "<?=GetMessageJS('CRM_TIMELINE_CALL_RENEWMARK')?>",
				meeting: "<?=GetMessageJS('CRM_TIMELINE_MEETING_TITLE')?>",
				meetingSuccessMark: "<?=GetMessageJS('CRM_TIMELINE_MEETING_SUCCESSMARK')?>",
				meetingRenewMark: "<?=GetMessageJS('CRM_TIMELINE_MEETING_RENEWMARK')?>",
				task: "<?=GetMessageJS('CRM_TIMELINE_TASK_TITLE')?>",
				taskSuccessMark: "<?=GetMessageJS('CRM_TIMELINE_TASK_SUCCESSMARK_2')?>",
				taskRenewMark: "<?=GetMessageJS('CRM_TIMELINE_TASK_RENEWMARK')?>",
				webform: "<?=GetMessageJS('CRM_TIMELINE_WEBFORM_TITLE')?>",
				wait: "<?=GetMessageJS('CRM_TIMELINE_WAIT_TITLE')?>",
				sms: "<?=GetMessageJS('CRM_TIMELINE_SMS')?>",
				visit: "<?=GetMessageJS('CRM_TIMELINE_VISIT')?>",
				zoom: "<?=GetMessageJS('CRM_TIMELINE_ZOOM')?>",
				bizproc: "<?=GetMessageJS('CRM_TIMELINE_BIZPROC_TITLE')?>",
				activityRequest: "<?=GetMessageJS('CRM_TIMELINE_ACTIVITY_REQUEST_TITLE_1')?>",
				restApplication: "<?=GetMessageJS('CRM_TIMELINE_ACTIVITY_REST_APP_TITLE')?>",
				openLine: "<?=GetMessageJS('CRM_TIMELINE_ACTIVITY_OPEN_LINE')?>",
				expand: "<?=GetMessageJS('CRM_TIMELINE_EXPAND_SM')?>",
				collapse: "<?=GetMessageJS('CRM_TIMELINE_COLLAPSE_SM')?>",
				menuEdit: "<?=GetMessageJS('CRM_TIMELINE_MENU_EDIT')?>",
				menuView: "<?=GetMessageJS('CRM_TIMELINE_MENU_VIEW')?>",
				menuCancel: "<?=GetMessageJS('CRM_TIMELINE_MENU_CANCEL')?>",
				menuDelete: "<?=GetMessageJS('CRM_TIMELINE_MENU_DELETE')?>",
				menuFasten: "<?=GetMessageJS('CRM_TIMELINE_MENU_FASTEN')?>",
				menuUnfasten: "<?=GetMessageJS('CRM_TIMELINE_MENU_UNFASTEN')?>",
				send: "<?=GetMessageJS('CRM_TIMELINE_SEND')?>",
				cancel: "<?=GetMessageJS('CRM_TIMELINE_CANCEL_BTN')?>",
				removeConfirmTitle: "<?=GetMessageJS('CRM_TIMELINE_ACTIVITY_DELETION_TITLE_CONFIRM')?>",
				removeConfirm: "<?=GetMessageJS('CRM_TIMELINE_ACTIVITY_DELETION_CONFIRM')?>",
				meetingRemove: "<?=GetMessageJS('CRM_TIMELINE_MEETING_DELETION_CONFIRM')?>",
				taskRemove: "<?=GetMessageJS('CRM_TIMELINE_TASK_DELETION_CONFIRM')?>",
				emailRemove: "<?=GetMessageJS('CRM_TIMELINE_EMAIL_DELETION_CONFIRM')?>",
				commentRemove: "<?=GetMessageJS('CRM_TIMELINE_COMMENT_DELETION_CONFIRM')?>",
				deliveryRemove: "<?=GetMessageJS('CRM_TIMELINE_DELIVERY_ACTIVITY_DELETION_CONFIRM')?>",
				outgoingCallRemove: "<?=GetMessageJS('CRM_TIMELINE_OUTGOING_CALL_DELETION_CONFIRM')?>",
				incomingCallRemove: "<?=GetMessageJS('CRM_TIMELINE_INCOMING_CALL_DELETION_CONFIRM')?>",
				document: "<?=GetMessageJS('CRM_TIMELINE_DOCUMENT')?>",
				documentRemove: "<?=GetMessageJS('CRM_TIMELINE_DOCUMENT_DELETION_CONFIRM')?>",
				zoomCreatedMessage: '<?=GetMessageJS("CRM_TIMELINE_ZOOM_CREATED_CONFERENCE_MESSAGE")?>',
				zoomCreatedCopyInviteLink: '<?=GetMessageJS("CRM_TIMELINE_ZOOM_CREATED_COPY_INVITE_LINK")?>',
				zoomCreatedStartConference: '<?=GetMessageJS("CRM_TIMELINE_ZOOM_CREATED_START_CONFERENCE")?>',
				storeDocumentProduct: "<?=GetMessageJS('CRM_TIMELINE_STORE_DOCUMENT_TITLE_2')?>",
				storeDocumentProductDescription: "<?=GetMessageJS('CRM_TIMELINE_STORE_DOCUMENT_DESCRIPTION_3')?>",
				storeDocumentService: "<?=GetMessageJS('CRM_TIMELINE_STORE_DOCUMENT_SERVICE_TITLE')?>",
				storeDocumentServiceDescription: "<?=GetMessageJS('CRM_TIMELINE_STORE_DOCUMENT_SERVICE_DESCRIPTION')?>",
				automationDebugger: "<?=GetMessageJS('CRM_TIMELINE_AUTOMATION_DEBUGGER_TITLE')?>",
			};

			BX.CrmHistoryItemSender.messages =
			{
				title: "<?=GetMessageJS('CRM_TIMELINE_SENDER_TITLE')?>",
				read: "<?=GetMessageJS('CRM_TIMELINE_SENDER_READ')?>",
				click: "<?=GetMessageJS('CRM_TIMELINE_SENDER_CLICK')?>",
				unsub: "<?=GetMessageJS('CRM_TIMELINE_SENDER_UNSUB')?>",
				error: "<?=GetMessageJS('CRM_TIMELINE_SENDER_ERROR')?>",
				removed: "<?=GetMessageJS('CRM_TIMELINE_SENDER_NAME_REMOVED')?>"
			};

			BX.CrmEntityChat.messages =
				{
					invite: "<?=GetMessageJS('CRM_TIMELINE_CHAT_INVITE_MSGVER_1')?>"
				};

			BX.message({
				"CRM_TIMELINE_CALL_TRANSCRIPT": '<?=GetMessageJS("CRM_TIMELINE_CALL_TRANSCRIPT")?>',
				"CRM_TIMELINE_CALL_TRANSCRIPT_PENDING": '<?=GetMessageJS("CRM_TIMELINE_CALL_TRANSCRIPT_PENDING")?>',
				"CRM_TIMELINE_BIZPROC_CREATED": '<?=GetMessageJS("CRM_TIMELINE_BIZPROC_CREATED")?>',
				"CRM_TIMELINE_BIZPROC_COMPLETED": '<?=GetMessageJS("CRM_TIMELINE_BIZPROC_COMPLETED")?>',
				"CRM_TIMELINE_BIZPROC_TERMINATED": '<?=GetMessageJS("CRM_TIMELINE_BIZPROC_TERMINATED")?>',
				"CRM_TIMELINE_VISIT_AT": '<?=GetMessageJS("CRM_TIMELINE_VISIT_AT")?>',
				"CRM_TIMELINE_VISIT_WITH": '<?=GetMessageJS("CRM_TIMELINE_VISIT_WITH")?>',
				"CRM_TIMELINE_VISIT_VKONTAKTE_PROFILE": '<?=GetMessageJS("CRM_TIMELINE_VISIT_VKONTAKTE_PROFILE")?>',
				"CRM_TIMELINE_FASTEN_LIMIT_MESSAGE": '<?=GetMessageJS("CRM_TIMELINE_FASTEN_LIMIT_MESSAGE")?>',
				"CRM_TIMELINE_EMPTY_COMMENT_MESSAGE": '<?=GetMessageJS("CRM_TIMELINE_EMPTY_COMMENT_MESSAGE")?>',
				"CRM_TIMELINE_SPOTLIGHT_FASTEN_MESSAGE": '<?=GetMessageJS("CRM_TIMELINE_SPOTLIGHT_FASTEN_MESSAGE")?>',
				"CRM_TIMELINE_SCORING_TITLE_2": '<?=GetMessageJS("CRM_TIMELINE_SCORING_TITLE_2")?>',
				"CRM_TIMELINE_DETAILS": '<?=GetMessageJS("CRM_TIMELINE_DETAILS")?>',
				"CRM_TIMELINE_COLLAPSE": '<?=GetMessageJS("CRM_TIMELINE_COLLAPSE")?>',
				"CRM_TIMELINE_ZOOM_SUCCESSFUL_ACTIVITY": '<?=GetMessageJS("CRM_TIMELINE_ZOOM_SUCCESSFUL_ACTIVITY")?>',
				"CRM_TIMELINE_ZOOM_CONFERENCE_END": '<?=GetMessageJS("CRM_TIMELINE_ZOOM_CONFERENCE_END_2")?>',
				"CRM_TIMELINE_ZOOM_JOINED_CONFERENCE": '<?=GetMessageJS("CRM_TIMELINE_ZOOM_JOINED_CONFERENCE")?>',
				"CRM_TIMELINE_ZOOM_MEETING_RECORD": '<?=GetMessageJS("CRM_TIMELINE_ZOOM_MEETING_RECORD")?>',
				"CRM_TIMELINE_ZOOM_MEETING_RECORD_PART": '<?=GetMessageJS("CRM_TIMELINE_ZOOM_MEETING_RECORD_PART")?>',
				"CRM_TIMELINE_ZOOM_MEETING_RECORD_IN_PROCESS": '<?=GetMessageJS("CRM_TIMELINE_ZOOM_MEETING_RECORD_IN_PROCESS")?>',
				"CRM_TIMELINE_ZOOM_DOWNLOAD_VIDEO": '<?=GetMessageJS("CRM_TIMELINE_ZOOM_DOWNLOAD_VIDEO")?>',
				"CRM_TIMELINE_ZOOM_DOWNLOAD_AUDIO": '<?=GetMessageJS("CRM_TIMELINE_ZOOM_DOWNLOAD_AUDIO")?>',
				"CRM_TIMELINE_ZOOM_CLICK_TO_WATCH": '<?=GetMessageJS("CRM_TIMELINE_ZOOM_CLICK_TO_WATCH")?>',
				"CRM_TIMELINE_ZOOM_LOGIN_REQUIRED": '<?=GetMessageJS("CRM_TIMELINE_ZOOM_LOGIN_REQUIRED")?>',
				"CRM_TIMELINE_ZOOM_PLAY_LINK_VIDEO": '<?=GetMessageJS("CRM_TIMELINE_ZOOM_PLAY_LINK_VIDEO")?>',
				"CRM_TIMELINE_ZOOM_COPY_PASSWORD": '<?=GetMessageJS("CRM_TIMELINE_ZOOM_COPY_PASSWORD")?>',
				"CRM_TIMELINE_DOCUMENT_VIEWED": '<?=GetMessageJS("CRM_TIMELINE_DOCUMENT_VIEWED")?>',
				"CRM_TIMELINE_DOCUMENT_VIEWED_STATUS": '<?=GetMessageJS("CRM_TIMELINE_DOCUMENT_VIEWED_STATUS")?>',
				"CRM_TIMELINE_DOCUMENT_CREATED_STATUS": '<?=GetMessageJS("CRM_TIMELINE_DOCUMENT_CREATED_STATUS")?>',
				"CRM_TIMELINE_PLAYBACK_RATE_SELECTOR_RATE_1": '<?=GetMessageJS("CRM_TIMELINE_PLAYBACK_RATE_SELECTOR_RATE_1")?>',
				"CRM_TIMELINE_PLAYBACK_RATE_SELECTOR_RATE_1.5": '<?=GetMessageJS("CRM_TIMELINE_PLAYBACK_RATE_SELECTOR_RATE_1.5")?>',
				"CRM_TIMELINE_PLAYBACK_RATE_SELECTOR_RATE_2": '<?=GetMessageJS("CRM_TIMELINE_PLAYBACK_RATE_SELECTOR_RATE_2")?>',
				"CRM_TIMELINE_PLAYBACK_RATE_SELECTOR_RATE_3": '<?=GetMessageJS("CRM_TIMELINE_PLAYBACK_RATE_SELECTOR_RATE_3")?>',
				"CRM_TIMELINE_PLAYBACK_RATE_SELECTOR_TEXT": '<?=GetMessageJS("CRM_TIMELINE_PLAYBACK_RATE_SELECTOR_TEXT")?>',
				"DISK_TMPLT_THUMB": '',
				"DISK_TMPLT_THUMB2": '',
			});

			var timeline = BX.CrmTimelineManager.create(
				"<?=CUtil::JSEscape($guid)?>",
				{
					ownerTypeId: <?=$arResult['ENTITY_TYPE_ID']?>,
					ownerId: <?=$arResult['ENTITY_ID']?>,
					ownerInfo: <?=CUtil::PhpToJSObject($arResult['ENTITY_INFO'])?>,
					userId: <?=$arResult['USER_ID']?>,
					readOnly: <?=$arResult['READ_ONLY'] ? 'true' : 'false'?>,
					currentUser: <?=\Bitrix\Main\Web\Json::encode($arResult['LAYOUT_CURRENT_USER'])?>,
					pingSettings: <?=\Bitrix\Main\Web\Json::encode($arResult['PING_SETTINGS'])?>,
					pullTagName: "<?=CUtil::JSEscape($arResult['PULL_TAG_NAME'])?>",
					progressSemantics: "<?=CUtil::JSEscape($arResult['PROGRESS_SEMANTICS'])?>",
					containerId: "<?=CUtil::JSEscape($listContainerID)?>",
					activityEditorId: "<?=CUtil::JSEscape($arResult['ACTIVITY_EDITOR_ID'])?>",
					chatData: <?=CUtil::PhpToJSObject($arResult['CHAT_DATA'])?>,
					scheduleData: <?=\Bitrix\Main\Web\Json::encode($scheduleItems)?>,
					historyData: <?=\Bitrix\Main\Web\Json::encode($historyItems)?>,
					historyNavigation: <?=CUtil::PhpToJSObject($arResult['HISTORY_NAVIGATION'])?>,
					historyFilterId: "<?=CUtil::JSEscape($arResult['HISTORY_FILTER_ID'])?>",
					isHistoryFilterApplied: <?=$arResult['IS_HISTORY_FILTER_APPLIED'] ? 'true' : 'false'?>,
					fixedData: <?=\Bitrix\Main\Web\Json::encode($fixedItems)?>,
					ajaxId: "<?=CUtil::JSEscape($arResult['AJAX_ID'])?>",
					currentUrl: "<?=CUtil::JSEscape($arResult['CURRENT_URL'])?>",
					serviceUrl: "/bitrix/components/bitrix/crm.timeline/ajax.php?&site=<?=SITE_ID?>&<?=bitrix_sessid_get()?>",
					editorContainer: "<?=CUtil::JSEscape($editorContainerID)?>",
					spotlightFastenShowed: <?=$spotlightFastenShowed ? 'true' : 'false'?>,
					audioPlaybackRate: <?= (float) $arResult['AUDIO_PLAYBACK_RATE'] ?>
				}
			);
			BX.CrmTimelineManager.setDefault(timeline);

		}
	);
</script>
<?php
$userPermissions = CCrmRole::GetUserPerms($USER->GetID());
if ($userPermissions['ACTIVITY']['READ'])
{
    $perm = json_encode($userPermissions['ACTIVITY']['READ']);
}

function getEmployees($id, $subdep = false)
{
    $arEmployees = [];
    if(CModule::IncludeModule("intranet")){
        $arUsers = CIntranetUtils::GetSubordinateEmployees($id, $subdep);
        while($User = $arUsers->GetNext()){
            if($User['ID'] != $id){
                $arEmployees[] = $User['ID'];

            }
        }
    }
    return $arEmployees;
}

?>
<script>
    const webhuk = 'https://192.168.1.137/rest/1/vs8qgambvrfdxb7l';
    const user = {
              id: <?=$USER->GetID()?>,
              isAdmin: '<?=$USER->IsAdmin()?>',
              perm: <?=$perm?>,
              department: <?= json_encode(getEmployees($USER->GetID()))?>
          },
          entity = <?=$arResult['ENTITY_ID']?>,
          entityType = <?=$arResult['ENTITY_TYPE_ID']?>,
          perms = {
              none: '',
              self: 'A',
              department: 'D',
              subdepartment: 'F',
              all: 'X'
          };

          if (user.perm['-'] === perms.subdepartment) {
              user.department = <?= json_encode(getEmployees($USER->GetID(), true))?>;
          }

    document.addEventListener('DOMContentLoaded', () => {
        let elementsProcessed = [];
        user.department.push(`${user.id}`);

        if (user.perm['-'] === undefined || user.perm['-'] === '') {
            permsNotChange();
        }

        const streamContainer = document.querySelector('.crm-entity-stream-container');
        const observer = new MutationObserver(mutations => {
            mutations.forEach(mutations => {
                if (mutations.previousSibling) {
                    const newActivities = document.querySelectorAll('.crm-timeline__card');
                    const newEvents = document.querySelectorAll('.crm-entity-stream-content-event');
                    main(newActivities);
                    mainForEvents(newEvents);
                }
            })
        });
        observer.observe(streamContainer, { childList: true, subtree: true });

        // For Events - CalendarActivity
        function mainForEvents(events) {
            let eventId = 1;
            events.forEach(item => {
                if (item.firstChild.className === 'crm-entity-stream-content-live-im-detail') {
                    return;
                }
                if (elementsProcessed.includes(item.dataset.id)) {
                    return;
                }
                const userId = prepareUserIdForEvents(item);

                item.dataset.id = `${eventId}`;
                elementsProcessed.push(`${eventId}`);
                eventId++;

                checkPerms(userId, item);
            })
        }

        function prepareUserIdForEvents (element) {
            let targetUrl;
            element.childNodes.forEach(child => {
                if (child.tagName === 'A') {
                    targetUrl = child.getAttribute('href');
                }
            })
            const id = targetUrl.replace(/[^0-9]/g, '');
            return {result: {AUTHOR_ID: id}};
        }

        // For Activity
        function main (activities) {
            activities.forEach(item => {
                if (elementsProcessed.includes(item.dataset.id)) {
                    return;
                }
                // console.log(item); // TODO: Удалить после тестов
                getOwnerData(item)
                    .then(response => {
                        checkPerms(response, item)
                    })
                    .catch(console.error);
            });
        }

        async function getOwnerData(item) {
            elementsProcessed.push(item.dataset.id);

            if (/ACTIVITY/.test(item.dataset.id)) {
                const activityId = item.dataset.id.replace('ACTIVITY_', '');
                const response = fetch(`${webhuk}/crm.activity.get.json`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json;charset=utf-8'
                    },
                    body: JSON.stringify({id: activityId})
                });
                return (await response).json()
            } else {
                const timelineLogId = item.dataset.id;
                const response = fetch(`${webhuk}/crm.timeline.comment.get.json`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json;charset=utf-8'
                    },
                    body: JSON.stringify({id: timelineLogId})
                });
                return (await response).json();
            }
        }

        // Main function for check permission and hide elements
        function checkPerms(data, element) {
            // console.log(user.perm['-']); // TODO: Удалить после тестов
            if (!user.isAdmin) {
                try {
                    switch (user.perm['-']) {
                        case perms.none:
                            hide(element);
                            break;
                        case perms.self:
                            if (data.result.AUTHOR_ID != user.id && data.result.RESPONSIBLE_ID != user.id) {
                                hide(element);
                            }
                            break;
                        case perms.department:
                            if (user.department.indexOf(data.result.AUTHOR_ID) === -1 && user.department.indexOf(data.result.RESPONSIBLE_ID) === -1) {
                                hide(element);
                            }
                            break;
                        case perms.subdepartment:
                            if (user.department.indexOf(data.result.AUTHOR_ID) === -1 && user.department.indexOf(data.result.RESPONSIBLE_ID) === -1) {
                                hide(element);
                            }
                            break;
                        case perms.all:
                            break;
                    }
                } catch (e) {
                    console.error(e);
                    permsNotChange('Непредвиденная ошибка при проверке прав доступа!');
                }
            }
        }

        function permsNotChange (message = null) {
            const parentElement = document.querySelector('.crm-entity-stream-section-live-im');
            const elementWarn = document.createElement('div');
            const defaultMessage = 'У вас нет прав для просмотра дел или права не настроены. \nОбратитесь к вашему администратору.'
            elementWarn.textContent = message ? message : defaultMessage;
            elementWarn.style.textAlign = 'center';
            elementWarn.style.color = '#fff';
            elementWarn.style.fontWeight = '500';
            elementWarn.style.padding = '10px';
            elementWarn.style.marginTop = '10px';
            elementWarn.style.borderRadius = '30px';
            elementWarn.style.backgroundColor = '#FF6666';
            parentElement.append(elementWarn);
        }

        function hide(element) {
            element.parentElement.parentElement.style.display = 'none';
            // console.log(element); // TODO: Удалить после тестов
        }
    });
</script>
<?
