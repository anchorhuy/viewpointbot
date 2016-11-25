<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 12.10.2016
 * Time: 2:09
 */
 
namespace Output;

use Input\Data;
use Types\ReplyKeyboardHide;

class Request
{
    private $keyboard;
    private $caption;
    private $parse_mode;
    private $one_time_keyboard;
    private $resize_keyboard;
    
    
    public function __construct(array $parameters = [])
    {
        foreach ($parameters as $k => $v) {
            $this->$k = $v;
        }

        if (!isset($this->parse_mode)) {
            $this->parse_mode = "HTML";
        }

        if (!isset($this->one_time_keyboard)) {
            $this->one_time_keyboard = false;
        }

        if (!isset($this->resize_keyboard)) {
            $this->resize_keyboard = true;
        }
    }


    public function hideKeyboard()
    {
        $keyboard['reply_markup'] =
            [
                'hide_keyboard' => true
            ];

        $this->keyboard = $keyboard;
    }

    public function forwardMessage()
    {
        $parameters['method']        = "forwardMessage";
        $parameters['from_chat_id']  = Data::getChatID();
        $parameters['chat_id']       = Data::getChatID();
        $parameters['message_id']    = Data::getMessageID();

        return $this->curl_request_json($parameters);
    }

    public function getFile()
    {
        $parameters['method']        = "getFile";
        $parameters['from_chat_id']  = Data::getChatID();
        $parameters['chat_id']       = Data::getChatID();
        $parameters['message_id']    = Data::getMessageID();

        return $this->curl_request_json($parameters);
    }
    
    public function answerCallbackQuery($text, $show_alert = false)
    {
        $parameters['method']            = "answerCallbackQuery";
        $parameters['text']              = $text;
        $parameters['callback_query_id'] = Data::getCallbackQueryID();
        $parameters['show_alert']        = $show_alert;
        
        return $this->curl_request_json($parameters);
    }
    
    public function editMessageText($text)
    {
        if (isset($this->keyboard)) {
            $parameters = $this->keyboard;
        }

        $parameters['method']                   = "editMessageText";
        $parameters['text']                     = $text;
        $parameters['disable_web_page_preview'] = false;
        $parameters['message_id']               = Data::getMessageID();
        $parameters['chat_id']                  = Data::getChatID();
        $parameters['parse_mode']               = $this->parse_mode;

        return $this->curl_request_json($parameters);

    }
    public function editMessageReplyMarkup($message_id = 0, $chat_id = 0)
    {
        if ($this->keyboard) {
            $parameters = $this->keyboard;
        }

        $message_id = ($message_id ?: Data::getMessageID());
        $chat_id    = ($chat_id    ?: Data::getChatID());

        $parameters['method']     = "editMessageReplyMarkup";
        $parameters['message_id'] = $message_id;
        $parameters['chat_id']    = $chat_id;

        return $this->curl_request_json($parameters);

    }
    
    public function sendMessage($text, $chat_id = 0)
    {
        if ($this->keyboard)
        {
            $parameters = $this->keyboard;
        }

        if (!$chat_id) $chat_id = Data::getChatID();
        
        $parameters['method']                   = "sendMessage";
        $parameters['text']                     = $text;
        $parameters['chat_id']                  = $chat_id;
        $parameters['parse_mode']               = $this->parse_mode;

        return $this->curl_request_json($parameters);
    }
    public function sendPhoto($photo, $chat_id = 0)
    {
        if (isset($this->keyboard)) {
            $parameters = $this->keyboard;
        }

        if (isset($this->caption)) {
            $parameters['caption'] = $this->caption;
        }

        if (!$chat_id) $chat_id = Data::getChatID();

        $parameters['method']        = "sendPhoto";
        $parameters['photo']         = $photo;
        $parameters['chat_id']       = $chat_id;

        return $this->curl_request_json($parameters);
    }
    public function sendFile($file)
    {
        if (isset($this->keyboard)) {
            $parameters = $this->keyboard;
        }

        if (isset($this->caption)) {
            $parameters['caption'] = $this->caption;
        }

        $parameters['method']        = "sendDocument";
        $parameters['document']      = $file;
        $parameters['chat_id']       = Data::getChatID();

        return $this->curl_request_json($parameters);
    }
    public function sendVenue(array $venue)
    {
        if ($this->keyboard) {
            $parameters = $this->keyboard;
        }
        
        if ($venue['title']) {
            $title = $venue['title'];
        }
        else {
            $title = 'Фотография сделана здесь';
        }

        $lat = $venue['lat'];
        $lng = $venue['lng'];
        $adr = $venue['address'];
        
        $parameters['method']    = "sendVenue";
        $parameters['latitude']  = $lat;
        $parameters['longitude'] = $lng;
        $parameters['title']     = $title;
        $parameters['address']   = $adr;
        $parameters['chat_id']   = Data::getChatID();

        return $this->curl_request_json($parameters);
    }
    public function sendLocation(array $location)
    {
        if ($this->keyboard) {
            $parameters = $this->keyboard;
        }

        $lat = $location['lat'];
        $lng = $location['lng'];

        $parameters['method']    = "sendLocation";
        $parameters['latitude']  = $lat;
        $parameters['longitude'] = $lng;
        $parameters['chat_id']   = Data::getChatID();

        return $this->curl_request_json($parameters);
    }

    public function createInlineKeyboard($keyboard)
    {
        $parameters['reply_markup'] =
            [
                'inline_keyboard' => $keyboard
            ];
        
        $this->keyboard = $parameters;
    }
    public function createReplyKeyboard($keyboard)
    {
        if ($keyboard)
        {
            $parameters['reply_markup'] =
                [
                    'keyboard' => $keyboard,
                    'one_time_keyboard' => $this->one_time_keyboard,
                    'resize_keyboard' => $this->resize_keyboard
                ];

            $this->keyboard = $parameters; 
        }
    }
    public function createCaption($caption)
    {
        $this->caption = $caption;
    }
    
    public function unsetOneTimeKeyboard(){
        $this->one_time_keyboard = false;
    }
    public function unsetResizeKeyboard(){
        $this->resize_keyboard = false;
    }
    public function unsetKeyboard(){
        unset($this->keyboard);
    }
    public function unsetCaption(){
        unset($this->caption);
    }
    
    private function curl_request_json($parameters) {
        
        $handle = curl_init(API_URL);
        curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($handle, CURLOPT_TIMEOUT, 60);
        curl_setopt($handle, CURLOPT_POSTFIELDS, json_encode($parameters));
        curl_setopt($handle, CURLOPT_HTTPHEADER, array("Content-Type: application/json"));
        $response = curl_exec($handle);

        if ($response === false) {
            $errno = curl_errno($handle);
            $error = curl_error($handle);
            error_log("Curl returned error $errno: $error\n");
            curl_close($handle);
            return false;
        }

        $http_code = intval(curl_getinfo($handle, CURLINFO_HTTP_CODE));
        curl_close($handle);

        if ($http_code >= 500) {
            // do not wat to DDOS server if something goes wrong
            sleep(10);
            return false;
        } else if ($http_code != 200) {
            $response = json_decode($response, true);
            error_log("Request has failed with error {$response['error_code']}: {$response['description']}\n");
            if ($http_code == 401) {
                throw new Exception('Invalid access token provided');
            }
            return false;
        } else {
            $response = json_decode($response, true);
            if (isset($response['description'])) {
                error_log("Request was successfull: {$response['description']}\n");
            }
            $response = $response['result'];
        }

        return $response;
    }
    
}