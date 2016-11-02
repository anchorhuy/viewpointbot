<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:13
 */

namespace Input;

class Data {

    public static $Message;
    public static $CallbackQuery;

    public static function getData()
    {
        $content = file_get_contents("php://input");
        $update = json_decode($content, true);

        if (!$update){
            exit();
        }

        foreach ($update as $k => $v)
        {
            if ($k == 'message') {
                self::$Message = new \Types\Message($v);
                return self::$Message;
            }

            elseif ($k == 'callback_query'){
                self::$CallbackQuery = new \Types\CallbackQuery($v);
                return self::$CallbackQuery;
            }
        }

    }

    public static function getChatID()
    {
        if (self::$Message) {
            $chatID = self::$Message->chat->id;
        }
        
        if (self::$CallbackQuery) {
            $chatID = self::$CallbackQuery->message->chat->id;
        }
        
        return $chatID;
    }
    
    public static function getCallbackQueryID()
    {
        if (self::$CallbackQuery) {
            $callbackQueryID = self::$CallbackQuery->id;
        }

        return $callbackQueryID;
    }

    public static function getMessageID()
    {
        if (self::$Message) {
            $messageID = self::$Message->message_id;
        }
        if (self::$CallbackQuery) {
            $messageID = self::$CallbackQuery->message->message_id;
        }

        return $messageID;
    }

    public static function getPhotoFileID()
    {
        if (self::$Message) {
            $photoFileID = self::$Message->photo[count(self::$Message->photo)-1]->file_id;
        }
        if (self::$CallbackQuery) {
            $photoFileID = self::$CallbackQuery->message->photo[count(self::$Message->photo)-1]->file_id;
        }

        return $photoFileID;
    }

    public static function getDocumentFileID()
    {
        if (self::$Message)
        {
            $documentFileID = self::$Message->document->file_id;
        }
        if (self::$CallbackQuery)
        {
            $documentFileID = self::$CallbackQuery->message->document->file_id;
        }
        return $documentFileID;
    }

    public static function getFullName()
    {
        if (self::$Message)
        {
            $fullName = self::$Message->chat->last_name . ' ' . self::$Message->chat->first_name;
        }

        if (self::$CallbackQuery)
        {
            $fullName = self::$CallbackQuery->message->chat->last_name . ' ' . self::$CallbackQuery->message->chat->first_name;
        }

        return $fullName;
    }

    public static function getCaption()
    {
        if (self::$Message)
        {
            $caption = self::$Message->caption;
        }

        if (self::$CallbackQuery)
        {
            $caption = self::$CallbackQuery->message->caption;
        }

        return $caption;
    }
    
    public static function getLatitude()
    {
        if (self::$Message)
        {
            $latitude = self::$Message->location->latitude;
        }

        if (self::$CallbackQuery)
        {
            $latitude = self::$CallbackQuery->message->location->latitude;
        }

        return $latitude;
    }
    
    public static function getLongitude()
    {
        if (self::$Message)
        {
            $longitude = self::$Message->location->longitude;
        }

        if (self::$CallbackQuery)
        {
            $longitude = self::$CallbackQuery->message->location->longitude;
        }

        return $longitude;
    }
    
    public static function getVenueTitle()
    {
        if (self::$Message)
        {
            $title = self::$Message->venue->title;
        }

        if (self::$CallbackQuery)
        {
            $title = self::$CallbackQuery->message->venue->title;
        }

        return $title;
    }
    
}


