<?
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Bitrix\Main\Localization\Loc;
use \FOS\Reviews\ReviewTable;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

$moduleId = 'fosdev.fosreviews';
Loader::includeModule($moduleId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = (int) $USER->getID();
    $currentUri = $_SERVER['HTTP_REFERER'];
    $currentUri = explode('/', $currentUri);
    $lastcurrentUri = '';
    foreach($currentUri as $key=>$val) {
        if($key == 0 || $key == 1 || $key == 2) continue;
        $lastcurrentUri .= "/" . $val;
    }
    $charactersToRemove = ["=", ",", ";", " ", "\t", "\r", "\n", "\013", "\014"];
    $lastcurrentUri = str_replace($charactersToRemove, "", $lastcurrentUri);
    $elementId = (int) $_COOKIE['elementId'.$lastcurrentUri];
    
    $rating = (int) $_POST['rating'];
    $message = $_POST['message'];
    if(!$message) $message = " ";

    if($userId > 0 && $elementId > 0 && $rating > 0) {
        $data = array(
            "ACTIVE" => 1,
            "USER_ID" => $userId,
            "PRODUCT_ID" => $elementId,
            "PUBLICATION_DATE" => time(),
            "COMMENT" => $message,
            "RATING" => $rating,
            "PHOTO" => array(
                "DEL" => array(),
                "ADD" => array()
            ),
        );

        if(count($_FILES) > 0) {
            foreach($_FILES as $file) {
                if($file["name"]) {
                    $data["PHOTO"]["ADD"][] = (int) CFile::SaveFile(array(
                        "name" => $file["name"],
                        "size" => $file["size"],
                        "tmp_name" => $file["tmp_name"],
                        "type" => $file["type"],
                        "MODULE_ID" => $moduleId,
                    ), $moduleId);
                }
            }
        }
        
        $result = ReviewTable::addReview($data);
    }
}
if($result > 0) {?>
    <div class="fosrev-modal-mini__content__t"><?=Loc::getMessage("REVIEW_ADD_TITLE")?></div>
    <div class="fosrev-modal-mini__content__tx"><?=Loc::getMessage("REVIEW_ADD_DESC")?></div>
<?}?>