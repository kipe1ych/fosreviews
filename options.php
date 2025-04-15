<?
use Bitrix\Main\Config\Option;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

$module_id = "fosdev.fosreviews";
Loc::loadMessages($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
Loc::loadMessages(__FILE__);
Loader::includeModule($module_id);

$request = HttpApplication::getInstance()->getContext()->getRequest();
$aTabs = array(
    array(
        "DIV" => "edit1",
        "TAB" => Loc::getMessage("FOSREVIEWS_ACCESS_TAB"),
        "TITLE" => Loc::getMessage("FOSREVIEWS_ACCESS_TAB_TITLE"),
    ),
);

// Save
if ($request->isPost() && $request['Update'] && check_bitrix_sessid()) {
    foreach ($aTabs as $aTab) {
        foreach ($aTab['OPTIONS'] as $arOption) {
            if (!is_array($arOption) || $arOption['note']) continue;

            $optionName = $arOption['name'];
            $optionValue = $request->getPost($optionName);
            if ($arOption['type'] === 'checkbox') $optionValue = in_array($optionValue, $arOption['values']) ? $optionValue : '';
            Option::set($module_id, $optionName, $optionValue);
        }
    }
}

// CSRF Protection
echo bitrix_sessid_post();

$tabControl = new CAdminTabControl("tabControl", $aTabs);
$tabControl->Begin();
?>

<form method="post" action="<?=htmlspecialcharsbx($request->getRequestUri())?>">
    <?=bitrix_sessid_post()?>
    <?foreach($aTabs as $aTab):
        if($aTab['OPTIONS']):
            $tabControl->BeginNextTab();
            __AdmSettingsDrawList($module_id, $aTab['OPTIONS']);
        endif;
    endforeach;
    
    $tabControl->BeginNextTab();
    require_once($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/admin/group_rights.php");
    $tabControl->Buttons();?>

    <input type="submit" name="Update" class="adm-btn-save" value="<?=Loc::getMessage("FOSREVIEWS_SAVE_BTN")?>">
</form>

<?$tabControl->End();?>