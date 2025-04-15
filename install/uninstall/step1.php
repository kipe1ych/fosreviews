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

// Execute the uninstallation script
if ($_POST['step'] < 2) {
    // Nothing to do
} else {
    $APPLICATION->IncludeAdminFile(Loc::getMessage('FOSREVIEWS_UNINSTALL_TITLE'), __DIR__ . '/step2.php');
}

CAdminMessage::ShowMessage([
    'TYPE' => 'ERROR',
    'MESSAGE' => Loc::getMessage('FOSREVIEWS_UNINSTALL_DESC'),
    'HTML' => true
]);

// Render the uninstall confirmation form
?>
<div style="display:flex; align-items:center;">
    <form action="<?= $APPLICATION->GetCurPage() ?>" method="POST">
        <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
        <input type="hidden" name="id" value="<?=$module_id?>">
        <input type="hidden" name="uninstall" value="Y">
        <input type="hidden" name="step" value="2">
        <?= bitrix_sessid_post() ?>
        <input type="submit" name="uninstall_confirm" value="<?= Loc::getMessage('MOD_UNINST_DEL') ?>">
    </form>

    <form action="<?= $APPLICATION->GetCurPage() ?>">
        <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
        <input type="submit" name="uninstall_cancel" value="<?= Loc::getMessage('MOD_BACK') ?>">
    </form>
</div>