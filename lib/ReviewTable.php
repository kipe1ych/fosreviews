<?php

namespace FOS\Reviews;

use Bitrix\Main\Entity;
use Bitrix\Main\Localization\Loc;

class ReviewTable extends Entity\DataManager
{
    /**
     * Returns the name of the database table for storing review ratings.
     * 
     * @return string The name of the database table for storing review ratings.
     *******************************************************************************************************************/
    public static function getTableName()
    {
        return 'fosreviews_review';
    }

    /**
     * Returns the map of the table columns for the reviews in the database.
     *
     * @return array The map of the table columns.
     *******************************************************************************************************************/
    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'primary' => true,
                'autocomplete' => true,
            )),
            new Entity\IntegerField('ACTIVE', array(
                'default_value' => 0,
            )),
            new Entity\IntegerField('USER_ID', array(
                'required' => true,
            )),
            new Entity\IntegerField('PRODUCT_ID', array(
                'required' => true,
            )),
            new Entity\IntegerField('PUBLICATION_DATE', array(
                'required' => true,
            )),
            new Entity\TextField('COMMENT', array(
                'required' => true,
            )),
        );
    }
    
    /**
     * Adds a new review to the database.
     *
     * @param bool $active The value indicating whether the review is active or not.
     * @param int $userId The ID of the user who wrote the review.
     * @param int $productId The ID of the product for which the review is written.
     * @param string $comment The comment text of the review.
     * @param array $photoIds The array of photo IDs associated with the review.
     * @param int|null $rating The rating value of the review.
     * @return int The ID of the newly added review.
     * @throws \Exception If the review could not be added.
     */
    public static function addReview($data)
    {
        if (empty($data['USER_ID'])) {
            global $APPLICATION;
            $APPLICATION->ThrowException(Loc::getMessage('MISS_USERID'));
            return false;
        }
        if (empty($data['PRODUCT_ID'])) {
            global $APPLICATION;
            $APPLICATION->ThrowException(Loc::getMessage('MISS_PRODUCTID'));
            return false;
        }
        if (empty($data['PUBLICATION_DATE'])) {
            global $APPLICATION;
            $APPLICATION->ThrowException(Loc::getMessage('MISS_PUBLICATION'));
            return false;
        }
        if (empty($data['COMMENT'])) {
            global $APPLICATION;
            $APPLICATION->ThrowException(Loc::getMessage('MISS_COMMENT'));
            return false;
        }

        $result = self::add(array(
            "ACTIVE" => $data["ACTIVE"],
            "USER_ID" => $data["USER_ID"],
            "PRODUCT_ID" => $data["PRODUCT_ID"],
            "PUBLICATION_DATE" => $data["PUBLICATION_DATE"],
            "COMMENT" => $data["COMMENT"],
        ));

        if ($result->isSuccess()) {
            $reviewId = $result->getId();

            // Add rating
            if ($data["RATING"] !== null) {
                RatingTable::addRating($reviewId, $data["PRODUCT_ID"], $data["RATING"]);
            }

            // Add photos
            if(count($data["PHOTO"]["ADD"])) {
                PhotoTable::addPhotos($reviewId, $data["PHOTO"]["ADD"]);
            }

            return $reviewId;
        } else {
            throw new \Exception('Error adding review: ' . implode(', ', $result->getErrorMessages()));
        }
    }

    /**
     * Updates an existing review in the database.
     *
     * @param int $id The ID of the review to be updated.
     * @param bool $active The value indicating whether the review is active or not.
     * @param string $publicationDate The publication date of the review.
     * @param string $comment The comment text of the review.
     * @param int|null $rating The rating value of the review.
     * @return bool The result of the update operation.
     * @throws \Exception If the review could not be updated.
     */
    public static function updateReview($id, $data)
    {
        // Validate inputs
        if (!is_int($id) || $id <= 0) throw new \InvalidArgumentException('Invalid review ID: ' . $id);
        if (empty($data['USER_ID'])) {
            global $APPLICATION;
            $APPLICATION->ThrowException(Loc::getMessage('MISS_USERID'));
            return false;
        }
        if (empty($data['PRODUCT_ID'])) {
            global $APPLICATION;
            $APPLICATION->ThrowException(Loc::getMessage('MISS_PRODUCTID'));
            return false;
        }
        if (empty($data['PUBLICATION_DATE'])) {
            global $APPLICATION;
            $APPLICATION->ThrowException(Loc::getMessage('MISS_PUBLICATION'));
            return false;
        }
        if (empty($data['COMMENT'])) {
            global $APPLICATION;
            $APPLICATION->ThrowException(Loc::getMessage('MISS_COMMENT'));
            return false;
        }

        $fields = array(
            "ACTIVE" => $data["ACTIVE"],
            "USER_ID" => $data["USER_ID"],
            "PRODUCT_ID" => $data["PRODUCT_ID"],
            "PUBLICATION_DATE" => $data["PUBLICATION_DATE"],
            "COMMENT" => $data["COMMENT"],
        );

        $result = self::update($id, $fields);

        if ($result->isSuccess()) {
            // Update rating
            if ($data["RATING"] !== null) {
                RatingTable::updateRating($id, $data["RATING"], $data["PRODUCT_ID"]);
            }
            // Update photos
            if(count($data["PHOTO"]["DEL"])) {
                PhotoTable::deletePhotosByFileIds($data["PHOTO"]["DEL"]);
            }
            if(count($data["PHOTO"]["ADD"])) {
                PhotoTable::addPhotos($id, $data["PHOTO"]["ADD"]);
            }

            return true;
        } else {
            throw new \Exception('Error updating review: ' . implode(', ', $result->getErrorMessages()));
        }
    }

    /**
     * Deletes a review and its associated data, including likes, photos, and rating.
     *
     * @param array $ids The ID of the review to be deleted.
     * @return bool Returns true if the deletion is successful, otherwise false.
     */
    public static function deleteReview($ids)
    {
        global $DB;

        $reviewTable = self::getTableName();
        $likesTable = LikeTable::getTableName();
        $photosTable = PhotoTable::getTableName();
        $ratingsTable = RatingTable::getTableName();

        $ids = array_map('intval', $ids);
        $idsString = implode(',', $ids);

        $sql = "DELETE r, l, p, ra
                FROM $reviewTable r
                LEFT JOIN $likesTable l ON r.ID = l.REVIEW_ID
                LEFT JOIN $photosTable p ON r.ID = p.REVIEW_ID
                LEFT JOIN $ratingsTable ra ON r.ID = ra.REVIEW_ID
                WHERE r.ID IN ($idsString)";

        $result = $DB->Query($sql);

        return $result->result;
    }

    /**
     * Retrieves an array of all reviews associated with a given product ID
     *
     * @param int $productId The ID of the product to retrieve reviews for
     * @return array An array of reviews, with associated photos, likes, and ratings data
     */
    public static function getReviewsByProductId($productId)
    {
        $result = self::getList(array(
            'select' => array('*'),
            'filter' => array('=PRODUCT_ID' => $productId),
            'order' => array('ID' => 'DESC'),
        ));

        $reviews = array();
        while ($row = $result->fetch()) {
            $row['PHOTOS'] = PhotoTable::getList(array(
                'select' => array('*'),
                'filter' => array('=REVIEW_ID' => $row['ID']),
            ))->fetchAll();
            // $row['LIKES'] = LikeTable::getLikesCount($row['ID']);
            // $row['RATING'] = RatingTable::getByReviewId($row['ID']);
            $reviews[] = $row;
        }

        return $reviews;
    }

    /**
     * Whether the item was purchased
     *
     * @param int Id user
     * @param int Id product
     * @return bool Returns true if item was purchased, otherwise false.
     */
    public static function wasPurchased($user, $product)
    {
        global $DB;

        $sql = "SELECT * FROM b_sale_order 
                WHERE USER_ID = '$user' 
                AND EXISTS (SELECT * FROM b_sale_basket 
                    WHERE b_sale_basket.ORDER_ID = b_sale_order.ID 
                    AND b_sale_basket.PRODUCT_ID = '$product');";

        $result = $DB->Query($sql);
        
        $exist = 0;
        while ($row = $result->Fetch()) {
            $exist = $row['ID'];
        }

        return $exist;
    }
    /**
     * Deletes all reviews for a given product ID
     *
     * @param int $productId The ID of the product whose reviews should be deleted
     * @return bool Returns true if all reviews were successfully deleted, false otherwise
     */
    public static function deleteAllReviewsByProductId($productId)
    {
        $reviews = self::getList(array(
            "select" => array("ID"),
            "filter" => array("PRODUCT_ID" => $productId)
        ));
        
        foreach ($reviews as $review) {
            LikeTable::deleteLikesByReviewId($review["ID"]);
            PhotoTable::deletePhotosByReviewId($review["ID"]);
            RatingTable::deleteRating($review["ID"]);
        }
        
        $result = self::deleteList(array('filter' => array('=PRODUCT_ID' => $productId)));
        return $result->isSuccess();
    }

    /**
     * Deletes all reviews written by a given user
     *
     * @param int $userId The ID of the user whose reviews are to be deleted
     * @return bool Returns true if all reviews were successfully deleted, false otherwise
     */
    public static function deleteAllReviewsByUserId($userId)
    {
        $reviews = self::getList(array(
            "select" => array("ID"),
            "filter" => array("USER_ID" => $userId)
        ));
        
        foreach ($reviews as $review) {
            LikeTable::deleteLikesByReviewId($review["ID"]);
            PhotoTable::deletePhotosByReviewId($review["ID"]);
            RatingTable::deleteRating($review["ID"]);
        }
        
        $result = self::deleteList(array('filter' => array('=USER_ID' => $userId)));
        return $result->isSuccess();
    }
    
    /**
     * Retrieves a review with the given ID and adds its associated photos, rating, and like data.
     *
     * @param int $id The ID of the review to retrieve.
     * @return array|null Returns an array containing the review data, or null if the review doesn't exist.
     */
    public static function getReviewById($id)
    {
        $review = self::getList(array(
            'select' => array('*'),
            'filter' => array('=ID' => $id),
        ));

        if (!$review) {
            return null;
        }

        // Get photos associated with the review
        $photos = PhotoTable::getList(array(
            'select' => array('*'),
            'filter' => array('=REVIEW_ID' => $id),
        ))->fetchAll();

        // Get average rating and total ratings for the review
        $ratingData = RatingTable::getByReviewId($id);

        // Get number of likes and dislikes for the review
        $likeData = LikeTable::getLikesCount($id);
        $numLikes = $likeData['LIKES'];
        $numDislikes = $likeData['DISLIKES'];

        // Add photos, rating, and like data to the review
        $review['PHOTOS'] = $photos;
        $review['RATING'] = $ratingData['RATING'];
        $review['NUM_LIKES'] = $numLikes;
        $review['NUM_DISLIKES'] = $numDislikes;
    
        return $review;
    }

    /**
     * Retrieve all reviews written by a specific user.
     *
     * @param int $userId The ID of the user whose reviews to retrieve.
     * @return array An array of review data, including photos, likes, and ratings.
     */
    public static function getReviewsByUserId($userId)
    {
        $result = self::getList(array(
            'select' => array('*'),
            'filter' => array('=USER_ID' => $userId),
        ));

        $reviews = array();
        while ($row = $result->fetch()) {
            $row['PHOTOS'] = PhotoTable::getList(array(
                'select' => array('*'),
                'filter' => array('=REVIEW_ID' => $row['ID']),
            ))->fetchAll();
            $row['LIKES'] = LikeTable::getLikesCount($row['ID']);
            $row['RATING'] = RatingTable::getByReviewId($row['ID']);
            $reviews[] = $row;
        }

        return $reviews;
    }
}
