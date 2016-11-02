<?php

require_once("const.php");
require_once("Autoloader.php");

Autoloader::autoloadRegister();

use Output\Request    as Request;
use Output\Keyboards  as Keyboards;
use Database\Database as Database;
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
        case 'nextRandImg':

            # Запрос на следующую картинку
            $request->answerCallbackQuery('Загружаем следующую фотографию..');

            # Айди прошлой фотографии
            $last_photo = $database->getInformationAboutLastPhoto();
            $last_photo_id = $last_photo['photo_id'];
            $last_photo_file_id = $last_photo['file_tlgrm_id'];
            $last_photo_address = $last_photo['address'];
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
                $database->updateViews($photo_id);
            } else {
                $text = "У меня больше нет новых фотографий 😥\n\r";
                $text = "Попробуй немного позже";
                $request->sendMessage($text);
            }

            exit();
        case 'setThisLocation':
            if ($database->checkUploading())
            {
                $latitude    = $data->message->venue->location->latitude;
                $longitude   = $data->message->venue->location->longitude;
                $coordinate  = $latitude . ',' . $longitude;
                $url         = GOOGLE_API_URL_GEOCODE . $coordinate . GOOGLE_API_KEY;
                $update      = json_decode(file_get_contents($url), true);
                $new_address = $update['results'][0]['formatted_address'];

                $keyboard[] = Keyboards::$replySendToModeration;
                $keyboard[] = Keyboards::$replyDeleteAddress;

                if ($database->checkIssetCoordinate()) {
                    $database->updatePhotoCoordinate($new_address);
                    $text = "<b>Координаты были успешно обновлены.</b>\n\r\n\r";
                } else {
                    $database->addPhotoCoordinate($new_address);
                    $text = "<b>Выбранное место успешно прикреплено к фотографии!</b>\n\r\n\r";
                }
                if ($database->checkIssetFile()) {
                    $keyboard[] = Keyboards::$replyDeleteFile;
                    $text .= "Теперь можно отправить Point на модерацию.\n\r";
                } else {
                    $text .= "Отправь ту же фотографию документом чтобы пользователи увидели ее в полном разрешении.\n\r";
                }

                $database->updatePhotoCaption();
                $request->createReplyKeyboard($keyboard);
                $request->sendMessage($text);
            } 
            exit();
            break;
        case 'howToAttachLocation':
            $text = "<b>Прикрепить место можно двумя способами:</b> \n\r\n\r";
            $text .= "- Написать название объекта и отправить мне, в ответ я выведу все места, которые мне известгны, среди них ты сможешь выбрать нужное и прикрепить к фотографии\n\r\n\r";
            $text .= "- Самостоятельно найти на карте точку и отправить ее в виде локации\n\r";
            $keyboard[] = Keyboards::$inlineHowToAttachLocationInDetails;
            $keyboard[] = Keyboards::$inlineHowToAttachFile;
            $request->createInlineKeyboard($keyboard);
            $request->editMessageReplyMarkup();
            $request->editMessageText($text);
            exit();
            break;
        case 'howToAttachLocationInDetails':
            $text  = "Нажми на 📎 в нижнем левом углу и выбери «Location»;\n\r";
            $text .= "Если ты находишься на месте, где сделана фотография нажми «Send my current location».\n\r";
            $text .= "Если же текущее местоположение отличается, то нужно перетаскивая точку по карте найти именно то место, где сделана фотография.\n\r\n\r";
            $keyboard[] = Keyboards::$inlineHowToAttachFile;
            $request->createInlineKeyboard($keyboard);
            $request->editMessageReplyMarkup();
            $request->editMessageText($text);
            exit();
            break;
        case 'howToAttachFile':
            $text  = "Нажми на 📎 в нижнем левом углу и выбери «File»;\n\r";
            $text .= "Среди фотографий выбери ту, которая только что была загружена тобой\n\r";
            $keyboard[] = Keyboards::$inlineHowToAttachLocation;
            $request->createInlineKeyboard($keyboard);
            $request->editMessageReplyMarkup();
            $request->editMessageText($text);
            exit();
            break;
        case 'setSightMode':
            $database->setSightMode();
            $keyboard[] = Keyboards::$inlineUnsetSightMode;
            $request->createInlineKeyboard($keyboard);
            $request->editMessageReplyMarkup();
            exit();
            break;
        case 'unsetSightMode':
            $database->unsetSightMode();
            $keyboard[] = Keyboards::$inlineSetSightMode;
            $request->createInlineKeyboard($keyboard);
            $request->editMessageReplyMarkup();
            exit();
            break;
    }

    if (substr($data->data, 0, 4)  == "like")
    {
        $photo_id = substr($data->data, 4);

        if ($database->checkAlreadyLike($photo_id)) {
            $request->answerCallbackQuery('❤ уже стоит', true);
            exit();
        }

        $request->answerCallbackQuery('Спасибо за ❤');
        $database->setLike($photo_id);

        if ($pay_info = $database->checkLikesToPay($photo_id))
        {

            $money = $pay_info['money'];
            $likes = (int)$pay_info['likes'] + 1;
            $views = $pay_info['views'];


            $author_info = $database->getInfoAboutAuthor($photo_id);
            $author_chat_id = $author_info['chat_id'];
            $photo_tlgrm_id = $author_info['photo_tlgrm_id'];

            $textForAuthor = "Поздравляю!\n";
            $textForAuthor .= "Твоя фоотография набарала " . $likes . " ❤ за " . $views . " просмотров\n";


            if ($database->checkIssetPhone()) {
                $textForAuthor .= "Скоро я переведу деньги на твой QIWI 💸";
            } else {
                $textForAuthor .= "Нажми на кнопку снизу чтобы мы знали куда перевести деньги";
                $request->createReplyKeyboard(Keyboards::$replySendContact);
            }

            $request->createCaption($textForAuthor);
            $request->sendPhoto($photo_tlgrm_id, $author_chat_id);

            $request->unsetKeyboard();
            $textForAdmin = "Еще одна фотография набрала необходимой количество ❤";
            if (is_array($admins_chat_id = $database->getAdminsChatID())) {
                foreach ($admins_chat_id as $admin_chat_id) {
                    $request->sendMessage($textForAdmin, $admin_chat_id);
                }
            } else {
                $request->sendMessage($textForAdmin, $admins_chat_id);
            }
        }
        exit();
    }
    if (substr($data->data, 0, 7)  == "dislike")
    {
        $photo_id = substr($data->data, 7);

        if ($database->checkAlreadyDislike($photo_id))
        {
            $request->answerCallbackQuery('😓');
            $database->setDislike($photo_id);
        }
        else
        {
            $request->answerCallbackQuery('💔 уже стоит', true);
        }
        exit();
    }
    if (substr($data->data, 0, 2)  == "gf")
    {
        $request->answerCallbackQuery('Загружаю документ..');
        $photo_id = substr($data->data, 2);
        
        if ($file_photo_id = $database->getFile($photo_id))
        {
            if ($database->checkAlreadyReport($photo_id, 1))
            {
                $caption = 'Жалоба на документ обрабатывается';
            }
            else
            {
                $caption = 'Можешь пожаловаться, если документ не совпадает с фотографией';
                $keyboard[] = [
                    [
                        "text" => "Не совпадает с фотографией",
                        "callback_data" => "reportF" . $photo_id
                    ]
                ];
                $request->createInlineKeyboard($keyboard);
            }

            $request->createCaption($caption);
            $request->sendFile($file_photo_id);
        }
        else
        {
            $request->answerCallbackQuery('Оригинал фотографии не найден 😓', true);
        }
        exit();
    }
    if (substr($data->data, 0, 2)  == "gl")
    {
        $request->answerCallbackQuery('Загружаю локацию..');
        $photo_id = substr($data->data, 2);
        if ($venue = $database->getAddress($photo_id))
        {
            if (!$database->checkAlreadyReport($photo_id, 0))
            {
                $keyboard[] = [
                    [
                        "text" => "Место не совпадает",
                        "callback_data" => "reportL" . $photo_id
                    ]
                ];
                $request->createInlineKeyboard($keyboard);
            }
            
            if ($venue['address']) 
            {
                $request->sendVenue($venue);
            }
            else 
            {
                $location = $venue;
                $request->sendLocation($venue);
            }
        }
        else
        {
            $request->answerCallbackQuery('Координаты фотографии не найден 😓', true);
        }
        exit();
    }
    if (substr($data->data, 0, 6)  == "report")
    {
        $subject  = $data->data{6};
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

        if ($database->checkAlreadyReport($photo_id, $subject_index))
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
    if (substr($data->data, 0, 10) == "nextGeoImg")
    {
        $request->answerCallbackQuery('Загружаю следующую фотографию..');
        $num   = (int) substr($data->data, 10);
        $photo = $database->getNearPhoto($num);

        if (count($photo) == 2){
            $last_photo  = $photo[0];
            $next_photo  = $photo[1];
        }
        else{
            $last_photo = $photo;
        }

        $last_photo_id       = $last_photo['photo_id'];
        $last_photo_tlgrm_id = $last_photo['photo'];
        $last_file           = $last_photo['file'];
        $last_address        = $last_photo['address'];
        if ($last_address) {
            $keyboard[] = [
                [
                    "text" => "Координаты",
                    "callback_data" => "gl" . $last_photo_id
                ]
            ];
        }
        if ($last_file) {
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

        $request->createInlineKeyboard($keyboard);
        $request->editMessageReplyMarkup();

        if ($next_photo)
        {
            unset($keyboard);
            $request->unsetKeyboard();

            $next_num = $num + 1;
            $next_photo_tlgrm_id = $next_photo['photo'];
            $next_photo_id       = $next_photo['photo_id'];
            $next_file           = $next_photo['file'];
            $next_address        = $next_photo['address'];
            $next_caption        = $next_photo['caption'] . "\n";
            $next_caption       .= "До этого места " . round((float) $next_photo['distance'], 2) . "км";
            $keyboard[] = [
                [
                    "text" => "Следующая",
                    "callback_data" => "nextGeoImg".$next_num
                ]
            ];
            if ($next_address) {
                $keyboard[] = [
                    [
                        "text" => "Координаты",
                        "callback_data" => "gl" . $next_photo_id
                    ]
                ];
            }
            if ($next_file) {
                $keyboard[] = [
                    [
                        "text" => "Оригинал",
                        "callback_data" => "gf" . $next_photo_id
                    ]
                ];
            }
            $keyboard[] = [
                [
                    "text" => "💔",
                    "callback_data" => "dislike" . $next_photo_id
                ],
                [
                    "text" => "❤",
                    "callback_data" => "like" . $next_photo_id
                ]
            ];

            $request->createCaption($next_caption);
            $request->createInlineKeyboard($keyboard);
            $request->sendPhoto($next_photo_tlgrm_id);
        }
        else
        {
            $text  = "<b>На указанном расстоянии больше нет фотографий</b>.\n\r\n\r";
            $text .= "Чтобы изменить радиус введите команду /dist_* (где «*» - целое число).\n\r";
            $text .= "<i>Пример</i>: /dist_3.";
            $request->unsetKeyboard();
            $request->sendMessage($text);            
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
            if ($database->checkIssetCoordinate())
            {
                $caption    = "Отправь этот Point на модерацию чтобы загрузить новый.\n";
                $keyboard[] = Keyboards::$replySendToModeration;
                $keyboard[] = Keyboards::$replyDeleteAddress;
                if ($database->checkIssetFile()) {
                    $keyboard[] = Keyboards::$replyDeleteFile;
                }
            }
            else
            {
                $caption  = "Прикрепи к этой фотографии геолокацию места и отправь на модерацию.\n";
                $caption .= "После этого ты сможешь добавить следующий Point.\n";
                if ($database->checkIssetFile()) {
                    $keyboard[] = Keyboards::$replyDeleteFile;
                }
            }
            
            $photo   = $database->getPhotoFileIDOnUploading();
            
            $request->createCaption($caption);
            $request->createReplyKeyboard($keyboard);
            $request->sendPhoto($photo);
            exit();
        }

        $database->sendToUploading();

        $text  = "Красивое фото!\n\r\n\r";
        $text .= "Осталось указазать геолокацию места и можешь отправлять на модерацию.\n\r";
        $text .= "Прикрепи оригинал фотографии, так ты получишь больше ❤.\n\r\n\r";
        $request->sendMessage($text);

        $text = "<i>Если что-то не понятно, то нажми на соответствующую кнопку под этим сообщением</i>.";
        $keyboard[] = Keyboards::$inlineHowToAttachLocation;
        $keyboard[] = Keyboards::$inlineHowToAttachFile;
        $request->createInlineKeyboard($keyboard);
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

                    if ($database->checkIssetCoordinate())
                    {
                        $keyboard[] = Keyboards::$replyDeleteAddress;
                    }

                    $request->createReplyKeyboard($keyboard);
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
                    $text .= "Теперь Point можно отправить на модерацию.\n";
                    $keyboard[] = Keyboards::$replyDeleteAddress;
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
                $latitude  = $data->location->latitude;
                $longitude = $data->location->longitude;
                
                $url = "https://maps.googleapis.com/maps/api/geocode/json?latlng=$latitude,$longitude&language=ru&result_type=street_address&key=AIzaSyAoSshruro4rvjdMicj1c0mvchKAVLMBg4";
                $update = json_decode(file_get_contents($url), true);
                $address = $update['results'][0]['formatted_address'];
                $database->addPhotoCoordinate($address);
                $text = "<b>Спасибо за геолокацию фотографии!</b>\n\r";
            }
            else
            {
                $text  = "<b>К фотографии уже прикреплена геолокация</b>\n\r.";
                $text .= "Удали существующую чтобы добавить новую..";

                if ($database->checkIssetFile())
                {
                    $keyboard[] = Keyboards::$replyDeleteFile;
                }

                $request->createReplyKeyboard($keyboard);
                $request->sendMessage($text);

                exit();
            }

            if (!$database->checkIssetFile()) {
                $text .= "Прикрепи оригинал документом чтобы люди смогли оценить ее по достоинству.\n\r";
            }
            else
            {
                $keyboard[] = Keyboards::$replyDeleteFile;
                $text .= "Теперь Point можно отправить на модерацию.\n";
            }

            $request->createReplyKeyboard($keyboard);
            $request->sendMessage($text);
            
            exit();
        }
        $database->updateUserCoordinate();
        if ($photo = $database->getNearPhoto()){
            $photo_tlgrm_id = $photo['photo'];
            $photo_id       = $photo['photo_id'];
            $file           = $photo['file'];
            $address        = $photo['address'];
            $caption        = $photo['caption'] . "\n";
            $caption       .= "До этого места " . round((float) $photo['distance'], 2) . "км";
            $keyboard[]     = [
                [
                    "text" => "Следующий",
                    "callback_data" => "nextGeoImg0"
                ]
            ];
            if ($address) {
                $keyboard[] = [
                    [
                        "text" => "Координаты",
                        "callback_data" => "gl" . $photo_id
                    ]
                ];
            }
            if ($file) {
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

            $request->createCaption($caption);
            $request->createInlineKeyboard($keyboard);
            $request->sendPhoto($photo_tlgrm_id);
            $database->updateViews($photo_id);
        }
        exit();
    }
    if ($input_text = $data->text)
    {
        if ($input_text == "/start")
        {
            if ($database->checkNewUser())
            {
                $database->createNewUser();
            }

            $text = "Рад приветствовать тебя, " . "<b>" . $data->from->first_name . "</b>" . "\n\r\n\r";

            $text .= "<b>Point</b> - это красивая фотография места вместе с ее геопозицией на карте." . "\n\r";
            $text .= "<i>Так же Point в виде бонуса может содержать в себе файл с исходным изображением без сжатия</i>." . "\n\r\n\r";

            $text .= "Я могу показать Point, находящийся в заданном тобой радиусе от заданного тобой места, или случайный." . "\n\r\n\r";

            $text .= "Ты можешь поделиться своими любимыми местами." . "\n\r";
            $text .= "Для этого отправь мне фотографию места и следуй дальнейшим инструкциям." . "\n\r\n\r";

            $text .= "Используй кнопки, которые появились у тебя вместо клавиатуры, для навигации." . "\n\r";

            $request->createReplyKeyboard(Keyboards::$replyDefault);
            $request->sendMessage($text);
            exit();
        }
        if ($input_text == "Помощь")
        {
            $text .= "<b>Point</b> - это красивая фотография места вместе с ее геопозицией на карте." . "\n\r";
            $text .= "<i>Так же Point в виде бонуса может содержать в себе файл с исходным изображением без сжатия</i>." . "\n\r\n\r";

            $text .= "Я могу показать Point, находящийся в заданном тобой радиусе от заданного тобой места, или случайный." . "\n\r\n\r";

            $text .= "Ты можешь поделиться своими любимыми местами." . "\n\r";
            $text .= "Для этого отправь мне фотографию места и следуй дальнейшим инструкциям." . "\n\r\n\r";

            $text .= "Используй кнопки, которые появились у тебя вместо клавиатуры, для навигации." . "\n\r";

            $request->createReplyKeyboard(Keyboards::$replyDefault);
            $request->sendMessage($text);
            exit();
        }
        if ($input_text == "/chatid") {
            $request->sendMessage($data->chat->id);
            exit();
        }
        if ($input_text == "Случайный Point")
        {
            if ($photo = $database->getRandPhoto())
            {
                $photo_id = $photo['photo_id'];
                $photo_tlgrm_id = $photo['photo_tlgrm_id'];
                $photo_file_tlgrm_id = $photo['file_tlgrm_id'];
                $photo_caption = $photo['caption'];
                $photo_address = $photo['address'];

                $keyboard[] = [
                    [
                        "text" => "Следующий",
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
                $database->updateViews($photo_id);
            }
            else
            {
                $text = 'Вы посмотрели все фотографии из нашей базы';
                $request->sendMessage($text);
            }

            exit();
        }
        if ($input_text == "Настройки")
        {
            $user_distance = $database->getUserDistance();
            
            $text  = "На данный момент ты можешь:"."\n\r\n\r";
            $text .= "<b>- Измнеить радиус поиска</b>."."\n\r";
            $text .= "Для этого введи и отправь мне команду /dist* где «*» - радиус в километрах."."\n\r\n\r";
            $text .= "<i>Число должно быть целым.</i>"."\n\r\n\r";
            $text .= "<b>- Включить или выключить Sight Mode</b>."."\n\r";
            $text .= "<i>Sight Mode</i> - режим поиска, при котором тебе будут показываться только точки (Point) <b>достопримечательностей</b>"."\n\r\n\r";
            $text .= "Текущий радиус поиска - ".$user_distance." км";
            
            if ($database->checkSightMode()) {
                $keyboard[] = Keyboards::$inlineUnsetSightMode;
            }
            else {
                $keyboard[] = Keyboards::$inlineSetSightMode;
            }
            $request->createInlineKeyboard($keyboard);
            $request->sendMessage($text);
            exit();
        }
        if (substr($input_text, 0, 5)  == "/dist")
        {
            $new_distance = (int) substr($input_text, 5);
            if ($new_distance != 0) {
                if ($new_distance < 7000)
                {
                    $database->updateUserDistance($new_distance);
                    $text = "Радиус поиска был успешно изменен на ".$new_distance." км"."\n\r";
                    $request->sendMessage($text);
                }
                else
                {
                    $text = "Попробуй число поменьше"."\n\r";
                    $request->sendMessage($text);
                }
            }
            else
            {
                $text  = "<b>Не могу распознать число 😓</b>"."\n\r";
                $text .= "Пример правильной команды:"."\n\r";
                $text .= "/dist13";
                $request->sendMessage($text);
            }
            
            
        }
        if ($database->checkUploading())
        {
            if ($input_text == 'Отправить на модерацию')
            {
                if ($database->checkIssetCoordinate())
                {
                    $database->sendToModeration();
                    $text = "<b>Point успешно отправлен на модерацию</b>.\n\r";
                    $request->createReplyKeyboard(Keyboards::$replyDefault);
                }
                else
                {
                    $text = "<b>Чтобы отправить Point на модерацию нужно прикрепить место</b>.\n\r";
                    $keyboard[] = Keyboards::$inlineHowToAttachLocation;
                    $request->createInlineKeyboard($keyboard);
                }
                $request->sendMessage($text);
                exit();
            }
            if ($input_text == 'Удалить файл')
            {
                $database->deleteFile();
                $text = "Файл удален";
                $keyboard[] = Keyboards::$replySendToModeration;
                !$database->checkIssetCoordinate() ?: $keyboard[] = Keyboards::$replyDeleteAddress;
                $request->createReplyKeyboard($keyboard);
                $request->sendMessage($text);
                exit();
            }
            if ($input_text == 'Удалить место')
            {
                $database->deleteCoordinate();
                $text = "Адрес удален";
                $keyboard[] = Keyboards::$replySendToModeration;
                !$database->checkIssetFile() ?: $keyboard[] = Keyboards::$replyDeleteFile;
                $request->createReplyKeyboard($keyboard);
                $request->sendMessage($text);
                exit();
            }

            $address = urlencode($data->text);
            $url = GOOGLE_API_URL_FIND_PLACE . $address . GOOGLE_API_KEY;
            $update = json_decode(file_get_contents($url), true);

            $results = $update['results'];
            $status  = $update['status'];

            if ($status == 'OK')
            {
                $results_num = count($results);

                switch ($results_num) {
                    case 1 :
                        $place = 'место';
                        break;
                    case 2:
                    case 3:
                    case 4:
                        $place = 'места';
                        break;
                    default:
                        $place = 'мест';
                        break;
                }

                $text  = "<b>Найдено ".$results_num." ".$place."</b>\n\r\n\r";

                if ($results_num > 1){
                    $text .= "Какое из них прикрепить к загружаемой фотографии?\n\r\n\r";
                }
                else {
                    $text .= "Нажми на кнопку под локацией чтобы прикрепить ее к фотографии\n\r\n\r";
                }

                $request->sendMessage($text);

                foreach ($results as $result)
                {
                    $venue['address'] = $result['formatted_address'];
                    $venue['lat']     = $result['geometry']['location']['lat'];
                    $venue['lng']     = $result['geometry']['location']['lng'];
                    $venue['title']   = $result['name'];
                    $request->createInlineKeyboard(Keyboards::$inlineSetThisLocation);
                    $request->sendVenue($venue);
                }

                $text  = "<b>Нет нужного места?</b>\n\r";
            }
            else
            {
                $text  = "Я не смог найти «".$input_text."» 😥 \n\r";
            }

            $text .= "Попробуй:\n\r";
            $text .= "- сформулировать запрос по-другому,\n\r";
            $text .= "- самостоятельно прикрепить локацию";
            $request->createInlineKeyboard([Keyboards::$inlineHowToAttachLocation]);
            $request->sendMessage($text);
            $request->hideKeyboard();
            exit();
        }

    }
}
