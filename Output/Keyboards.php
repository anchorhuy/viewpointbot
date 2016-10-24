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
    public static $selectDay = [
        [
            'Сейчас', 
            'Сегодня', 
            'Завтра'
        ],
        [
            'Вт',
            'Ср',
            'Чт',
            'Пт',
            'Сб'
        ]
    ];

    public static $replySendToModeration = [
        [
            "text" => "Отправить на модерацию"
        ]
    ];

    public static $replyDeleteFile = [
        [
            "text" => "Удалить файл"
        ]
    ];

    public static $replyDeleteAddress = [
        [
            "text" => "Удалить адрес"
        ]
    ];
    
    public static $inlineGetStarted = [
        [
            [
                "text"          => "Случайная фотография",
                "callback_data" => "/rand_img"
            ]
        ],

        [
            [
                "text"          => "Партнерская программа",
                "callback_data" => "/partner"
            ]
        ]
    ];

    public static $inlineRandomMessage = [
        [
            [
                "text"          => "Случайная фотография",
                "callback_data" => "/rand_img"
            ]
        ],

        [
            [
                "text"          => "Партнерская программа",
                "callback_data" => "/partner"
            ]
        ]
    ];

    public static $inlineSendToModeration = [
        [
            [
                "text"          => "Отправить на модерацию",
                "callback_data" => "Отправить на модерацию"
            ]
        ]
    ];

    public static $inlineDeleteFile = [
        [
            [
                "text"          => "Удалить оригинал",
                "callback_data" => "deleteFile"
            ]
        ],
        [
            [
                "text"          => "Отмена",
                "callback_data" => "cancel"
            ]
        ]
    ];

    public static $inlineDeleteCoordinate = [
        [
            [
                "text"          => "Удалить геолокацию",
                "callback_data" => "deleteCoordinate"
            ]
        ],
        [
            [
                "text"          => "Отмена",
                "callback_data" => "cancel"
            ]
        ]
    ];

}
