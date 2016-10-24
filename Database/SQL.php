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
    public static $insInViewHistory         = "INSERT INTO view_history (user_id, photo_id)                        VALUES (:user_id, :photo_id)";
    public static $insPhotoWithoutCaption   = "INSERT INTO photos       (photo_tlgrm_id, auth_id, status)          VALUES (:photo_tlgrm_id, :auth_id, :status)";
    public static $insFile                  = "INSERT INTO files        (file_tlgrm_id, photo_id)                  VALUES (:file_tlgrm_id, :photo_id)";
    public static $insPhotoCoordinate       = "INSERT INTO coordinates  (address, coordinate, photo_id)            VALUES (:address, PointFromText(:coordinate), :photo_id)";
    public static $insNewUser               = "INSERT INTO users        (chat_id, user_name)                       VALUES (:chat_id, :user_name)";
    public static $insNewReport             = "INSERT INTO reports      (photo_id, user_id, subject)               VALUES (:photo_id, (SELECT user_id FROM users WHERE chat_id = :chat_id), :subject)
";

    
    #SELECT
    public static $selRandPhoto = "SELECT photo_tlgrm_id, photo_id, caption FROM photos       WHERE photo_id NOT IN (SELECT photo_id FROM view_history INNER JOIN users ON view_history.user_id = users.user_id WHERE chat_id = :chat_id)";
    
    public static $selGeoPhoto = 
       "set @mylat= :lat;
        set @mylon= :lng ;
        set @dist = :dist;
        
        set @lon1 = @mylon - @dist/abs(cos(radians(@mylat))*69);
        set @lon2 = @mylon + @dist/abs(cos(radians(@mylat))*69);
        set @lat1 = @mylat - (@dist/69);
        set @lat2 = @mylat + (@dist/69);
        
        SELECT x(coordinate), y(coordinate), coordinate, 6371 * 2 * ASIN(SQRT(
                                                                             POWER(SIN((@mylat - abs(x(coordinate))) * pi()/180 / 2),
                                                                                   2) + COS(@mylat * pi()/180 ) * COS(abs(x(coordinate)) *
                                                                                                                      pi()/180) * POWER(SIN((@mylon - y(coordinate)) *
                                                                                                                                            pi()/180 / 2), 2) )) as distance
        FROM coordinates
        
        WHERE
          x(coordinate) between @lat1 and @lat2
          AND
          y(coordinate) between @lon1 and @lon2
        
        HAVING distance < @dist
        ORDER BY distance";

    public static $selLike =
        "SELECT TRUE
            FROM view_history
              INNER JOIN users
                ON users.user_id = view_history.user_id
            WHERE photo_id = :photo_id AND chat_id = :chat_id AND `like` = 0";
    
    public static $selDislike =
        "SELECT TRUE
            FROM view_history
              INNER JOIN users
                ON users.user_id = view_history.user_id
            WHERE photo_id = :photo_id AND chat_id = :chat_id AND dislike = 0";

    public static $selInformationAboutThisPhoto =
       "SELECT
          photo_tlgrm_id,
          photos.photo_id,
          address,
          file_tlgrm_id,
          caption
        FROM photos
          LEFT JOIN files
            ON files.photo_id = photos.photo_id
          LEFT JOIN coordinates
            ON coordinates.photo_id = photos.photo_id
        WHERE photos.photo_id NOT IN (SELECT photo_id
                                      FROM users
                                        INNER JOIN view_history
                                          ON view_history.user_id = users.user_id
                                      WHERE chat_id = :chat_id)";

    public static $selInformationAboutLastPhoto =
        "
        SELECT
          view_history.photo_id,
          address,
          file_tlgrm_id
        FROM users
          LEFT JOIN view_history
            ON users.user_id = view_history.user_id
          LEFT JOIN files
            ON view_history.photo_id = files.photo_id
          LEFT JOIN coordinates
            ON view_history.photo_id = coordinates.photo_id
        WHERE chat_id = :chat_id
        ORDER BY view_id DESC
        LIMIT 1 ";

    public static $selInformationAboutPhoto =
        "
        SELECT
          address,
          file_tlgrm_id
        FROM users
          LEFT JOIN view_history
            ON users.user_id = view_history.user_id
          LEFT JOIN files
            ON view_history.photo_id = files.photo_id
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
    
    public static $selLastWatchedPhotoID        = "SELECT photo_id                          FROM view_history INNER JOIN users ON view_history.user_id = users.user_id WHERE chat_id = :chat_id ORDER BY view_id DESC LIMIT 1";
    public static $selCountUnwatchedPhoto       = "SELECT count(*)                          FROM photos       WHERE photo_id NOT IN (SELECT photo_id FROM view_history LEFT JOIN users ON view_history.user_id = users.user_id WHERE chat_id = :chat_id)";
    public static $selPhotoIDOnUploading        = "SELECT photo_id                          FROM photos       INNER JOIN users       ON auth_id = user_id     WHERE status = 0 AND chat_id = :chat_id limit 1";
    public static $selPhotoFileIDOnUploading    = "SELECT photo_tlgrm_id                    FROM photos       INNER JOIN users       ON auth_id = user_id     WHERE status = 0 AND chat_id = :chat_id limit 1";
    public static $selUserID                    = "SELECT user_id                           FROM users        WHERE chat_id  = :chat_id";
    public static $selUserDistance              = "SELECT distance                          FROM users        WHERE chat_id  = :chat_id";
   
    public static $selAddress = 
        "SELECT
          address,
          X(coordinate) AS lat,
          Y(coordinate) AS lng
        FROM coordinates
        WHERE photo_id = :photo_id
        LIMIT 1";
    
    public static $selFile                      = "SELECT file_tlgrm_id                     FROM files        WHERE photo_id = :photo_id LIMIT 1";
    public static $selOnModeration              = "SELECT COUNT(*)                          FROM photos       INNER JOIN users       ON auth_id = user_id     WHERE status = 1 and chat_id = :chat_id";
    public static $selOnPublic                  = "SELECT COUNT(*)                          FROM photos       INNER JOIN users       ON auth_id = user_id     WHERE status = 2 and chat_id = :chat_id";
    public static $selCheckBan                  = "SELECT TRUE                              FROM black_list   WHERE chat_id = :chat_id";
    public static $selCheckReport =
        "SELECT TRUE
            FROM reports
              INNER JOIN users
                ON users.user_id = reports.user_id
            WHERE chat_id = :chat_id AND photo_id = :photo_id AND subject = :subject";
    public static $selCheckInUpload             = "SELECT TRUE                              FROM photos       INNER JOIN users       ON auth_id = user_id     WHERE status = 0 AND chat_id = :chat_id LIMIT 1";
    public static $selCheckFile                 = "SELECT TRUE                              FROM photos       INNER JOIN users       ON auth_id = user_id INNER JOIN files       ON photos.photo_id = files.photo_id       WHERE photos.status = 0 AND chat_id = :chat_id limit 1";
    public static $selCheckCoordinate           = "SELECT TRUE                              FROM photos       INNER JOIN users       ON auth_id = user_id INNER JOIN coordinates ON photos.photo_id = coordinates.photo_id WHERE photos.status = 0 AND chat_id = :chat_id limit 1";
    public static $selCheckNewUser              = "SELECT NOT EXISTS                          
                                                  (SELECT *                                 FROM users      WHERE chat_id = :chat_id )";


    #UPDATE
    public static $updUserCoordinate = 
        "UPDATE users
         SET `coordinate` = :coordinate
         WHERE chat_id = :chat_id";

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

    public static $updToModeration = "UPDATE photos SET status        = status+1          WHERE auth_id = :auth_id AND status = 0  LIMIT 1";
    public static $updActivity     = "UPDATE users  SET last_activity = CURRENT_TIMESTAMP WHERE chat_id = :chat_id LIMIT 1";


    #DELETE
    public static $delFileOnUploading       = "DELETE FROM files       WHERE photo_id = :photo_id LIMIT 1";
    public static $delCoordinateOnUploading = "DELETE FROM coordinates WHERE photo_id = :photo_id LIMIT 1";
}