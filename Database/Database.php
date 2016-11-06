<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 12.10.2016
 * Time: 2:08
 */

namespace Database;

use Input\Data;

class Database
{
    private $pdo;

    public function __construct()
    {
        try
        {
            $pdo = new \PDO("mysql:host=" . DB_HOST . ";dbname=". DB_NAME, DB_USER, DB_PASSWORD);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->exec('SET NAMES "utf8"');
        }
        catch(\PDOException $e)
        {
            echo 'Ошибка!' . $e->getMessage();
            exit();
        }
        $this->pdo = $pdo;

    }

    public function checkNewUser()
    {
        $values = ['chat_id' => Data::getChatID()];
        return $this->select(SQL::$selCheckNewUser, $values);
    }
    public function checkBlackList()
    {
        $values = ['chat_id' => Data::getChatID()];
        return $this->select(SQL::$selCheckBan, $values);
    }
    public function checkLimit()
    {
        $values       = ['chat_id' => Data::getChatID()];
        $onModeration = $this->select(SQL::$selOnModeration, $values);
        $limit        = $this->select(SQL::$selLimitOnModeration);
        
        return ($onModeration >= $limit) ? 1 : 0;
    }
    public function checkAdmin()
    {
//        if ( Data::getChatID() == ADMIN_CHAT_ID ){
//            return 1;
//        }
//        else {
//            return 0;
//        }
    }
    public function checkTrustee()
    {
        $values = ['chat_id' => Data::getChatID()];
        if( $this->select(SQL::$selOnPublic, $values) >= UPLOADS_TO_TRUSTEE) {
            return 1;
        }
        else {
            return 0;
        }
    }
    public function checkUploading()
    {
        $values = ['chat_id' => Data::getChatID()];
        return $this->select(SQL::$selCheckInUpload, $values);
    }
    public function checkIssetLocation()
    {
        $values = ['chat_id' => Data::getChatID()];
        return $this->select(SQL::$selCheckCoordinate, $values);
    }
    public function checkIssetPhone()
    {
        $values = ['chat_id' => Data::getChatID()];
        return $this->select(SQL::$selCheckPhone, $values);
    }
    public function checkAlreadyLike($photo_id)
    {
        $values['chat_id']  = Data::getChatID();
        $values['photo_id'] = $photo_id;
        return $this->select(SQL::$selLike, $values);
    }
    public function checkAlreadyDislike($photo_id)
    {
        $values['chat_id']  = Data::getChatID();
        $values['photo_id'] = $photo_id;
        return $this->select(SQL::$selDislike, $values);
    }
    public function checkAlreadyReport($photo_id, $subject)
    {
        $values['subject']   = $subject;
        $values['chat_id']  = Data::getChatID();
        $values['photo_id'] = $photo_id;
        return $this->select(SQL::$selCheckReport, $values);
    }
    public function checkViews($photo_id)
    {
        $values['photo_id'] = $photo_id;
        $last_views     = $this->select(SQL::$selLastViews, $values);
        $views_to_reset = $this->select(SQL::$selViewsToReset);
        
        if ($last_views > $views_to_reset) {
            $this->resetLastViews($photo_id);
        }
    }
    public function checkLikesToPay($photo_id)
    {
        $values['photo_id'] = $photo_id;
        $last_views   = $this->select(SQL::$selLastViews, $values);
        $likes_to_pay = $this->select(SQL::$selLikesToPay);
        
        if ($last_views < $likes_to_pay)
            $result['last_views'] = '1';

        $last_likes = $this->getLastLikes($photo_id, $last_views);

        if ($last_likes < $likes_to_pay)
            $result['last_likes'] = '1';
        
        $this->resetLastViews($photo_id);
        $this->createNewPay();

        $result = [
            'views' => $last_views,
            'likes' => $likes_to_pay,
            'money' => $this->getMoneyToPay()
        ];
        
        return $result;
    }
    public function checkAlreadyViewLine($photo_id)
    {
        $values['photo_id'] = $photo_id;
        $values['user_id']  = self::getUserId();
        return $this->select(SQL::$selCheckAlreadyViewLine, $values);
    }
    public function checkSightMode()
    {
        $values['chat_id'] = Data::getChatID();
        $result = $this->select(SQL::$selUserSettings, $values);
        return $result['only_sights'];
    }

