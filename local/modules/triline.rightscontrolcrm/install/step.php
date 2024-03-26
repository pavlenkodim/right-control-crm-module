<?php

/**
 * Created by PhpStorm
 * User: Dmitry Pavlenko
 * e-mail: admin3@triline.kz
 * @ PKF Temir
 */

use \Bitrix\Main\Localization\Loc;

if (!check_bitrix_sessid())
    return;

if ($ex = $APPLICATION->GetException())
    echo CAdminMessage::ShowMessage(array(
        "TYPE" => "ERROR",
        "MESSAGE" => Loc::getMessage("MOD_INST_ERR"),
        "HTML" => true,
    ));
else
    echo CAdminMessage::ShowNote(Loc::getMessage("MOD_INST_OK"));

?>
<form action="<?php echo $APPLICATION->GetCurPage()?>">
    <input type="hidden" name="lang" value="<?php echo LANG?>">
    <input type="submit" name="" value="<?php echo GetMessage("MOD_BACK")?>">
</form>
