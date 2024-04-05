<?php

// Подключение ядра Bitrix24
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/header.php');
$APPLICATION->SetTitle("Exemple");

use Bitrix\Crm\Security\EntityAuthorization;
use Bitrix\Crm\RoleTable;
use Bitrix\Crm\Service;

// Установка текущего пользователя
global $USER;
if (!$USER->IsAuthorized()) {
    die('User is not authorized');
}
$user = $USER->GetID();
//// Проверка прав доступа текущего пользователя
//if (!EntityAuthorization::canEntityAddToRole(\CCrmOwnerType::Activity)) {
//    die('Access denied');
//}
echo "<pre>";

//$userPerms = \CCrmRole::GetUserPerms(1);
//print_r($userPerms);
//$userPermissions = Service\Container::getInstance()->getUserPermissions();
$userPermissions = CCrmRole::GetUserPerms($user);
print_r($userPermissions);

// Получение списка всех ролей
$roles = RoleTable::getList(array(
    'select' => array('ID', 'NAME')
))->fetchAll();

echo "\nRoles: \n";
print_r($roles);


//echo 'Permissions for Activity have been updated successfully';

// Выход из ядра Bitrix24
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/footer.php');
