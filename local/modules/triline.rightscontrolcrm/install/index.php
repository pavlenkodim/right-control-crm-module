<?php

/**
 * Created by PhpStorm
 * User: Dmitry Pavlenko
 * e-mail: admin3@triline.kz
 * @ PKF Temir
 */

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

    public function GetPath($notDocumentRoot=false): array|string
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

    function InstallDB(): bool
    {
//        RegisterModule("triline.rightscontrolcrm");

        return true;
    }

    function UnInstallDB(): bool
    {
//        UnRegisterModule("triline.rightscontrolcrm");

        return true;
    }

    function InstallEvents(): bool
    {
        return true;
    }

    function UnInstallEvents(): bool
    {
        return true;
    }

    public function InstallFiles(): bool
    {
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/local/modules/".$this->MODULE_ID."/install/components",
            $_SERVER["DOCUMENT_ROOT"]."/local/components",
            true,
            true
        );

        return true;
    }

    public function UnInstallFiles(): bool
    {
        \Bitrix\Main\IO\Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"] . "/local/components/bitrix/crm.timeline/");

        return true;
    }

    function DoInstall(): void
    {
        global $APPLICATION;
        if($this->isVersion())
        {
            if (\Bitrix\Main\ModuleManager::isModuleInstalled('crm'))
            {
                $this->InstallDB();
                $this->InstallEvents();
                $this->InstallFiles();

                \Bitrix\Main\ModuleManager::registerModule($this->MODULE_ID);
            }
            else
            {
                $APPLICATION->ThrowException(Loc::getMessage("TRILINE_RIGHTSCONTROLCRM_INSTALL_ERROR_CRM"));
            }
        }
        else
        {
            $APPLICATION->ThrowException(Loc::getMessage("TRILINE_RIGHTSCONTROLCRM_INSTALL_ERROR_VERSION"));
        }

        $APPLICATION->IncludeAdminFile(Loc::getMessage("TRILINE_RIGHTSCONTROLCRM_INSTALL_TITLE"), $this->GetPath()."/install/step.php");
    }

    function DoUninstall(): void
    {
        global $APPLICATION;

        $this->UnInstallFiles();
        $this->UnInstallEvents();
        $this->UnInstallDB();

        \Bitrix\Main\ModuleManager::unRegisterModule($this->MODULE_ID);

        $APPLICATION->IncludeAdminFile(Loc::getMessage("TRILINE_RIGHTSCONTROLCRM_UNINSTALL_TITLE"), $this->GetPath()."/install/unstep.php");
    }

    function GetModuleRightList(): array
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