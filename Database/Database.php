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
        $values = ['chat_id' => Data::getChatID()];
        if( $this->select(SQL::$selOnModeration, $values) >= LIMIT_PICS) {
            return 1;
        }
        else {
            return 0;
        }
    }
    public function checkIsItLastPhoto($photo_id)
    {
        $values['chat_id']        = Data::getChatID();
        $values['photo_id']       = $photo_id;
        $select = $this->select(SQL::$selIsItLastPhoto, $values);
        
        if ($select['max_id'] == $select['cur_id']) {
            return 1;
        }
        else {
            return 0;
        }
    }
    public function checkAdmin()
    {
        if ( Data::getChatID() == ADMIN_CHAT_ID ){
            return 1;
        }
        else {
            return 0;
        }
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
    public function checkIssetFile()
    {
        $values = ['chat_id' => Data::getChatID()];
        return $this->select(SQL::$selCheckFile, $values);
    }
    public function checkIssetCoordinate()
    {
        $values = ['chat_id' => Data::getChatID()];
        return $this->select(SQL::$selCheckCoordinate, $values);
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


    public function getUserDistance()
    {
        $values['chat_id'] = Data::getChatID();
        return $this->select(SQL::$selUserDistance, $values);
    }
    public function getAddress($photo_id)
    {
        $values['photo_id'] = $photo_id;
        return $this->select(SQL::$selAddress, $values);
    }
    public function getFile($photo_id)
    {
        $values['photo_id'] = $photo_id;
        return $this->select(SQL::$selFile, $values);
    }
    public function getUserId()
    {
        $values['chat_id'] = Data::getChatID();
        return $this->select(SQL::$selUserID, $values);
    }
    public function getUserGeo()
    {
        $values['chat_id'] = Data::getChatID();
        return $this->select(SQL::$selUserGeo, $values);
    }
    public function getFileIdOnUploading()
    {
        $values['chat_id'] = Data::getChatID();
        return $this->select(SQL::$selPhotoFileIDOnUploading, $values);
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
        return $this->select(SQL::$selLastWatchedPhotoID, $values);
    }
    public function getInformationAboutLastPhoto()
    {
        $values['chat_id'] = Data::getChatID();
        return $this->select(SQL::$selInformationAboutLastPhoto, $values, true);
    }
    public function getInformationAboutPhoto()
    {
        $values['chat_id'] = Data::getChatID();
        return $this->select(SQL::$selInformationAboutPhoto, $values, true);
    }
    public function getRandPhoto()
    {
        $values['chat_id'] = Data::getChatID();
        $n     = rand(0 , $this->select(SQL::$selCountUnwatchedPhoto, $values) - 1);
        $photo = $this->select(SQL::$selInformationAboutThisPhoto . " LIMIT " . $n . ", 1", $values);

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
        $info = self::getUserGeo();
                
        $values['lat']  = $info['lat'];
        $values['lng']  = $info['lng'];
        $values['dist'] = $info['dist'];
        
        if ($n == -1) {
            $sql = SQL::$selGeoPhoto . "LIMIT 1";
        }
        else {
            $sql = SQL::$selGeoPhoto . "LIMIT " . $n . ", 2";
        }
        return $this->select($sql, $values);
    }

    public function updateActivity()
    {
        $values['chat_id'] = Data::getChatID();
        $this->update(SQL::$updActivity, $values);
    }
    
    public function setLike($photo_id)
    {
        $values['chat_id']  = Data::getChatID();
        $values['photo_id'] = $photo_id;
        return $this->update(SQL::$updLike, $values);
    }
    public function setDislike($photo_id)
    {
        $values['chat_id']  = Data::getChatID();
        $values['photo_id'] = $photo_id;
        return $this->update(SQL::$updDislike, $values);
    }
    public function setUserCoordinate()
    {
        $values['coordinate'] = 'POINT(' . Data::getLatitude() . " " . Data::getLongitude() . ')';
        $values['chat_id']    = Data::getChatID();

        $this->insert(SQL::$updUserCoordinate, $values);
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
    public function sendToPublic()
    {
        $values['photo_tlgrm_id'] = Data::getPhotoFileID();
        $values['auth_id']        = $this->getUserId();
        $values['status']         = 2;

        if (Data::getCaption()) {
            $values['caption']    = Data::getCaption();
        }

        $this->insert(SQL::$insPhoto, $values);
    }
    
    public function deleteFile()
    {
        $values['photo_id'] = $this->getPhotoIDOnUploading();
        $this->insert(SQL::$delFileOnUploading, $values);
    }
    public function deleteCoordinate()
    {
        $values['photo_id'] = $this->getPhotoIDOnUploading();
        $this->insert(SQL::$delCoordinateOnUploading, $values);
    }
    
    public function addFile()
    {
        $values['file_tlgrm_id']  = Data::getDocumentFileID();
        $values['photo_id']       = $this->getPhotoIDOnUploading();

        $this->insert(SQL::$insFile, $values);
    }
    public function addPhotoCoordinate($address)
    {
        
        $values['address']     = $address;
        $values['coordinate']  = 'POINT(' . Data::getLatitude() . " " . Data::getLongitude() . ')';
        $values['photo_id']    = $this->getPhotoIDOnUploading();

        $this->insert(SQL::$insPhotoCoordinate, $values);
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