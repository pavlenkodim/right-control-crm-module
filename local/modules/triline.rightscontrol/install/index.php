<?php
use \Bitrix\Main\Localization\loc;
use \Bitrix\Main\Config as Conf;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;

loc::loadMessages(__FILE__);

class triline_rightscontrol extends CModule
{
    var $exclusionAdminFiles;

    function __construct()
    {
        $arModuleVersion = array();
        include(__DIR__."/version.php");

        $this -> exclusionAdminFiles = array(
            "..",
            ".",
            "menu.php",
            "operation_description.php",
            "task_description.php"
        );

        $this->MODULE_ID = "triline.rightscontrol";
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("TRILINE_RIGHTSCONTROLCRM_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("TRILINE_RIGHTSCONTROLCRM_MODULE_DESC");

        $this->PARTNER_NAME = Loc::getMessage("TRILINE_RIGHTSCONTROLCRM_PARTNER_NAME");
        $this->PARTNER_URL = Loc::getMessage("TRILINE_RIGHTSCONTROLCRM_PARTNER_URI");

        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = "Y";
        $this->SHOW_GROUP_RIGHTS = "Y";
    }

    public function GetPath($notDocumentRoot=false)
    {
        if($notDocumentRoot)
            return str_ireplace(Application::getDocumentRoot(),'',dirname(__DIR__));
        else
            return dirname(__DIR__);
    }

    public function isVersion()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '23.00.00');
    }

    function InstallDB()
    {
        return true;
    }

    function UnInstallDB()
    {
        Option::delete($this->MODULE_ID);
        return true;
    }

    function InstallEvents()
    {
        return true;
    }

    function UnInstallEvents()
    {
        return true;
    }

    function InstallFiles($arParams = array())
    {
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/local/modules/triline.rightscontrol/install/components",
            $_SERVER["DOCUMENT_ROOT"]."/local/components", true, true);
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx("/local/components/bitrix/crm.config.perms.role.edit");//component role edit
        DeleteDirFilesEx("/local/components/bitrix/crm.timeline");//component timeline
        return true;
    }

    function DoInstall()
    {
        global $APPLICATION, $DOCUMENT_ROOT;
        if($this->isVersion())
        {
            $this->InstallFiles();
            $this->InstallDB();
            $this->InstallEvents();
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
            $APPLICATION->IncludeAdminFile("Установка модуля", $DOCUMENT_ROOT."/local/modules/triline.rightscontrol/install/step.php");
        }
    }

    function DoUninstall()
    {
        global $APPLICATION, $DOCUMENT_ROOT;
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->UnInstallDB();
        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile("Деинсталляция модуля", $DOCUMENT_ROOT."/local/modules/triline.rightscontrol/install/unstep.php");
    }
}
