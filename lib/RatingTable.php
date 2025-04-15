<?php

namespace FOS\Reviews;

use Bitrix\Main\Entity;

class RatingTable extends Entity\DataManager
{
    /**
     * Returns the name of the database table for storing review ratings.
     * 
     * @return string The name of the database table for storing review ratings.
     *******************************************************************************************************************/
    public static function getTableName()
    {
        return 'fosreviews_rating';
    }

    /**
     * Returns the entity map definition for the rating table.
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
            new Entity\IntegerField('PRODUCT_ID', array(
                'required' => true,
            )),
            new Entity\IntegerField('RATING', array(
                'required' => true,
            )),
        );
    }

    /**
     * Add a new rating to the database
     * 
     * @param int $reviewId The ID of the review to which the rating belongs
     * @param int $productId The ID of the product being rated
     * @param int $rating The rating value (between 1 and 5)
     * @return bool True if the rating was added successfully, false otherwise
     * @throws \InvalidArgumentException if any of the input parameters are invalid
     *******************************************************************************************************************/
    public static function addRating($reviewId, $productId, $rating)
    {
        // Validate inputs
        if (!is_int($reviewId) || $reviewId <= 0) throw new \InvalidArgumentException('Review ID must be a positive integer');
        if (!is_int($productId) || $productId <= 0) $productId = 0;
        if (!is_numeric($rating) || $rating < 1 || $rating > 5) $rating = 5;

        $result = self::add(array(
            'REVIEW_ID' => $reviewId,
            'PRODUCT_ID' => $productId,
            'RATING' => $rating,
        ));

        return $result->isSuccess();
    }

    /**
     * Update the rating and product ID for a review.
     * 
     * @param int $reviewId The ID of the review to update.
     * @param int|float $rating The new rating to set for the review (must be a number between 1 and 5).
     * @param int $productId The new product ID to set for the review.
     * @throws \InvalidArgumentException if the review ID or product ID is not an integer or if the rating is not a number between 1 and 5.
     * @throws \Exception if an error occurs while updating the rating.
     * @return bool True if the update was successful, false otherwise.
     *******************************************************************************************************************/
    public static function updateRating($reviewId, $rating, $productId)
    {
        // Validate inputs
        if (!is_int($reviewId)) throw new \InvalidArgumentException('Review ID must be an integer');
        if (!is_int($productId)) $productId = 0;
        if (!is_numeric($rating) || $rating < 1 || $rating > 5) $rating = 5;

        // Update the rating
        $data = array();
        if (is_numeric($rating)) $data['RATING'] = $rating;
        if ($productId) $data['PRODUCT_ID'] = $productId;

        $result = self::update(array('REVIEW_ID' => $reviewId), $data);

        if (!$result->isSuccess()) throw new \Exception('Error updating rating');

        return true;
    }

    /**
     * Deletes a rating record from the database for the specified review ID.
     * @param int $reviewId The ID of the review to delete the rating for.
     * @return bool True if the rating was deleted successfully, false otherwise.
     * @throws \InvalidArgumentException If the review ID is not a positive integer.
     *******************************************************************************************************************/
    public static function deleteRating($reviewId)
    {
        // Validate inputs
        if (!is_int($reviewId) || $reviewId <= 0) throw new \InvalidArgumentException('Review ID must be an integer');

        $connection = \Bitrix\Main\Application::getConnection();
        $affectedRows = $connection->queryExecute(
            "DELETE FROM ".self::getTableName()." WHERE REVIEW_ID = {$reviewId}"
        );

        return $affectedRows !== false;
    }

    /**
     * Retrieves a single record from the database by review ID.
     * 
     * @param int $reviewId The ID of the review to retrieve.
     * @throws \InvalidArgumentException if $reviewId is not an integer.
     * @return array|null Returns an array containing the data of the retrieved record, or null if no record is found.
     *******************************************************************************************************************/
    public static function getByReviewId($reviewId)
    {
        // Validate inputs
        if (!is_int($reviewId)) throw new \InvalidArgumentException('Review ID must be an integer');
    
        $result = self::getList(array(
            'filter' => array('=REVIEW_ID' => $reviewId),
            'limit' => 1
        ));
        
        return $result->fetch();
    }

    /**
     * Retrieves the average rating and total number of ratings for a given product ID.
     *
     * @param int $productId The ID of the product.
     * @return array An associative array containing the average rating and total number of ratings.
     * @throws InvalidArgumentException If the product ID is not a positive integer.
     *******************************************************************************************************************/
    public static function getAverageRatingByProductId($productId)
    {
        // Validate input
        if ($productId <= 0) throw new \InvalidArgumentException('Product ID must be a positive integer');
    
        $tableName = self::getTableName();
        $query = "
            SELECT AVG(rr.RATING) AS avg_rating, COUNT(*) AS total_ratings
            FROM {$tableName} rr
            INNER JOIN fosreviews_review rv ON rr.REVIEW_ID = rv.ID
            WHERE rr.PRODUCT_ID = {$productId} AND rv.ACTIVE = 1
        ";
        $connection = \Bitrix\Main\Application::getConnection();
        $result = $connection->query($query);
        $row = $result->fetch();
        $avgRating = $row['avg_rating'];
        $totalRatings = $row['total_ratings'];
        return array('AVG_RATING' => $avgRating, 'TOTAL_RATINGS' => $totalRatings);
    }
}
