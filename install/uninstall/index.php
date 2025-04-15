<?php
use Bitrix\Main\Loader;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\SystemException;
use Bitrix\Main\Application;
use Bitrix\Main\HttpRequest;

Loc::loadMessages(__FILE__);

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

try {
    $request = Application::getInstance()->getContext()->getRequest();
    $module_id = 'fosdev.fosreviews';

    // Check if the module is installed
    if (!ModuleManager::isModuleInstalled($module_id)) throw new SystemException(Loc::getMessage('FOSREVIEWS_UNINSTALL_ERROR_MODULE_NOT_INSTALLED'));
    // Check the permission level of the user before doing anything else
    if ($APPLICATION->GetGroupRight($module_id) < "W") throw new SystemException(Loc::getMessage('FOSREVIEWS_UNINSTALL_ERROR_PERMISSIONS'));
    // Check if the request is a POST request
    if ($request->getRequestMethod() !== HttpRequest::METHOD_POST) throw new SystemException(Loc::getMessage('FOSREVIEWS_UNINSTALL_ERROR_POST_REQUIRED'));
    // Load the module
    if (!Loader::includeModule($module_id)) throw new SystemException(Loc::getMessage('FOSREVIEWS_UNINSTALL_ERROR_LOAD_MODULE'));

    // Check if we're in the first step of the uninstallation process
    if ($request->get('step') < 2) {
        // Display the first step
        $APPLICATION->IncludeAdminFile(Loc::getMessage('FOSREVIEWS_UNINSTALL_TITLE'), __DIR__ . '/step1.php');
    } else {
        // Display the second step
        $APPLICATION->IncludeAdminFile(Loc::getMessage('FOSREVIEWS_UNINSTALL_TITLE'), __DIR__ . '/step2.php');
    }
} catch (SystemException $e) {
    // Display a user-friendly error message if something goes wrong
    ShowError($e->getMessage());
}
