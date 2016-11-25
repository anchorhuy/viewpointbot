<?php
/**
 * Created by PhpStorm.
 * User: Pavel
 * Date: 12.10.2016
 * Time: 2:10
 */

namespace Output;


class Keyboards
{
    public static $replySendToModeration = [
        [
            "text" => "Загрузить Point"
        ]
    ];
    public static $replySendContact = [
        [
            [
                "text"            => "Отправить мой номер",
                "request_contact" => true
            ]
        ]
    ];
    public static $replyDefault = [
        [
            [
                "text"             => "Ближайший Point",
                "request_location" => true
            ]
        ],

        [
            [
                "text"             => "Случайный Point"
            ]
        ],

        [
            [
                "text"            => "Помощь"
            ],
            [
                "text"            => "Настройки"
            ]
        ]

    ];
    public static $replyDeleteAddress = [
        [
            "text" => "Удалить место"
        ]
    ];
    public static $replyDeletePhoto = [
        [
            "text" => "Удалить фотографию"
        ]
    ];
    
    public static $inlineSetThisLocation = [
        [
            [
                "text"          => "Прикрепить это место",
                "callback_data" => "setThisLocation"
            ]
        ]
    ];
    public static $inlineNextRandomPhoto = [
            [
                "text" => "→",
                "callback_data" => "nextRandImg"
            ]
    ];
    public static $inlineHowToAttachPlace = [
        [
            "text"          => "Как прикрепить место?",
            "callback_data" => "howToAttachLocation"
        ]
    ];
    public static $inlineSetSightMode = [
        [
            "text"          => "Включить Sight Mode",
            "callback_data" => "setSightMode"
        ]
    ];
    public static $inlineUnsetSightMode = [
        [
            "text"          => "Выключить Sight Mode",
            "callback_data" => "unsetSightMode"
        ]
    ];
    public static $inlineHowToAttachLocationInDetails = [
        [
            "text"          => "Как отправить локацию?",
            "callback_data" => "howToAttachLocationInDetails"
        ]
    ];

    public static function createKeyboardGeoPhoto($photo_id, $num)
    {
        $keyboard[] = self::createButtonNextGeoPhoto($num);
        $keyboard[] = self::createButtonGetLocation($photo_id);
        $keyboard[] = self::createButtonsFeedback($photo_id);

        return $keyboard;
    }
    public static function createKeyboardRandomPhoto($photo_id)
    {
        $keyboard[] = self::$inlineNextRandomPhoto;
        $keyboard[] = self::createButtonGetLocation($photo_id);
        $keyboard[] = self::createButtonsFeedback($photo_id);

        return $keyboard;
    }
    public static function createKeyboardForEditMessage($photo_id) 
    {
        $keyboard[] = self::createButtonGetLocation($photo_id);
        $keyboard[] = self::createButtonsFeedback($photo_id);

        return $keyboard;
    }

    public static function createButtonGetLocation($photo_id) {
        return
            [
                [
                    "text" => "Место",
                    "callback_data" => "gl" . $photo_id
                ]
            ];
    }
    public static function createButtonsFeedback($photo_id) {
        return
            [
                [
                    "text" => "💔",
                    "callback_data" => "dislike" . $photo_id
                ],
                [
                    "text" => "❤",
                    "callback_data" => "like" . $photo_id
                ]
            ];
    }
    public static function createButtonNextGeoPhoto($num) {
        return
            [
                [
                    "text" => "→",
                    "callback_data" => "nextGeoImg".$num
                ]
            ];
    }

}

