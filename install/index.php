<?php
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\GroupTable;
use Bitrix\Main\Application;
use Bitrix\Main\SystemException;
use Bitrix\Main\AccessDeniedException;

Loc::loadMessages(__FILE__);
Loader::includeModule('main');

if (class_exists('fosdev_fosreviews')) {
    return;
}

Class fosdev_fosreviews extends CModule
{
    public $MODULE_ID = "fosdev.fosreviews";
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $PARTNER_NAME;
    public $PARTNER_URI;

    public function __construct()
    {
        if (file_exists(__DIR__ . '/version.php')) {
            $arModuleVersion = array();

            include __DIR__ . '/version.php';

            $this->MODULE_NAME = Loc::getMessage('FOSREVIEWS_MODULE_NAME');
            $this->MODULE_DESCRIPTION = Loc::getMessage('FOSREVIEWS_MODULE_DESCRIPTION');
            $this->MODULE_VERSION = $arModuleVersion['VERSION'];
            $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
            $this->PARTNER_NAME = Loc::getMessage('FOSREVIEWS_PARTNER_NAME');
            $this->PARTNER_URI = Loc::getMessage('FOSREVIEWS_PARTNER_URI');
        }
    }

    public function GetModuleRightList()
    {
        return array(
            "reference_id" => array("D", "W"),
            "reference" => array(
                "[D] " . Loc::getMessage('FOSREVIEWS_ACCESS_D'),
                "[W] " . Loc::getMessage('FOSREVIEWS_ACCESS_W'),
            ),
        );
    }

    public function DoInstall()
    {
        global $APPLICATION, $USER;

        if (!$USER->IsAdmin()) return;

        if (!ModuleManager::IsModuleInstalled($this->MODULE_ID)) {
            ModuleManager::registerModule($this->MODULE_ID);
            $this->InstallDB();
            $this->InstallFiles();
            $this->InstallEvents();

            $APPLICATION->IncludeAdminFile(Loc::getMessage('FOSREVIEWS_INSTALL_TITLE'), __DIR__ . '/step1.php');
        } else {
            $APPLICATION->ThrowException(Loc::getMessage('FOSREVIEWS_INSTALL_ERROR'));
        }
    }
    public function DoUninstall()
    {
        global $APPLICATION;

        if (!check_bitrix_sessid()) throw new SystemException("Invalid session ID");
        if (!ModuleManager::isModuleInstalled($this->MODULE_ID)) throw new SystemException(Loc::getMessage('FOSREVIEWS_UNINSTALL_ERROR'));
        if ($APPLICATION->GetGroupRight($this->MODULE_ID) < "W") throw new AccessDeniedException("Access Denied");

        $APPLICATION->includeAdminFile(Loc::getMessage('FOSREVIEWS_UNINSTALL_TITLE'), __DIR__ . '/uninstall/step1.php');
    }
    public function InstallDB()
    {
        global $DB;
    
        $DB->RunSQLBatch(__DIR__ . '/db/install.sql');
    
        return true;
    }

    public function UnInstallDB()
    {
        global $DB;
    
        $DB->RunSQLBatch(__DIR__ . '/db/uninstall.sql');
    
        return true;
    }

    public function InstallFiles()
    {
        CopyDirFiles(
            __DIR__ . '/admin',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin',
            true,
            true
        );
        CopyDirFiles(
            __DIR__ . '/tools',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/tools',
            true,
            true
        );
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/{$this->MODULE_ID}/components",
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/components',
            true,
            true
        );

        return true;
    }

    public function UnInstallFiles()
    {
        DeleteDirFiles(
            __DIR__ . '/admin',
            $_SERVER['DOCUMENT_ROOT'] . '/bitrix/admin'
        );
        DeleteDirFilesEx('/bitrix/tools/fosreviews/');
        DeleteDirFilesEx('/bitrix/components/fosdev/reviews.list/');

        return true;
    }

    public function InstallEvents()
    {
        return true;
    }

    public function UnInstallEvents()
    {
        return true;
    }
}