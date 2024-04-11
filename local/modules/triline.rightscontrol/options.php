<?php
/**
 * Created by PhpStorm
 * User: Dmitry Pavlenko
 * @ Triline
 * @ PKF Temir
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;

$module_id = 'triline.rightscontrol';

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'] . BX_ROOT . "/modules/main/options.php");
Loc::loadMessages(__FILE__);

\Bitrix\Main\Loader::includeModule($module_id);

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();

$aTabs = array(
    array(
        'DIV' => 'edit1',
        'TAB' => Loc::getMessage('TRILINE_RIGHTSCONTROLCRM_MAIN_SETTINGS'),
        'OPTIONS' => array(
            array('webhuk', Loc::getMessage('TRILINE_RIGHTSCONTROLCRM_FIELD_WEBHUK').':', '', array('text', 80))
        )
    )
);

if ($request->isPost() && $request['Update'] && check_bitrix_sessid())
{
    foreach ($aTabs as $aTab)
    {
        foreach ($aTab['OPTIONS'] as $arOption)
        {
            if (!is_array($arOption))
                continue;

            if ($arOption['note'])
                continue;

            $optionName = $arOption[0];
            $optionValue = $request->getPost($optionName);
            Option::set($module_id, $optionName, is_array($optionValue) ? implode(",", $optionValue) : $optionValue);
        }
    }
}

// Render
$tabControl = new CAdminTabControl('tabControl', $aTabs);
?>
<? $tabControl->Begin(); ?>
<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=htmlspecialcharsbx($request['mid'])?>&amp;lang=<?=$request['lang']?>" name="triline.rightscontrol">

    <? foreach ($aTabs as $aTab):
            if ($aTab['OPTIONS']):?>
        <? $tabControl->BeginNextTab();?>
        <? __AdmSettingsDrawList($module_id, $aTab['OPTIONS']);?>

    <? endif;
    endforeach; ?>

    <?
    $tabControl->BeginNextTab();

    $tabControl->Buttons(); ?>

    <input type="submit" name="Update" value="<?echo GetMessage('MAIN_SAVE')?>">
    <input type="reset" name="reset" value="<?echo GetMessage('MAIN_RESET')?>">
    <?=bitrix_sessid_post();?>
</form>

<? $tabControl->End(); ?>
