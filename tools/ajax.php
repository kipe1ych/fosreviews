<?
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_before.php");

$moduleId = 'fosdev.fosreviews';
Loader::includeModule($moduleId);

// Check permissions
if($APPLICATION->GetGroupRight($module_id) >= "W") {
    if ($_REQUEST['action'] == 'deleteReview') {
        $reviewIds = (array) $_REQUEST['review_ids'];
        
        // Validate input parameters
        if (!is_array($reviewIds) || count($reviewIds) === 0 || !array_filter($reviewIds, 'is_numeric')) {
            echo Json::encode(array('success' => false, 'error' => 'Invalid input parameters'));
            die();
        }

        try {
            \FOS\Reviews\ReviewTable::deleteReview($reviewIds);
            echo Json::encode(array('success' => true));
        } catch (Exception $e) {
            echo Json::encode(array('success' => false, 'error' => $e->getMessage()));
        }

        die();
    }
} else {
    echo Json::encode(array('success' => false, 'error' => "Access Denied"));
    die();
}