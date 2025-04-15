<?php
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;
use Bitrix\Main\AccessDeniedException;

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
// Check user's permissions
if ($APPLICATION->GetGroupRight($module_id) < "W" || !$USER->IsAdmin()) throw new AccessDeniedException("Access Denied");
// Check for POST request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') die();

global $errors;

if (!$errors) {
    $APPLICATION->IncludeAdminFile(Loc::getMessage('FOSREVIEWS_UNINSTALL_TITLE'), __DIR__ . '/step3.php');
} else {
    foreach ($errors as $error) {
        CAdminMessage::ShowMessage([
            'TYPE' => 'ERROR',
            'MESSAGE' => $error,
            'HTML' => true
        ]);
    }
}
