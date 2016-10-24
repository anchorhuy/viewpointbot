<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */

class Message
{
    public $message_id;
    public $from;
    public $date;
    public $chat;
    public $forward_from;
    public $forward_from_chat;
    public $forward_date;
    public $reply_to_message;
    public $edit_date;
    public $text;
    public $entities;
    public $audio;
    public $document;
    public $game;
    public $photo;
    public $sticker;
    public $video;
    public $voice;
    public $caption;
    public $contact;
    public $location;
    public $venue;
    public $new_chat_member;
    public $left_chat_member;
    public $new_chat_title;
    public $new_chat_photo;
    public $delete_chat_photo;
    public $group_chat_created;
    public $supergroup_chat_created;
    public $channel_chat_created;
    public $migrate_to_chat_id;
    public $migrate_from_chat_id;
    public $pinned_message;

    public function __construct($message)
    {
        foreach ($message as $k => $v)
        {

            if ($k == 'message_id'              or
                $k == 'date'                    or
                $k == 'forward_date'            or
                $k == 'edit_date'               or
                $k == 'text'                    or
                $k == 'caption'                 or
                $k == 'delete_chat_photo'       or
                $k == 'group_chat_created'      or
                $k == 'supergroup_chat_created' or
                $k == 'channel_chat_created'    or
                $k == 'migrate_to_chat_id'      or
                $k == 'migrate_from_chat_id'     )
            {
                $this->$k = $v;
                continue;
            }


            if ($k == 'from'              or
                $k == 'forward_from'      or
                $k == 'new_chat_member'   or
                $k == 'left_chat_member'   )
            {
                $this->$k = new User($v);
                continue;
            }


            if ($k == 'chat'              or
                $k == 'forward_from_chat'  )
            {
                $this->$k = new Chat($v);
                continue;
            }


            if ($k == 'reply_to_message'  or
                $k == 'pinned_message'     )
            {
                $this->$k = new Message($v);
                continue;
            }


            if ($k == 'entities')
            {
                foreach ($v as $entity)
                {
                    $this->entities[] = new MessageEntity($entity);
                }
                continue;
            }


            if ($k == 'photo') {
                foreach ($v as $photoSize)
                {
                    $this->photo[] = new PhotoSize($photoSize);
                }
                continue;
            }


            if ($k == 'audio') {
                $this->$k = new Audio($v);
                continue;
            }

            if ($k == 'document') {
                $this->$k = new Document($v);
                continue;
            }
//
//            if ($k == 'game') {
//                $this->$k = new Game($v);
//                continue;
//            }

            if ($k == 'sticker') {
                $this->$k = new Sticker($v);
                continue;
            }

            if ($k == 'video') {
                $this->$k = new Video($v);
                continue;
            }

            if ($k == 'voice') {
                $this->$k = new Voice($v);
                continue;
            }

            if ($k == 'contact') {
                $this->$k = new Contact($v);
                continue;
            }

            if ($k == 'location') {
                $this->$k = new Location($v);
                continue;
            }

            if ($k == 'venue') {
                $this->$k = new Venue($v);
                continue;
            }


        }

        define("MESSAGE_ID", $this->message_id);

    }
}


class User
{
    public $id;
    public $first_name;
    public $last_name;
    public $username;

    public function __construct($user)
    {
        foreach ($user as $k => $v)
        {
            $this->$k = $v;
        }
    }
}


class Chat
{
    public $id;
    public $type;
    public $title;
    public $username;
    public $first_name;
    public $last_name;
    public $all_members_are_administrators;

    public function __construct($chat)
    {
        foreach ($chat as $k => $v)
        {
            $this->$k = $v;
        }

        define("CHAT_ID",    $this->id);
        define("USER_NAME",  html_entity_decode($this->first_name)
            .' '
            .html_entity_decode($this->last_name));
    }
}


class MessageEntity
{
    public $type;
    public $offset;
    public $length;
    public $url;
    public $user;

    public function __construct($entity)
    {
        foreach ($entity as $k => $v)
        {

            if ($k == 'user') {
                $this->$k = new User($v);
                continue;
            }

            $this->$k = $v;

        }
    }
}


class PhotoSize
{
    public $file_id;
    public $width;
    public $height;
    public $file_size;

    public function __construct($photo)
    {
        foreach ($photo as $k => $v)
        {
            $this->$k = $v;
        }
    }
}


class Audio
{
    public $file_id;
    public $duration;
    public $performer;
    public $title;
    public $mime_type;
    public $file_size;

    public function __construct($audio)
    {
        foreach ($audio as $k => $v)
        {
            $this->$k = $v;
        }
    }
}


class Document
{
    public $file_id;
    public $thumb;
    public $file_name;
    public $mime_type;
    public $file_size;

    public function __construct($document)
    {
        foreach ($document as $k => $v)
        {

            if ($k == 'thumb') {
                $this->$k = new PhotoSize($v);
                continue;
            }

            $this->$k = $v;

        }
    }
}