    public function getUserDistance()
    {
        $values['chat_id'] = Data::getChatID();
        return $this->select(SQL::$selUserDistance, $values);
    }
    public function getLocation($photo_id)
    {
        $values['photo_id'] = $photo_id;
        return $this->select(SQL::$selAddress, $values);
    }
    public function getUserId()
    {
        $values['chat_id'] = Data::getChatID();
        return $this->select(SQL::$selUserID, $values);
    }
    public function getUserLocation()
    {
        $values['chat_id'] = Data::getChatID();
        return $this->select(SQL::$selUserGeo, $values);
    }
    public function getPhotoFileIDOnUploading()
    {
        $values['chat_id'] = Data::getChatID();
        return $this->select(SQL::$selPhotoFileIDOnUploading, $values);
    }
    public function getPhotoIDOnUploading()
    {
        $values['chat_id'] = Data::getChatID();
        return $this->select(SQL::$selPhotoIDOnUploading, $values);
    }
    public function getLastWatchedPhotoID()
    {
        $values['chat_id'] = Data::getChatID();
        return $this->select(SQL::$selLastWatchedPhotoID, $values, true);
    }
    public function getInformationAboutPhoto()
    {
        $values['chat_id'] = Data::getChatID();
        return $this->select(SQL::$selInformationAboutPhoto, $values, true);
    }
    public function getRandPhoto()
    {
        
        $user_sight_settings = $this->checkSightMode();
        
        $count_sql = SQL::$selCountUnwatchedPhoto;
        $photo_sql = SQL::$selRandomPhoto;
        
        if ($user_sight_settings) 
        {
            $count_sql .= " AND sight = 1";
            $photo_sql .= " AND sight = 1 ";
        }
        $values['chat_id'] = Data::getChatID();
        
        $count = $this->select($count_sql, $values);
        $n     = rand(0 , $count - 1);
        $photo = $this->select($photo_sql." LIMIT ".$n.", 1", $values);

        unset($values);

        if ($photo)
        {
            $values['user_id']        = $this->getUserId();
            $values['photo_id']       = $photo['photo_id'];
            $this->insert(SQL::$insInViewHistory, $values);
        }

        return $photo;
    }
    public function getNearPhoto($n = -1)
    {
        $info = self::getUserLocation();
                
        $values['lat']  = $info['lat'];
        $values['lng']  = $info['lng'];
        $values['dist'] = $info['dist'];
        
        if ($n == -1) {
            $sql = SQL::$selGeoPhoto . "LIMIT 1";
        }
        else {
            $sql = SQL::$selGeoPhoto . "LIMIT " . $n . ", 2";
        }
        
        if ($photo = $this->select($sql, $values))
        {
            unset($values);
            
            if (count($photo) == 2)
            {
                $photo_id = $photo[1]['photo_id'];
            }
            else
            {
                $photo_id = $photo['photo_id'];
            }

            if (!$this->checkAlreadyViewLine($photo_id) and $photo)
            {
                $values['photo_id'] = $photo_id;
                $this->update(SQL::$updNewView, $values);
                $values['user_id']  = $this->getUserId();
                $this->insert(SQL::$insInViewHistory, $values);
            }
        }

        return $photo;
    }
    public function getStartPointViewID($photo_id, $last_views)
    {
        $values['photo_id'] = $photo_id;
        $sql = SQL::$selViewIDStartingPoint . " LIMIT " . ($last_views - 1) . ", 1";
        return $this->select($sql, $values);
    }
    public function getEndPointViewID($photo_id)
    {
        $values['photo_id'] = $photo_id;
        return $this->select(SQL::$selViewIDEndingPoint, $values);
    }
    public function getLastLikes($photo_id, $last_views)
    {
        $values['start_point'] = $this->getStartPointViewID($photo_id, $last_views);
        $values['end_point']   = $this->getEndPointViewID($photo_id);
        $values['photo_id']    = $photo_id;
        return $this->select(SQL::$selLastLikes, $values);
    }
    public function getInfoAboutAuthor($photo_id)
    {
        $values['photo_id'] = $photo_id;
        return $this->select(SQL::$selInfoAboutAuthor, $values);
    }
    public function getAdminsChatID()
    {
        return $this->select(SQL::$selAdminsChatID);
    }
    public function getMoneyToPay()
    {
        return $this->select(SQL::$selMoneyToPay);
    }

