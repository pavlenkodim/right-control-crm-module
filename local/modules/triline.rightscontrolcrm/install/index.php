<?php

use \Bitrix\Main\Localization\loc;
use \Bitrix\Main\Config as Conf;
use \Bitrix\Main\Config\Option;
use \Bitrix\Main\Loader;
use \Bitrix\Main\Application;

loc::loadMessages(__FILE__);

class triline_rightscontrolcrm extends CModule
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

        $this->MODULE_ID = "triline.rightscontrolcrm";
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

    public function isVersionD7()
    {
        return CheckVersion(\Bitrix\Main\ModuleManager::getVersion('main'), '14.00.00');
    }

    function InstallDB()
    {
        return true;
    }

    function UnInstallDB()
    {
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
        return true;
    }

    function UnInstallFiles()
    {
        return true;
    }

    function DoInstall()
    {
        if($this->isVersionD7())
        {
            \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);

            $this->InstallDB();
            $this->InstallEvents();
            $this->InstallFiles();
        }
    }

    function DoUninstall()
    {
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->UnInstallDB();

    }

    function GetModuleRightList()
    {
        return array(
            "reference_id" => array("D","K","S","W"),
            "reference" => array(
                "[D] ".Loc::getMessage("TRILINE_RIGHTSCONTROLCRM_DENIED"),
                "[K] ".Loc::getMessage("TRILINE_RIGHTSCONTROLCRM_READ_COMPONENT"),
                "[S] ".Loc::getMessage("TRILINE_RIGHTSCONTROLCRM_WRITE_SETTINGS"),
                "[W] ".Loc::getMessage("TRILINE_RIGHTSCONTROLCRM_FULL"))
        );
    }
}