class Sticker
{
    public $file_id;
    public $width;
    public $height;
    public $thumb;
    public $emoji;
    public $file_size;

    public function __construct($sticker)
    {
        foreach ($sticker as $k => $v)
        {

            if ($k == 'thumb') {
                $this->$k = new PhotoSize($v);
                continue;
            }

            $this->$k = $v;

        }
    }
}


class Video
{
    public $file_id;
    public $width;
    public $height;
    public $duration;
    public $thumb;
    public $mime_type;
    public $file_size;

    public function __construct($video)
    {
        foreach ($video as $k => $v)
        {

            if ($k == 'thumb') {
                $this->$k = new PhotoSize($v);
                continue;
            }

            $this->$k = $v;

        }
    }
}


class Voice
{
    public $file_id;
    public $duration;
    public $mime_type;
    public $file_size;

    public function __construct($voice)
    {
        foreach ($voice as $k => $v)
        {
            $this->$k = $v;
        }
    }

}


class Contact
{
    public $phone_number;
    public $first_name;
    public $last_name;
    public $user_id;

    public function __construct($contact)
    {
        foreach ($contact as $k => $v)
        {
            $this->$k = $v;
        }
    }
}


class Location
{
    public $longitude;
    public $latitude;


    public function __construct($location)
    {
        foreach ($location as $k => $v)
        {
            $this->$k = $v;
        }
    }
}


class Venue
{
    public $location;
    public $title;
    public $address;
    public $foursquare_id;

    public function __construct($venue)
    {
        foreach ($venue as $k => $v)
        {

            if ($k == 'location') {
                $this->$k = new Location($v);
                continue;
            }

            $this->$k = $v;

        }
    }
}


class UserProfilePhotos
{
    public $total_count;
    public $photos;

    public function __construct($userProfilePhotos)
    {
        foreach ($userProfilePhotos as $k => $v)
        {
            if ($k == 'photos') {
                foreach ($k as $k2 => $v2)
                {
                    $this->$k2 = new Location($v2);
                }
                continue;
            }

            $this->$k = $v;

        }
    }
}


class ReplyKeyboardMarkup {
    public $keyboard;
    public $resize_keyboard;
    public $one_time_keyboard;
    public $selective;

    public function __construct($keyboard)
    {
        foreach ($keyboard as $k => $v)
        {

            if ($k == 'keyboard') {
                foreach ($k as $k2 => $v2)
                {
                    $this->$k2 = new KeyboardButton($v2);
                }
                continue;
            }

            $this->$k = $v;

        }
    }
}


class KeyboardButton {
    public $text;
    public $request_contact;
    public $request_location;

    public function __construct($button)
    {
        foreach ($button as $k => $v)
        {
            $this->$k = $v;
        }
    }
}


class ReplyKeyboardHide {
    public $hide_keyboard;
    public $selective;

    public function __construct($keyboardHide)
    {
        foreach ($keyboardHide as $k => $v)
        {
            $this->$k = $v;
        }
    }
}


class InlineKeyboardMarkup {
    public $inline_keyboard;

    public function __construct($inlineKeyboard)
    {
        foreach ($inlineKeyboard as $k => $v)
        {

            if ($k == 'inline_keyboard') {
                foreach ($k as $k2 => $v2)
                {
                    $this->$k2 = new InlineKeyboardButton($v2);
                }
                continue;
            }

            $this->$k = $v;

        }
    }
}


class InlineKeyboardButton {
    public $text;
    public $url;
    public $callback_data;
    public $switch_inline_query;
    public $switch_inline_query_current_chat;
    public $callback_game;

    public function __construct($inlineButton)
    {
        foreach ($inlineButton as $k => $v)
        {
            $this->$k = $v;
        }
    }
}


class CallbackQuery {
    public $id;
    public $from;
    public $message;
    public $inline_message_id;
    public $chat_instance;
    public $data;
    public $game_short_name;

    public function __construct($callbackQuery)
    {
        foreach ($callbackQuery as $k => $v)
        {
            if ($k == 'from') {
                $this->$k = new User($v);
                continue;
            }

            if ($k == 'message') {
                $this->$k = new Message($v);
                continue;
            }

            $this->$k = $v;
        }
    }
}


class ForceReply {
    public $force_reply;
    public $selective;

    public function __construct($forceReply)
    {
        foreach ($forceReply as $k => $v)
        {
            $this->$k = $v;
        }
    }
}


class ChatMember {
    public $user;
    public $status;

    public function __construct($chatMember)
    {
        foreach ($chatMember as $k => $v)
        {
            if ($k == 'user') {
                $this->$k = new User($v);
                continue;
            }

            $this->$k = $v;

        }
    }
}


class ResponseParameters {
    public $migrate_to_chat_id;
    public $retry_after;

    public function __construct($responseParameters)
    {
        foreach ($responseParameters as $k => $v)
        {
            $this->$k = $v;
        }
    }
}

