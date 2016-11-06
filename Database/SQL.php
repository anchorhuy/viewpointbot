<?php

/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 1:45
 */

namespace Database;

class SQL
{
    #INSERT
    public static $insPhoto                 = "INSERT INTO photos       (photo_tlgrm_id, auth_id, status, caption) VALUES (:photo_tlgrm_id, :auth_id, :status, :caption)";
    public static $insPay                   = "INSERT INTO payment (user_id, `status`, to_pay) VALUES ((SELECT user_id
                                                         FROM users
                                                         WHERE chat_id = :chat_id
                                                         LIMIT 1), 0, (SELECT pay
                                                                       FROM configuration
                                                                       LIMIT 1))";
    public static $insInViewHistory         = "INSERT INTO view_history (user_id, photo_id)                        VALUES (:user_id, :photo_id)";
    public static $insPhotoWithoutCaption   = "INSERT INTO photos       (photo_tlgrm_id, auth_id, status)          VALUES (:photo_tlgrm_id, :auth_id, :status)";
    public static $insPhotoCoordinate       = "INSERT INTO coordinates  (address, coordinate, photo_id)            VALUES (:address, PointFromText(:coordinate), :photo_id)";
    public static $insNewUser               = "INSERT INTO users        (chat_id, user_name)                       VALUES (:chat_id, :user_name)";
    public static $insNewReport             = "INSERT INTO reports      (photo_id, user_id, subject)               VALUES (:photo_id, (SELECT user_id FROM users WHERE chat_id = :chat_id), :subject)
