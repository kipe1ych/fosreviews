<?php

namespace FOS\Reviews;

use Bitrix\Main\Entity;

class PhotoTable extends Entity\DataManager
{
    /**
     * Returns the name of the database table associated with the ReviewPhoto entity.
     * 
     * @return string The name of the database table.
     *******************************************************************************************************************/
    public static function getTableName()
    {
        return 'fosreviews_photo';
    }

    /**
     * Returns the entity map definition for the photos table.
     * 
     * @return array
     *******************************************************************************************************************/
    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'autocomplete' => true,
            )),
            new Entity\IntegerField('REVIEW_ID', array(
                'primary' => true,
                'required' => true,
            )),
            new Entity\IntegerField('FILE_ID', array(
                'required' => true,
            )),
        );
    }

    /**
     * Adds photos to a review.
     * 
     * @param int $reviewId The ID of the review to which the photos will be added.
     * @param array $fileIds An array of file IDs that correspond to the photos.
     * @return bool Returns true if the photos were added successfully, false otherwise.
     *******************************************************************************************************************/
    public static function addPhotos($reviewId, $fileIds)
    {
        if (!is_int($reviewId)) throw new \InvalidArgumentException('ReviewId must be an integer');
        if (!is_array($fileIds) || empty($fileIds)) throw new \InvalidArgumentException('FileIds must be a non-empty array');
        foreach ($fileIds as $fileId) {
            if (!is_int($fileId)) throw new \InvalidArgumentException('The file IDs must be integers.');
        }

        $fields = array();
        foreach ($fileIds as $fileId) {
            $fields[] = array(
                'REVIEW_ID' => $reviewId,
                'FILE_ID' => $fileId
            );
        }

        $result = self::addMulti($fields);

        return $result->isSuccess();
    }

    /**
     * Deletes photos by the provided file IDs.
     * 
     * @param array $fileIds The IDs of the files to delete photos for.
     * @return bool True if the photos were deleted successfully, false otherwise.
     *******************************************************************************************************************/
    public static function deletePhotosByFileIds($fileIds)
    {
        if (!is_array($fileIds)) throw new \InvalidArgumentException('The file IDs must be provided as an array.');
        foreach ($fileIds as $fileId) {
            if (!is_int($fileId)) throw new \InvalidArgumentException('The file IDs must be integers.');
        }

        $ids = array_map('intval', $fileIds);
        if (empty($ids)) {
            return true; // Nothing to delete
        }

        $connection = \Bitrix\Main\Application::getConnection();
        $affectedRows = $connection->queryExecute(
            "DELETE FROM ".self::getTableName()." WHERE FILE_ID IN (".join(',', $ids).")"
        );

        return $affectedRows !== false;
    }
    /**
     * Deletes all photos associated with a given review ID.
     *
     * @param int $reviewId The ID of the review to delete photos for.
     * @return bool Whether the delete operation was successful.
     *******************************************************************************************************************/
    public static function deletePhotosByReviewId($reviewId)
    {
        if (!is_int($reviewId)) throw new \InvalidArgumentException('ReviewId must be an integer');

        $connection = \Bitrix\Main\Application::getConnection();
        $affectedRows = $connection->queryExecute(
            "DELETE FROM " . self::getTableName() . " WHERE REVIEW_ID = " . (int) $reviewId
        );

        return $affectedRows !== false;
    }

    /**
     * Returns an array of file IDs associated with a review.
     * 
     * @param int $reviewId The ID of the review.
     * @return array An array of file IDs associated with the review.
     *******************************************************************************************************************/
    public static function getPhotosByReviewId($reviewId)
    {
        if (!is_int($reviewId)) throw new \InvalidArgumentException('ReviewId must be an integer');

        $result = array();
        $res = self::getList(array(
            'filter' => array('=REVIEW_ID' => $reviewId),
            'select' => array('FILE_ID')
        ));
        $rows = $res->fetchAll();

        foreach ($rows as $row) {
            $result[] = $row['FILE_ID'];
        }

        return $result;
    }
}
