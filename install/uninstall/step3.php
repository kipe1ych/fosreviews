<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\AccessDeniedException;
use Bitrix\Main\ModuleManager;

// Protect the file from direct access
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

// Load language messages
Loc::loadMessages(__FILE__);
$module_id = 'fosdev.fosreviews';

// Include the module
if (!Loader::IncludeModule($module_id)) {
    ShowError(Loc::getMessage('FOSREVIEWS_UNINSTALL_ERROR_LOAD_MODULE'));
    return;
}
// Check user permissions and privileges before proceeding
if (!check_bitrix_sessid() || $APPLICATION->GetGroupRight($module_id) < "W" || !$USER->IsAdmin()) throw new AccessDeniedException("Access Denied");

if ($_POST['save'] != 'Y') {
    try {
        $fosreviewsModule = new fosdev_fosreviews;
        $fosreviewsModule->UnInstallDB();
        $fosreviewsModule->UnInstallFiles();
        $fosreviewsModule->UnInstallEvents();
        
        ModuleManager::unRegisterModule($fosreviewsModule->MODULE_ID);

        CAdminMessage::ShowMessage([
            'TYPE' => 'OK',
            'MESSAGE' => Loc::getMessage('MOD_UNINST_OK'),
            'HTML' => true
        ]);
    } catch (Exception $e) {
        CAdminMessage::ShowMessage([
            'TYPE' => 'ERROR',
            'MESSAGE' => $e->getMessage(),
            'HTML' => true
        ]);
    }
}
?>
<form action="<?= $APPLICATION->GetCurPage() ?>">
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="submit" name="" value="<?= Loc::getMessage('MOD_BACK') ?>">
</form>