";

    
    #SELECT
    public static $selGeoPhoto = 
       "SELECT
          photos.photo_id,
          x(coordinate) as address,
          photo_tlgrm_id as photo,
          caption,
          6371 * 2 * ASIN(SQRT(
                              POWER(SIN((:lat - abs(x(coordinate))) * pi() / 180 / 2),
                                    2) + COS(:lat * pi() / 180) * COS(abs(x(coordinate)) *
                                                                      pi() / 180) * POWER(SIN((:lng - y(coordinate)) *
                                                                                              pi() / 180 / 2), 2))) AS distance
        FROM photos
          LEFT JOIN coordinates
            ON photos.photo_id = coordinates.photo_id
        
        WHERE
          x(coordinate) BETWEEN (:lat - (:dist / 69)) AND (:lat + (:dist / 69))
          AND
          y(coordinate) BETWEEN (:lng - :dist / abs(cos(radians(:lat)) * 69)) AND (:lng + :dist / abs(cos(radians(:lat)) * 69))
        
        HAVING distance < :dist
        ORDER BY distance ";
    
    public static $selGeoSight = 
       "SELECT
          photos.photo_id,
          x(coordinate) as address,
          photo_tlgrm_id as photo,
          6371 * 2 * ASIN(SQRT(
                              POWER(SIN((:lat - abs(x(coordinate))) * pi() / 180 / 2),
                                    2) + COS(:lat * pi() / 180) * COS(abs(x(coordinate)) *
                                                                      pi() / 180) * POWER(SIN((:lng - y(coordinate)) *
                                                                                              pi() / 180 / 2), 2))) AS distance
        FROM photos
          LEFT JOIN coordinates
            ON photos.photo_id = coordinates.photo_id
        
        WHERE
          x(coordinate) BETWEEN (:lat - (:dist / 69)) AND (:lat + (:dist / 69))
          AND
          y(coordinate) BETWEEN (:lng - :dist / abs(cos(radians(:lat)) * 69)) AND (:lng + :dist / abs(cos(radians(:lat)) * 69))
          AND 
          photos.sight = 1
        
        HAVING distance < :dist
        ORDER BY distance ";

    public static $selLike =
        "SELECT TRUE
            FROM view_history
              INNER JOIN users
                ON users.user_id = view_history.user_id
            WHERE photo_id = :photo_id AND chat_id = :chat_id AND `like` = 1";
    
    public static $selDislike =
        "SELECT TRUE
            FROM view_history
              INNER JOIN users
                ON users.user_id = view_history.user_id
            WHERE photo_id = :photo_id AND chat_id = :chat_id AND dislike = 0";
    
    public static $selAdminsChatID =
       "SELECT chat_id
        FROM admins
          INNER JOIN users
            ON admins.user_id = users.user_id
        WHERE notification = 1";

    public static $selRandomPhoto =
       "SELECT
          photo_tlgrm_id,
          photos.photo_id,
          x(coordinates.coordinate) as address,
          caption
        FROM photos
          LEFT JOIN coordinates
            ON coordinates.photo_id = photos.photo_id
        WHERE photos.photo_id NOT IN (SELECT photo_id
                                      FROM users
                                        INNER JOIN view_history
                                          ON view_history.user_id = users.user_id
                                      WHERE chat_id = :chat_id)";
    
    public static $selInformationAboutLastPhoto =
        "SELECT
          view_history.photo_id,
          x(coordinates.coordinate) as address
        FROM users
          LEFT JOIN view_history
            ON users.user_id = view_history.user_id
          LEFT JOIN coordinates
            ON view_history.photo_id = coordinates.photo_id
        WHERE chat_id = :chat_id
        ORDER BY view_id DESC
        LIMIT 1 ";

    public static $selLastViews = 
       "SELECT last_views
        FROM photos
        WHERE photo_id = :photo_id 
        LIMIT 1";
    
    public static $selViewIDStartingPoint = 
       "SELECT
          view_id as begin_view_id
        FROM view_history
        WHERE photo_id = :photo_id
        ORDER BY view_id DESC ";
    
    public static $selViewIDEndingPoint = 
       "SELECT
          max(view_id) AS end_view_id
        FROM view_history
        WHERE photo_id = :photo_id";
    
    public static $selLastLikes =
       "SELECT count(*)
        FROM view_history
        WHERE view_id BETWEEN :start_point AND :end_point AND photo_id = :photo_id AND `like` = 1";

    public static $selViewsToReset =
       "SELECT
          reset_views
        FROM configuration";
    
    public static $selLikesToPay =
       "SELECT
          likes_to_pay
        FROM configuration";

    public static $selMoneyToPay =
       "SELECT
          pay
        FROM configuration";
    
    public static $selInformationAboutPhoto =
        "SELECT
          address
        FROM users
          LEFT JOIN view_history
            ON users.user_id = view_history.user_id
          LEFT JOIN coordinates
            ON view_history.photo_id = coordinates.photo_id
        WHERE chat_id = :chat_id AND view_history.photo_id = :photo_id
        LIMIT 1";

    public static $selIsItLastPhoto = 
        "
        SELECT
          view_id   AS max_id,
          (SELECT view_id
           FROM users
             INNER JOIN view_history
               ON users.user_id =
                  view_history.user_id
           WHERE chat_id = :chat_id AND photo_id = :photo_id
           LIMIT 1) AS touch_id
        FROM users
          INNER JOIN view_history
            ON users.user_id = view_history.user_id
        WHERE chat_id = :chat_id
        ORDER BY view_id DESC
        LIMIT 1";

    public static $selUserGeo =
        "SELECT
          x(coordinate) `lat`,
          y(coordinate) `lng`,
          distance `dist`
        FROM users
        WHERE chat_id = :chat_id
        LIMIT 1";
    
    public static $selUserSettings =
        "SELECT
         only_sights
        FROM users
        WHERE chat_id = :chat_id
        LIMIT 1";
    
    public static $selCountUnwatchedPhoto       =
        "SELECT count(*)
        FROM photos
        WHERE photo_id NOT IN (SELECT photo_id
                               FROM view_history
                                 LEFT JOIN users
                                   ON view_history.user_id = users.user_id
                               WHERE chat_id = :chat_id)";
        
    public static $selPhotoIDOnUploading = 
       "SELECT photo_id
        FROM photos
          INNER JOIN users
            ON auth_id = user_id
        WHERE status = 0 AND chat_id = :chat_id
        LIMIT 1";
    
    public static $selPhotoFileIDOnUploading = 
       "SELECT photo_tlgrm_id
        FROM photos
          INNER JOIN users
            ON auth_id = user_id
        WHERE status = 0 AND chat_id = :chat_id
        LIMIT 1";
    
    public static $selUserID = 
       "SELECT user_id
        FROM users
        WHERE chat_id = :chat_id";
    
    public static $selUserDistance =
       "SELECT distance
        FROM users
        WHERE chat_id = :chat_id";
   
    public static $selAddress = 
        "SELECT
          address,
          X(coordinate) AS lat,
          Y(coordinate) AS lng
        FROM coordinates
        WHERE photo_id = :photo_id
        LIMIT 1";
    
    public static $selCheckBan = 
       "SELECT TRUE
        FROM black_list
        WHERE chat_id = :chat_id";
   
    public static $selCheckReport =
        "SELECT TRUE
            FROM reports
              INNER JOIN users
                ON users.user_id = reports.user_id
            WHERE chat_id = :chat_id AND photo_id = :photo_id AND subject = :subject";
    
    public static $selCheckInUpload             = "SELECT TRUE                              FROM photos       INNER JOIN users       ON auth_id = user_id     WHERE status = 0 AND chat_id = :chat_id LIMIT 1";
    
    public static $selCheckPhone                = 
        "SELECT TRUE
        FROM users
        WHERE chat_id = :chat_id AND phone IS NOT NULL";
    
    public static $selCheckCoordinate            = "SELECT TRUE                              FROM photos       INNER JOIN users       ON auth_id = user_id INNER JOIN coordinates ON photos.photo_id = coordinates.photo_id WHERE photos.status = 0 AND chat_id = :chat_id limit 1";
    
    public static $selCoordinatePhotoOnUploading = "SELECT address
        FROM photos
          INNER JOIN users
            ON auth_id = user_id
          INNER JOIN coordinates
            ON photos.photo_id = coordinates.photo_id
        WHERE photos.status = 0 AND chat_id = :chat_id
        LIMIT 1";
    
    public static $selCheckAlreadyViewLine = 
       "SELECT TRUE
        FROM view_history
        WHERE photo_id = :photo_id AND user_id = :user_id";
    
    public static $selCheckNewUser              = "SELECT NOT EXISTS                          
                                                  (SELECT *                                 FROM users      WHERE chat_id = :chat_id )";


    #UPDATE
    public static $updUserCoordinate = 
        "UPDATE users
         SET `coordinate` = PointFromText(:coordinate)
         WHERE chat_id = :chat_id";
    public static $updUserDistance = 
       "UPDATE users
        SET distance = :distance
        WHERE chat_id = :chat_id";
    
    public static $updPhotoCoordinate = 
       "UPDATE coordinates
          INNER JOIN photos
            ON coordinates.photo_id = photos.photo_id
          INNER JOIN users
            ON photos.auth_id = users.user_id
        SET coordinates.coordinate = PointFromText(:coordinate),
            address    = :address
        WHERE chat_id = :chat_id AND status = 0";

    public static $updLike =
       "UPDATE view_history
          INNER JOIN users
            ON users.user_id = view_history.user_id
        SET `like` = 1
        WHERE chat_id = :chat_id AND photo_id = :photo_id AND `like` = 0";    
    
    public static $updDislike =
       "UPDATE view_history
          INNER JOIN users
            ON users.user_id = view_history.user_id
        SET `dislike` = 1
        WHERE chat_id = :chat_id AND photo_id = :photo_id AND `dislike` = 0";
    
    public static $updNewView =
       "UPDATE photos
        SET all_views = all_views + 1, last_views = last_views + 1
        WHERE photo_id = :photo_id
        LIMIT 1";

    public static $updResetLastViews =
       "UPDATE photos
        SET all_views = all_views + 1, last_views = last_views + 1
        WHERE photo_id = :photo_id AND status = 0
        LIMIT 1";
    
    public static $updPhotoCaption =
       "UPDATE photos
          INNER JOIN users
            ON user_id = auth_id
        SET caption = :caption
        WHERE chat_id = :chat_id AND status = 0 AND caption IS NULL";
    
    public static $updSetSightMode =
       "UPDATE users
        SET only_sights = 1
        WHERE chat_id = :chat_id";
    
    public static $updUnsetSightMode =
       "UPDATE users
        SET only_sights = 0
        WHERE chat_id = :chat_id";

    public static $updToModeration = "UPDATE photos SET status        = status+1          WHERE auth_id = :auth_id AND status = 0 ";
    public static $updActivity     = "UPDATE users  SET last_activity = CURRENT_TIMESTAMP WHERE chat_id = :chat_id LIMIT 1";


    #DELETE
    public static $delCoordinateOnUploading = "DELETE FROM coordinates WHERE photo_id = :photo_id LIMIT 1";
}