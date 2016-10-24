<?php

require_once("const.php");
require_once("Autoloader.php");

Autoloader::autoloadRegister();

use Types\Message              as Message;
use Types\CallbackQuery        as CallbackQuery;

use Output\Request    as Request;
use Output\Keyboards  as Keyboards;

use Database\Database as Database;
use Database\SQL      as SQL;

use Input\Data as Data;


//Get Data
$data = Data::getData();

//Connect to Database
$database = new Database();
$database->updateActivity();

//New template for request
$request  = new Request();


if ($data->message)
{
    switch ($data->data)
    {
        case 'deleteCoordinate' :

            $func = $data->data;
            $database->$func();
            $request->answerCallbackQuery('Геолокация успешно удалена', true);
            $request->createInlineKeyboard([]);
            $request->editMessageReplyMarkup();
            $request->editMessageText('Теперь можешь прикрепить новую геолокацию');
            exit();
            break;
        case 'deleteFile' :

            $func = $data->data;
            $database->$func();
            $request->answerCallbackQuery('Файл успешно удален', true);
            $request->createInlineKeyboard([]);
            $request->editMessageReplyMarkup();
            $request->editMessageText('Теперь можешь прикрепить новый оригинал');
            exit();
            break;
        case 'nextRandImg':

            # Запрос на следующую картинку
            $request->answerCallbackQuery('Загружаем следующую фотографию..');

            # Айди прошлой фотографии
            $last_photo = $database->getInformationAboutLastPhoto();
            $last_photo_id        = $last_photo['photo_id'];
            $last_photo_file_id   = $last_photo['file_tlgrm_id'];
            $last_photo_address   = $last_photo['address'];

            if ($database->checkIsItLastPhoto($last_photo_id)){
                $keyboard[] = [
                    [
                        "text" => "Следующая",
                        "callback_data" => "nextRandImg"
                    ]
                ];
            }
            if ($last_photo_address) {
                $keyboard[] = [
                    [
                        "text" => "Координаты",
                        "callback_data" => "gl" . $last_photo_id
                    ]
                ];
            }
            if ($last_photo_file_id) {
                    $keyboard[] = [
                        [
                            "text" => "Оригинал",
                            "callback_data" => "gf" . $last_photo_id
                        ]
                    ];
                }
            $keyboard[] = [
                [
                    "text" => "💔",
                    "callback_data" => "dislike" . $last_photo_id
                ],
                [
                    "text" => "❤",
                    "callback_data" => "like" . $last_photo_id
                ]
            ];

            # Редактируем сообщение
            $request->createInlineKeyboard($keyboard);
            $request->editMessageReplyMarkup();
            $request->unsetKeyboard();
            unset($keyboard);

            if ($photo = $database->getRandPhoto())
            {
                $photo_id            = $photo['photo_id'];
                $photo_tlgrm_id      = $photo['photo_tlgrm_id'];
                $photo_file_tlgrm_id = $photo['file_tlgrm_id'];
                $photo_caption       = $photo['caption'];
                $photo_address       = $photo['address'];

                $keyboard[] = [
                    [
                        "text" => "Следующая",
                        "callback_data" => "nextRandImg"
                    ]
                ];
                if ($photo_caption) {
                    $request->createCaption($photo_caption);
                }
                if ($photo_address) {
                    $keyboard[] = [
                        [
                            "text" => "Координаты",
                            "callback_data" => "gl" . $photo_id
                        ]
                    ];
                }
                if ($photo_file_tlgrm_id) {
                    $keyboard[] = [
                        [
                            "text" => "Оригинал",
                            "callback_data" => "gf" . $photo_id
                        ]
                    ];
                }
                $keyboard[] = [
                    [
                        "text" => "💔",
                        "callback_data" => "dislike" . $photo_id
                    ],
                    [
                        "text" => "❤",
                        "callback_data" => "like" . $photo_id
                    ]
                ];


                $request->createInlineKeyboard($keyboard);
                $request->sendPhoto($photo_tlgrm_id);
            }
            else
            {
                $text = 'Вы посмотрели все фотографии из нашей базы';
                $request->sendMessage($text);
            }

            exit();
            break;
    }
    if (substr($data->data, 0, 4) == "like")
    {
        $photo_id = substr($data->data, 4);

        if ($database->checkAlreadyLike($photo_id)) 
        {
            $request->answerCallbackQuery('Спасибо за ❤');
            $database->setLike($photo_id);
        }
        else 
        {
            $request->answerCallbackQuery('❤ уже стоит', true);
        }
        exit();
        /*
        # Айди прошлой фотографии
        $photo = $database->getInformationAboutLastPhoto();
        $photo_file_id   = $last_photo['file_tlgrm_id'];
        $photo_address   = $last_photo['address'];
        $keyboard        = [
            [
                [
                    "text" => "Нравится",
                    "callback_data" => "unlike" . $photo_id
                ]
            ]
        ];

        if ($photo_address) {
            $keyboard[] = [
                [
                    "text" => "Координаты",
                    "callback_data" => "gl" . $photo_id
                ]
            ];
        }
        if ($photo_file_id) {
                $keyboard[] = [
                    [
                        "text" => "Оригинал",
                        "callback_data" => "gf" . $photo_id
                    ]
                ];
            }

        # Редактируем сообщение
        $request->createInlineKeyboard($keyboard);
        $request->editMessageReplyMarkup();
        $request->unsetKeyboard();
*/
    }
    if (substr($data->data, 0, 7) == "dislike")
    {
        $photo_id = substr($data->data, 7);

        if ($database->checkAlreadyDislike($photo_id))
        {
            $request->answerCallbackQuery('Жаль что фотография тебе не понравилась 😓');
            $database->setDislike($photo_id);
        }
        else
        {
            $request->answerCallbackQuery('💔 уже стоит', true);
        }
        exit();
    }
    if (substr($data->data, 0, 2) == "gf")
    {
        $photo_id = substr($data->data, 2);
        
        if ($file_photo_id = $database->getFile($photo_id))
        {
            $caption = 'Можешь пожаловаться, если документ не совпадает с фотографией';
            $keyboard[] = [
                [
                    "text" => "Не совпадает с фотографией",
                    "callback_data" => "reportF" . $photo_id
                ]
            ];
            $request->createCaption($caption);
            $request->createInlineKeyboard($keyboard);
            $request->sendFile($file_photo_id);
        }
        else
        {
            $request->answerCallbackQuery('Оригинал фотографии не найден 😓', true);
        }
        exit();
    }
    if (substr($data->data, 0, 2) == "gl")
    {
        $photo_id = substr($data->data, 2);
        if ($address = $database->getAddress($photo_id))
        {
            $keyboard[] = [
                [
                    "text" => "Место не совпадает",
                    "callback_data" => "reportL" . $photo_id
                ]
            ];
            $request->createInlineKeyboard($keyboard);
            $request->sendVenue($address);
        }
        else
        {
            $request->answerCallbackQuery('Координаты фотографии не найден 😓', true);
        }
        exit();
    }
    if (substr($data->data, 0, 6) == "report")
    {
        $subject = substr($data->data, 6, 7);
        $photo_id = substr($data->data, 7);
        switch ($subject)
        {
            case 'L' :
                $subject_index = 0;
                break;
            case 'F' :
                $subject_index = 1;
                break;
        }

        if ($address = $database->checkAlreadyReport($photo_id, $subject_index))
        {
            $answer = 'Жалоба уже отправлена';
            $request->answerCallbackQuery($answer, true);
        }
        else
        {
            $answer = 'Отправляем жалобу';
            $request->answerCallbackQuery($answer);
            $request->createInlineKeyboard([]);
            $request->editMessageReplyMarkup();
            $database->createNewReport($photo_id, $subject_index);
        }
        exit();
    }
}
else
{
    if ($data->photo) {
        if ($database->checkBlackList()) {
            $text = "Вы не можете добавлять фотографии";
            $request->sendMessage($text);

            exit();
        } 
        elseif ($database->checkLimit()) {
            $text = "Вы не можете добавить больше " . LIMIT_PICS . " фотографий за раз.\n\r";
            $text .= "Необходимо подождать пока ваши фотографии пройдут модерацию.";

            $request->sendMessage($text);

            exit();
        }
        elseif ($database->checkUploading()) {
            $caption = "Нужно отправить данную фотографию на модерацию чтобы загрузить новую.\n\n";
            $photo = $database->getPhotoFileIDOnUploading();

            if ($database->checkIssetFile()) {
                $caption .= "❤ Оригинал фотографии загружен.\n";
            } else {
                $caption .= "💔 Оригинал фотографии не загружен.\n";
            }

            if ($database->checkIssetCoordinate()) {
                $caption .= "❤ Геопозиция фотографии определена.\n";
            } else {
                $caption .= "💔 Геопозиция фотографии не определена.\n";
            }

            $request->createCaption($caption);
            $request->createInlineKeyboard(Keyboards::$inlineSendToModeration);
            $request->sendPhoto($photo);
            exit();
        }

        $database->sendToUploading();

        $text = "Отлично, фотография загружена!\n\r\n\r";
        $text .= "Чтобы участвовать в партнерской программе нужно поделиться геопозицией места\n\r";
        $text .= "(<i>подробнее:</i> /partner).\n\r\n\r";
        $text .= "Также ты можешь отправить оригинал фотографии документом, пользователям это нравится 😉.\n\r\n\r";
        $text .= "<i>Если геолокация сфотографированного места не совпадает с текущей, то можешь написать адрес в сообщении и отправить в ответ на это</i>.";
        $keyboard[] = Keyboards::$replySendToModeration;

        $request->createReplyKeyboard($keyboard);
        $request->sendMessage($text);

        exit();
    }
    if ($data->document) {
        if ($database->checkUploading()) {
            if (substr($data->document->mime_type, 0, 5) == 'image')
            {
                $keyboard[] = Keyboards::$replySendToModeration;
                $keyboard[] = Keyboards::$replyDeleteFile;

                if ($database->checkIssetFile()) {
                    $text  = "К текущей фотографии уже был прикреплен документ.\n\r";
                    $text .= "Удали его чтобы загрузить новый";
                    $request->createInlineKeyboard(Keyboards::$inlineDeleteFile);
                    $request->sendMessage($text);

                    exit();
                }
                else
                {
                    $database->addFile();
                    $text = "<b>Спасибо за оригинал фотографии!</b>\n\r";
                }

                if (!$database->checkIssetCoordinate()) {
                    $text .= "Отправь геопозицию с места, где была сделана фотография, чтобы другие пользователи смогли там побывать.\n\r";
                } else {
                    $text .= "Теперь можно отправить её на модерацию.\n";
                    $keyboard[] = Keyboards::$replyDeleteAddress;
                    $request->createInlineKeyboard(Keyboards::$inlineSendToModeration);
                }

                $request->createReplyKeyboard($keyboard);
                $request->sendMessage($text);
                exit();
            }
            else {
                $text = "Этот формат не совсем подходит, попробуй другой.\n\r";
                $request->sendMessage($text);
            }
        }
        else
        {
            $text = "Сначала нужно загрузить фотографию";
            $request->sendMessage($text);
        }
    }
    if ($data->location)
    {
        if ($database->checkUploading())
        {
            $keyboard[] = Keyboards::$replySendToModeration;
            $keyboard[] = Keyboards::$replyDeleteAddress;

            if (!$database->checkIssetCoordinate()) {
                $latitude = $data->location->latitude;
                $longitude = $data->location->longitude;

                $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&language=ru&result_type=street_address&key=AIzaSyAoSshruro4rvjdMicj1c0mvchKAVLMBg4";
                $update = json_decode(file_get_contents($url), true);
                $address = $update['results'][0]['formatted_address'];
                $database->addPhotoCoordinate($address);

                $text = "<b>Спасибо за геолокацию фотографии!</b>\n\r";
            }
            else
            {
                $text = "К фотографии уже прикреплена геолокация, удали существующую чтобы добавить новую.";
                $request->createInlineKeyboard(Keyboards::$inlineDeleteCoordinate);
                $request->sendMessage($text);

                exit();
            }

            if (!$database->checkIssetFile()) {
                $text .= "Прикрепи оригинал документом чтобы люди смогли оценить ее по достоинству.\n\r";
            }
            else
            {
                $keyboard[] = Keyboards::$replyDeleteFile;
                $text .= "Теперь можно отправить её на модерацию.\n";
            }

            $request->createReplyKeyboard($keyboard);
            $request->sendMessage($text);
        }
        else
        {
            $database->setUserCoordinate();
            $photo = $database->getNearPhoto();
//            $request->sendMessage($database->getPhotosInDistance());
        }
        exit();

    }
    if ($input_text = $data->text)
    {
        if ($database->checkUploading())
        {
            if ($input_text == 'Отправить на модерацию')
            {
                $database->sendToModeration();
                $request->hideKeyboard();
                $text = "<b>Фотография успешно отправлена на модерацию</b>.\n\r";
                if (!$database->checkLimit())
                {
                    $text .= "Загрузи еще!\n\r";
                }
                $request->sendMessage($text);
                exit();
            }
            if ($input_text == 'Удалить файл')
            {
                $database->deleteFile();
                $text = "Файл удален";
                $keyboard[] = Keyboards::$replySendToModeration;
                $database->checkIssetCoordinate() ?: $keyboard[] = Keyboards::$replyDeleteAddress;
                $request->createReplyKeyboard($keyboard);
                $request->sendMessage($text);
                exit();
            }
            if ($input_text == 'Удалить адрес')
            {
                $database->deleteCoordinate();
                $text = "Адрес удален";
                $keyboard[] = Keyboards::$replySendToModeration;
                $database->checkIssetFile() ?: $keyboard[] = Keyboards::$replyDeleteFile;
                $request->createReplyKeyboard($keyboard);
                $request->sendMessage($text);
                exit();
            }

            $address = urlencode($data->text);
            $url = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $address . "&language=ru&key=AIzaSyAoSshruro4rvjdMicj1c0mvchKAVLMBg4";
            $update = json_decode(file_get_contents($url), true);

            if ($update['results'][0]['formatted_address']) {
                if (!$database->checkIssetCoordinate()) {
                    $data->location->latitude = $update['results'][0]['geometry']['location']['lat'];
                    $data->location->longitude = $update['results'][0]['geometry']['location']['lng'];
                    $address = $update['results'][0]['formatted_address'];
                    $database->addPhotoCoordinate($address);

                    $text = "Спасибо за геолокацию фотографии!\n\r\n\r";
                } else {
                    $text = "К фотографии уже прикреплена геолокация.\n\r";
                    $text .= "Удали существующую чтобы добавить новую.";
                    $request->createInlineKeyboard(Keyboards::$inlineDeleteCoordinate);
                    $request->sendMessage($text);
                    exit();
                }
            } else {
                $text = 'Я тебя не понимаю.';
                $text .= 'Если это адрес - то попробуй записать его по-другому.';
                $request->sendMessage($text);
                exit();
            }

            if (!$database->checkIssetFile()) {
                $text .= "Прикрепи оригинал документом чтобы люди смогли оценить ее по достоинству.\n\r";
            } else {
                $text .= "Теперь можно отправить фотографию на модерацию.\n";
            }

            $request->createInlineKeyboard(Keyboards::$inlineSendToModeration);
            $request->sendMessage($text);

            exit();
        }
        if ($input_text == "/start")
        {
            if ($database->checkNewUser())
            {
                $database->createNewUser();
            }

            $text = "Рад приветствовать тебя, " . "<b>" . $data->from->first_name . "</b>" . "\n\r\n\r";

            $text .= "Я могу поделиться с тобой слуйчайной фотографией живописного места." . "\n\r";
            $text .= "Для этого введи (или просто нажми): /rand_img" . "\n\r\n\r";

            $text .= "Также я могу показать фотографию живописного места, находящегося рядом с тобой." . "\n\r";
            $text .= "Чтобы получить фотографию отправь свою геолокацию." . "\n\r";
            $text .= "По умолчанию показываются результаты в радиусе 5км." . "\n\r";
            $text .= "Изменить это значение можно отправив запрос /dist_* (где «*» - целое число)." . "\n\r";
            $text .= "<i>Пример</i>: /dist_3." . "\n\r\n\r";

            $text .= "Ты можешь учавствовать в <b>партнерской программе</b> загружая свои фотографии." . "\n\r";
            $text .= "Чтобы узнать подробности введи (или просто нажми): /partner" . "\n\r\n\r";

            $request->createInlineKeyboard(Keyboards::$inlineGetStarted);
            $request->sendMessage($text);

            exit();
        }
        if ($input_text == "/chatid") {
            $request->sendMessage($data->chat->id);
            exit();
        }
        if ($input_text == "/rand_img") {
            if ($photo = $database->getRandPhoto()) {
                $photo_id = $photo['photo_id'];
                $photo_tlgrm_id = $photo['photo_tlgrm_id'];
                $photo_file_tlgrm_id = $photo['file_tlgrm_id'];
                $photo_caption = $photo['caption'];
                $photo_address = $photo['address'];

                $keyboard[] = [
                    [
                        "text" => "Следующая",
                        "callback_data" => "nextRandImg"
                    ]
                ];
                if ($photo_caption) {
                    $request->createCaption($photo_caption);
                }
                if ($photo_address) {
                    $keyboard[] = [
                        [
                            "text" => "Координаты",
                            "callback_data" => "gl" . $photo_id
                        ]
                    ];
                }
                if ($photo_file_tlgrm_id) {
                    $keyboard[] = [
                        [
                            "text" => "Оригинал",
                            "callback_data" => "gf" . $photo_id
                        ]
                    ];
                }
                $keyboard[] = [
                    [
                        "text" => "💔",
                        "callback_data" => "dislike" . $photo_id
                    ],
                    [
                        "text" => "❤",
                        "callback_data" => "like" . $photo_id
                    ]
                ];

                $request->createInlineKeyboard($keyboard);
                $request->sendPhoto($photo_tlgrm_id);
            } else {
                $text = 'Вы посмотрели все фотографии из нашей базы';
                $request->sendMessage($text);
            }
            exit();
        }
    }
}
