<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 10.10.2016
 * Time: 3:15
 */
namespace Types;

use Types\Contact              as Contact;
use Types\Location             as Location;
use Types\User                 as User;
use Types\Chat                 as Chat;
use Types\Venue                as Venue;
use Types\InlineKeyboardButton as InlineKeyboardButton;
use Types\InlineKeyboardMarkup as InlineKeyboardMarkup;
use Types\KeyboardButton       as KeyboardButton;
use Types\Document             as Document;
use Types\PhotoSize            as PhotoSize;


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