    public function updateActivity()
    {
        $values['chat_id'] = Data::getChatID();
        $this->update(SQL::$updActivity, $values);
    }
    public function updateViews($photo_id)
    {
        $values['photo_id'] = $photo_id;
        $this->update(SQL::$updNewView, $values);
    }   
    public function updatePhotoLocation($address)
    {
        $values['address']    = $address;
        $values['coordinate'] = 'POINT(' . Data::getLatitude() . " " . Data::getLongitude() . ')';
        $values['chat_id']    = Data::getChatID();

        $this->update(SQL::$updPhotoCoordinate, $values);
    }
    public function updatePhotoCaption()
    {
        $values['caption']    = Data::getVenueTitle();
        $values['chat_id']    = Data::getChatID();
        $this->update(SQL::$updPhotoCaption, $values);
    }
    public function updateUserLocation()
    {
        $values['coordinate'] = 'POINT(' . Data::getLatitude() . " " . Data::getLongitude() . ')';
        $values['chat_id']    = Data::getChatID();

        $this->update(SQL::$updUserCoordinate, $values);
    }
    public function updateUserDistance($distance)
    {
        $values['distance'] = $distance;
        $values['chat_id']  = Data::getChatID();

        $this->update(SQL::$updUserDistance, $values);
    }

    public function setLike($photo_id)
    {
        $values['chat_id']  = Data::getChatID();
        $values['photo_id'] = $photo_id;
        return $this->update(SQL::$updLike, $values);
    }
    public function setSightMode()
    {
        $values['chat_id']  = Data::getChatID();
        $this->update(SQL::$updSetSightMode, $values);
    }
    public function setDislike($photo_id)
    {
        $values['chat_id']  = Data::getChatID();
        $values['photo_id'] = $photo_id;
        return $this->update(SQL::$updDislike, $values);
    }

    public function sendToUploading()
    {
        $values['photo_tlgrm_id'] = Data::getPhotoFileID();
        $values['auth_id']        = $this->getUserId();
        $values['status']         = 0;

        if (Data::getCaption()) {
            $values['caption']    = Data::getCaption();
            $this->insert(SQL::$insPhoto, $values);
        }
        else {
            $this->insert(SQL::$insPhotoWithoutCaption, $values);
        }
    }
    public function sendToModeration()
    {
        $values['auth_id']        = $this->getUserId();
        $this->insert(SQL::$updToModeration, $values);
    }
    
    public function resetLastViews($photo_id)
    {
        $values['photo_id'] = $photo_id;
        $this->update(SQL::$updResetLastViews, $values);
    }
    public function deletePhotoLocation()
    {
        $values['photo_id'] = $this->getPhotoIDOnUploading();
        $this->insert(SQL::$delCoordinateOnUploading, $values);
    }
    public function unsetSightMode()
    {
        $values['chat_id']  = Data::getChatID();
        $this->update(SQL::$updUnsetSightMode, $values);
    }
    
    public function addPhotoLocation($address)
    {
        
        $values['address']     = $address;
        $values['coordinate']  = 'POINT(' . Data::getLatitude() . " " . Data::getLongitude() . ')';
        $values['photo_id']    = $this->getPhotoIDOnUploading();

        $this->insert(SQL::$insPhotoCoordinate, $values);
    }

    public function createNewPay(){
        $values['chat_id']   = Data::getChatID();
        $this->insert(SQL::$insPay, $values);
    }
    public function createNewUser(){
        $values['user_name'] = Data::getFullName();
        $values['chat_id']   = Data::getChatID();
        $this->insert(SQL::$insNewUser, $values);
    }
    public function createNewReport($photo_id, $subject){
        $values['subject']   = $subject;
        $values['photo_id']  = $photo_id;
        $values['chat_id']   = Data::getChatID();
        $this->insert(SQL::$insNewReport, $values);
    }
    
    public function select($sql, array $values = null, $selectAll = false){

        try
        {
            $s = $this->pdo->prepare($sql);

            if(isset($values)) {
                foreach ($values as $key => $value) {
                    $s->bindValue(":$key", $value);
                }
            }

            $s->execute();
        }
        catch(PDOException $e)
        {
            echo 'Ошибка!' . $e->getMessage();
            exit();
        }
        
        $result = $s->fetchAll();
        
        if (count($result) == 1)
        {
            if (count($result[0]) <= 2 and !$selectAll){
                return $result[0][0];
            }
            return $result[0];
        }
        
        return $result;
    }
    public function insert($sql, array $values = null){

        try
        {
            $s = $this->pdo->prepare($sql);

            if(isset($values)) {
                foreach ($values as $key => $value) {
                    $s->bindValue(":$key", $value);
                }
            }

            $s->execute();
        }
        catch(PDOException $e)
        {
            echo 'Ошибка!' . $e->getMessage();
            exit();
        }
    }
    public function update($sql, array $values = null){

        try
        {
            $s = $this->pdo->prepare($sql);

            if(isset($values)) {
                foreach ($values as $key => $value) {
                    $s->bindValue(":$key", $value);
                }
            }

            return $s->execute();
        }
        catch(PDOException $e)
        {
            echo 'Ошибка!' . $e->getMessage();
            exit();
        }
        
    }
    
}