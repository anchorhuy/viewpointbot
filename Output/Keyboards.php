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
    
    public static $inlineSetThisLocation = [
        [
            [
                "text"          => "Прикрепить это место",
                "callback_data" => "setThisLocation"
            ]
        ]
    ];
    public static $inlineHowToAttachLocation = [
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

}

