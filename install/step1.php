<?php
use Bitrix\Main\Application;
use Bitrix\Main\Localization\Loc;

// Check for session ID
if (!check_bitrix_sessid()) return;

// Get application instance
$application = Application::getInstance();
$context = $application->getContext();
$request = $context->getRequest();

// Load module messages
Loc::loadMessages(__FILE__);

if ($errorException = $APPLICATION->GetException()) {
    CAdminMessage::ShowMessage([
        'TYPE' => 'ERROR',
        'MESSAGE' => Loc::getMessage('MOD_INST_ERR'),
        'DETAILS' => $errorException->GetString(),
        'HTML' => true
    ]);
} else {
    CAdminMessage::ShowMessage([
        'TYPE' => 'OK',
        'MESSAGE' => Loc::getMessage('FOSREVIEWS_INSTALL_SUCCESS_TITLE'),
        'DETAILS' => Loc::getMessage('FOSREVIEWS_INSTALL_DESC'),
        'HTML' => true
    ]);
    // CAdminMessage::ShowMessage([
    //     'TYPE' => 'OK',
    //     'MESSAGE' => Loc::getMessage('FOSREVIEWS_TITLE_DEV'),
    //     'DETAILS' => Loc::getMessage('FOSREVIEWS_DESC_DEV'),
    //     'HTML' => true
    // ]);
}

// Add back button
?>
<form action="<?= $context->getServer()->getRequestUri() ?>">
    <?= bitrix_sessid_post() ?>
    <input type="hidden" name="lang" value="<?= LANGUAGE_ID ?>">
    <input type="submit" name="" value="<?= Loc::getMessage('MOD_BACK') ?>">
</form>