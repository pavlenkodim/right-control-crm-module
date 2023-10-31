<?php

/**
 * Created by PhpStorm
 * User: Dmitry Pavlenko
 * @ Triline
 * @ PKF Temir
 */

use \Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid())
    return;

echo CAdminMessage::ShowNote(GetMessage("MOD_UNINST_OK"));
?>
<form action="<?php echo $APPLICATION->GetCurPage()?>">
    <input type="hidden" name="lang" value="<?php echo LANG?>">
    <input type="submit" name="" value="<?php echo GetMessage("MOD_BACK")?>">
</form>
