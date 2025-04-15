<?php

namespace FOS\Reviews;

use Bitrix\Main\Entity;

class LikeTable extends Entity\DataManager
{
    /**
     * Returns the name of the database table for storing review likes.
     * 
     * @return string The name of the database table for storing review likes.
     *******************************************************************************************************************/
    public static function getTableName()
    {
        return 'fosreviews_like';
    }

    /**
     * Returns the entity map definition for the likes table.
     * 
     * @return array
     *******************************************************************************************************************/
    public static function getMap()
    {
        return array(
            new Entity\IntegerField('ID', array(
                'autocomplete' => true,
            )),
            new Entity\IntegerField('USER_ID', array(
                'primary' => true,
                'required' => true,
            )),
            new Entity\IntegerField('REVIEW_ID', array(
                'primary' => true,
                'required' => true,
            )),
            new Entity\IntegerField('LIKED', array(
                'required' => true,
            )),
        );
    }
    /**
     * Adds a new like or dislike to the database for the given user and review.
     *
     * @param int $userId The ID of the user who liked/disliked the review.
     * @param int $reviewId The ID of the review that was liked/disliked.
     * @param int $liked Whether the review was liked (1) or disliked (0).
     * @return bool Whether the like was added successfully.
     * @throws \InvalidArgumentException If the user ID, review ID, or liked parameter is invalid.
     *******************************************************************************************************************/
    public static function addLike($userId, $reviewId, $liked)
    {
        // Validate inputs
        if (!is_int($userId) || $userId <= 0) throw new \InvalidArgumentException('User ID must be a positive integer');
        if (!is_int($reviewId) || $reviewId <= 0) throw new \InvalidArgumentException('Review ID must be a positive integer');
        if (!is_int($liked) || $liked < 0 || $liked > 1) throw new \InvalidArgumentException('Liked must be an integer 0 or 1');

        $result = self::add(array(
            'USER_ID' => $userId,
            'REVIEW_ID' => $reviewId,
            'LIKED' => $liked,
        ));

        return $result->isSuccess();
    }

    /**
     * Updates a like record for a specific user and review with a new value for the 'liked' field.
     * 
     * @param int $userId The user ID for the like record.
     * @param int $reviewId The review ID for the like record.
     * @param int $liked The new value for the 'liked' field (0 or 1).
     * @throws \InvalidArgumentException if the inputs are invalid.
     * @return bool Returns true if the update was successful, false otherwise.
     *******************************************************************************************************************/
    public static function updateLike($userId, $reviewId, $liked)
    {
        // Validate inputs
        if (!is_int($userId) || $userId <= 0) throw new \InvalidArgumentException('User ID must be a positive integer');
        if (!is_int($reviewId) || $reviewId <= 0) throw new \InvalidArgumentException('Review ID must be a positive integer');
        if (!is_int($liked) || $liked < 0 || $liked > 1) throw new \InvalidArgumentException('Liked must be an integer 0 or 1');

        $result = self::update(array(
            'USER_ID' => $userId,
            'REVIEW_ID' => $reviewId,
        ), array(
            'LIKED' => $liked,
        ));

        return $result->isSuccess();
    }

    /**
     * Deletes all like records associated with a specific review ID.
     * 
     * @param int $reviewId The ID of the review to delete likes for.
     * @return bool Returns true on success or false on failure.
     * @throws \InvalidArgumentException If review ID is not a positive integer.
     *******************************************************************************************************************/
    public static function deleteLikesByReviewId($reviewId)
    {
        // Validate input
        if (!is_int($reviewId) || $reviewId <= 0) throw new \InvalidArgumentException('Review ID must be a positive integer');

        $connection = \Bitrix\Main\Application::getConnection();
        $affectedRows = $connection->queryExecute(
            "DELETE FROM " . self::getTableName() . " WHERE REVIEW_ID = " . $reviewId
        );

        return $affectedRows !== false;
    }

    /**
     * Deletes a like record from the database for the given user and review IDs.
     * 
     * @param int $userId The ID of the user who liked the review.
     * @param int $reviewId The ID of the review being liked.
     * @throws InvalidArgumentException If $userId or $reviewId are not positive integers.
     * @return bool True if the record was successfully deleted, false otherwise.
     *******************************************************************************************************************/
    public static function deleteLike($userId, $reviewId)
    {
        // Validate inputs
        if (!is_int($userId) || $userId <= 0) throw new \InvalidArgumentException('User ID must be a positive integer');
        if (!is_int($reviewId) || $reviewId <= 0) throw new \InvalidArgumentException('Review ID must be a positive integer');
    
        $result = self::delete(array(
            'USER_ID' => $userId,
            'REVIEW_ID' => $reviewId,
        ));

        return $result->isSuccess();
    }

    /**
     * Get the number of likes and dislikes for a given review, and whether the given user has liked it.
     * 
     * @param int $reviewId The ID of the review to get likes for.
     * @param int|null $userId The ID of the user to check if they liked the review (optional).
     * @return array An array containing the number of likes, dislikes, and whether the user liked the review.
     * @throws InvalidArgumentException If the review ID is not a positive integer.
     *******************************************************************************************************************/
    public static function getLikesCount($reviewId, $userId = null)
    {
        // Validate input
        if (!is_int($reviewId) || $reviewId <= 0) throw new \InvalidArgumentException('Review ID must be a positive integer');

        $result = array(
            'LIKES' => 0,
            'DISLIKES' => 0,
            'LIKED' => null,
        );

        $res = self::getList(array(
            'filter' => array(
                '=REVIEW_ID' => $reviewId,
            ),
            'select' => array('LIKED', 'USER_ID'),
        ));

        while ($record = $res->fetch()) {
            if ($record['LIKED']) {
                $result['LIKES']++;
            } else {
                $result['DISLIKES']++;
            }
            if ($userId && $record['USER_ID'] == $userId) {
                $result['LIKED'] = $record['LIKED'];
            }
        }

        return $result;
    }

    /**
     * Toggle like for a review by a user. If the like already exists and has the same value, it is deleted.
     * If it doesn't exist, it is added. Otherwise, it is updated.
     *
     * @param int $userId The ID of the user who is liking the review.
     * @param int $reviewId The ID of the review being liked.
     * @param int $liked Whether the user likes (1) or dislikes (0) the review.
     * @throws \InvalidArgumentException If any of the inputs are invalid.
     * @return bool Whether the operation was successful.
     *******************************************************************************************************************/
    public static function toggleLike($userId, $reviewId, $liked)
    {
        // Validate inputs
        if (!is_int($userId) || $userId <= 0) throw new \InvalidArgumentException('User ID must be a positive integer');
        if (!is_int($reviewId) || $reviewId <= 0) throw new \InvalidArgumentException('Review ID must be a positive integer');
        if (!is_int($liked) || $liked < 0 || $liked > 1) throw new \InvalidArgumentException('Liked must be an integer 0 or 1');

        // Check if the like already exists for the review and user
        $existingRecord = self::getList(array(
            'filter' => array(
                '=USER_ID' => $userId,
                '=REVIEW_ID' => $reviewId,
            ),
            'select' => array('LIKED'),
        ))->fetch();

        // If it doesn't exist, add the like
        if (!$existingRecord) {
            return self::addLike($userId, $reviewId, $liked);
        }

        // If the existing like has the same value, delete it
        if ($existingRecord['LIKED'] == $liked) {
            return self::deleteLike($userId, $reviewId);
        }

        // Otherwise, update the like
        return self::updateLike($userId, $reviewId, $liked);
    }
}
