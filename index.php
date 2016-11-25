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

            $request->answerCallbackQuery('Загружаю следующий Point..');

            $photo_id = $database->getLastWatchedPhotoID();
            $keyboard = Keyboards::createKeyboardForEditMessage($photo_id);

            $request->createInlineKeyboard($keyboard);
            $request->editMessageReplyMarkup();

            if ($photo = $database->getRandPhoto())
            {
                $photo_id       = $photo['photo_id'];
                $photo_tlgrm_id = $photo['photo_tlgrm_id'];
                if ($photo_caption  = $photo['caption']) {
                    $request->createCaption($photo_caption);
                }

                $keyboard = Keyboards::createKeyboardRandomPhoto($photo_id);

                $request->createInlineKeyboard($keyboard);
                $request->sendPhoto($photo_tlgrm_id);
                $database->updateViews($photo_id);
            }
            else
            {
                $request->unsetKeyboard();
                $text  = "У меня больше нет новых фотографий 😥\n\r";
                $text .= "Попробуй немного позже";
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

                if ($database->checkIssetLocation()) {
                    $database->updatePhotoLocation($new_address);
                    $text = "<b>Координаты были успешно обновлены.</b>\n\r\n\r";
                } else {
                    $database->addPhotoLocation($new_address);
                    $text  = "<b>Выбранное место успешно прикреплено к фотографии!</b>\n\r";
                    $text .= "<b>Теперь можешь загрузить Point!</b>\n\r\n\r";
                    $text .= "Если хочешь указать другое место, то удали прикрепленное(снизу есть кнопка) и отправь новое.";
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
            $request->createInlineKeyboard($keyboard);
            $request->editMessageReplyMarkup();
            $request->editMessageText($text);
            exit();
            break;
        case 'howToAttachLocationInDetails':
            $text  = "Нажми на 📎 в нижнем левом углу и выбери «Location»;\n\r";
            $text .= "Если ты находишься на месте, где сделана фотография нажми «Send my current location».\n\r";
            $text .= "Если же текущее местоположение отличается, то нужно перетаскивая точку по карте найти именно то место, где сделана фотография и нажать «Send this location».\n\r\n\r";

            $keyboard[] = Keyboards::$inlineHowToAttachPlace;
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

//        if ($pay_info = $database->checkLikesToPay($photo_id))
//        {
//
//            $money = $pay_info['money'];
//            $likes = (int)$pay_info['likes'] + 1;
//            $views = $pay_info['views'];
//
//
//            $author_info = $database->getInfoAboutAuthor($photo_id);
//            $author_chat_id = $author_info['chat_id'];
//            $photo_tlgrm_id = $author_info['photo_tlgrm_id'];
//
//            $textForAuthor = "Поздравляю!\n";
//            $textForAuthor .= "Твоя фоотография набарала " . $likes . " ❤ за " . $views . " просмотров\n";
//
//
//            if ($database->checkIssetPhone()) {
//                $textForAuthor .= "Скоро я переведу деньги на твой QIWI 💸";
//            } else {
//                $textForAuthor .= "Нажми на кнопку снизу чтобы мы знали куда перевести деньги";
//                $request->createReplyKeyboard(Keyboards::$replySendContact);
//            }
//
//            $request->createCaption($textForAuthor);
//            $request->sendPhoto($photo_tlgrm_id, $author_chat_id);
//
//            $request->unsetKeyboard();
//            $textForAdmin = "Еще одна фотография набрала необходимой количество ❤";
//            if (is_array($admins_chat_id = $database->getAdminsChatID())) {
//                foreach ($admins_chat_id as $admin_chat_id) {
//                    $request->sendMessage($textForAdmin, $admin_chat_id);
//                }
//            } else {
//                $request->sendMessage($textForAdmin, $admins_chat_id);
//            }
//        }
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
    if (substr($data->data, 0, 2)  == "gl")
    {
        $request->answerCallbackQuery('Загружаю место..');
        $photo_id = substr($data->data, 2);
        if ($venue = $database->getLocation($photo_id))
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

        if ($database->checkAlreadyReport($photo_id))
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
            $database->createNewReport($photo_id);
        }
        exit();
    }
    if (substr($data->data, 0, 10) == "nextGeoImg")
    {
        $request->answerCallbackQuery('Загружаю следующий Point..');
        $num   = (int) substr($data->data, 10);
        $photo = $database->getNearPhoto($num);

        if (count($photo) == 2){
            $last_photo  = $photo[0];
            $next_photo  = $photo[1];
        }
        else{
            $last_photo = $photo;
        }

        $last_photo_id = $last_photo['photo_id'];
        $keyboard = Keyboards::createKeyboardForEditMessage($last_photo_id);
        $request->createInlineKeyboard($keyboard);
        $request->editMessageReplyMarkup();

        if ($next_photo)
        {
            $num = $num + 1;
            $photo_id = $next_photo['photo_id'];

            $keyboard = Keyboards::createKeyboardGeoPhoto($photo_id,$num);
            $request->createInlineKeyboard($keyboard);

            $caption  = $next_photo['caption'] . "\n";
            $caption .= "До этого места " . round((float) $next_photo['distance'], 2) . "км";
            $request->createCaption($caption);

            $photo = $next_photo['photo'];
            $request->sendPhoto($photo);
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
            $limit = $database->getLimit();
            $text = "Вы не можете добавить больше " . $limit . " фотографий за раз.\n\r";
            $text .= "Необходимо подождать пока ваши фотографии пройдут модерацию.";
            $request->sendMessage($text);
            exit();
        }
        elseif ($database->checkUploading()) {
            if ($database->checkIssetLocation())
            {
                $caption    = "Отправь этот Point на модерацию чтобы загрузить новый.\n";
                $keyboard[] = Keyboards::$replySendToModeration;
                $keyboard[] = Keyboards::$replyDeleteAddress;
                $request->createReplyKeyboard($keyboard);
            }
            else
            {
                $caption  = "Прикрепи к этой фотографии геолокацию места и отправь на модерацию.\n";
                $caption .= "После этого ты сможешь добавить следующий Point.\n";
            }
            
            $photo = $database->getPhotoFileIDOnUploading();
            $request->createCaption($caption);
            $request->sendPhoto($photo);
            exit();
        }

        if ($captionLength = strlen(Data::getCaption()) <= 160)
        {
            $database->sendToUploading();
            $text  = "<b>Красивое фото!</b>\n\r\n\r";
            $text .= "Осталось указазать место.\n\r";
            $keyboard[] = Keyboards::$replyDeletePhoto;
            $request->createReplyKeyboard($keyboard);
            $request->hideKeyboard();
        }
        else
        {
            $text  = "Описание должно содержать в себе не больше 160 символов\n\r";
            $text .= "Загрузи фотографию заново и убери ".($captionLength - 160)." символов";
        }
        
        $message = $request->sendMessage($text);
        $keyboard[] = Keyboards::$inlineHowToAttachPlace;
        $request->createInlineKeyboard($keyboard);
        $request->editMessageReplyMarkup($message['message_id']);
        exit();
    }
    if ($data->location)
    {
        if ($database->checkUploading())
        {
            $keyboard[] = Keyboards::$replySendToModeration;
            $keyboard[] = Keyboards::$replyDeleteAddress;
            $keyboard[] = Keyboards::$replyDeletePhoto;

            if (!$database->checkIssetLocation())
            {
                $latitude   = $data->location->latitude;
                $longitude  = $data->location->longitude;
                $coordinate = $latitude.','.$longitude;
                
                $url = GOOGLE_API_URL_GEOCODE.$coordinate.GOOGLE_API_KEY;
                $update = json_decode(file_get_contents($url), true);
                $address = $update['results'][0]['formatted_address'];
                $database->addPhotoLocation($address);
                $text  = "<b>Место успешно прикреплено.</b>\n\r\n\r";
                $text .= "Теперь можешь загрузить Point!\n\r";
            }
            else
            {
                $text  = "<b>К фотографии уже прикреплено место</b>\n\r\n\r.";
                $text .= "Нажми на кнопку «Удалить место» в нижней части экрана чтобы прикрепить новое.";
            }

            $request->createReplyKeyboard($keyboard);
            $request->sendMessage($text);
            exit();
        }
        $database->updateUserLocation();
        
        if ($photo = $database->getNearPhoto())
        {
            $photo_id       = $photo['photo_id'];
            $keyboard = Keyboards::createKeyboardGeoPhoto($photo_id, 0);
            $request->createInlineKeyboard($keyboard);

            $caption  = $photo['caption'] . "\n";
            $caption .= "До этого места " . round((float) $photo['distance'], 2) . "км";
            $request->createCaption($caption);

            $photo = $photo['photo'];
            $request->sendPhoto($photo);

            $database->updateViews($photo_id);
        }
        else
        {
            $distance = $database->getUserDistance();
            $text  = "<b>Я не смог найти Point в радиусе ".$distance." км</b>.\n\r\n\r";
            $text .= "Попробуй увеличить расстояние поиска.\n\r";
            $text .= "Для этого отправь /dist*.\n\r";
            $text .= "Вместо «*» напиши новый радиус в км.\n\r\n\r";
            $text .= "<i>Пример:</i>\n\r";
            $text .= "/dist50";
            $request->sendMessage($text);
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

            $text = "Рад приветствовать тебя, " . "<b>" . $data->from->first_name . "</b>" . "!\n\r\n\r";

            $text .= "<b>Point</b> - это фотография красивого места вместе с его геопозицией на карте." . "\n\r\n\r";

            $text .= "Я могу показать Point, находящийся в заданном тобой радиусе от заданного тобой места, или случайный." . "\n\r\n\r";

            $text .= "Так можно загрузить свой Point." . "\n\r";
            $text .= "Для этого отправь мне фотографию места и следуй дальнейшим инструкциям." . "\n\r\n\r";

            $text .= "Используй кнопки, которые появились у тебя вместо клавиатуры, для навигации." . "\n\r";

            $request->createReplyKeyboard(Keyboards::$replyDefault);
            $request->sendMessage($text);
            exit();
        }
        if ($input_text == "Помощь")
        {
            $text .= "<b>Point</b> - это фотография красивого места вместе с его геопозицией на карте." . "\n\r\n\r";

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
                $keyboard = Keyboards::createKeyboardRandomPhoto($photo_id);
                $request->createInlineKeyboard($keyboard);

                if ($photo_caption  = $photo['caption']) {
                    $request->createCaption($photo_caption);
                }

                $photo = $photo['photo_tlgrm_id'];
                $request->sendPhoto($photo);

                $database->updateViews($photo_id);
            }
            else
            {
                $text = "У меня больше нет новых фотографий 😥\n\r";
                $text = "Попробуй немного позже";
                $request->sendMessage($text);
            }

            exit();
        }
        if ($input_text == "Настройки")
        {
            $user_distance = $database->getUserDistance();
            
            $text  = "На данный момент ты можешь:"."\n\r\n\r";
            $text .= "<b>- Измнеить радиус поиска</b>."."\n\r";
            $text .= "Для этого введи и отправь мне команду /dist* где «*» - радиус в километрах."."\n\r";
            $text .= "<i>Число должно быть целым.</i>"."\n\r\n\r";
            $text .= "<b>- Включить или выключить Sight Mode</b>."."\n\r";
            $text .= "<i>Sight Mode</i> - режим поиска, при котором тебе будут показываться только точки (Point) <b>достопримечательностей</b>."."\n\r\n\r";
            $text .= "Текущий радиус поиска - ".$user_distance." км.";
            
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
            exit();
        }
        if ($database->checkUploading())
        {
            if ($input_text == 'Загрузить Point')
            {
                if ($database->checkIssetLocation())
                {
                    $database->sendToModeration();
                    $text = "<b>Point успешно загружен.</b>\n\r\n\r";
                    $text .= "Скоро мы его опубликуем.\n\r";
                    $request->createReplyKeyboard(Keyboards::$replyDefault);
                }
                else
                {
                    $text = "<b>Чтобы загрузить Point нужно прикрепить место</b>.\n\r";
                    $keyboard[] = Keyboards::$inlineHowToAttachPlace;
                    $request->createInlineKeyboard($keyboard);
                }
                $request->sendMessage($text);
                exit();
            }
            if ($input_text == 'Удалить место')
            {
                $database->deletePhotoLocation();
                $text = "Место успешно удалено.";
                $keyboard[] = Keyboards::$replySendToModeration;
                $request->createReplyKeyboard($keyboard);
                $request->sendMessage($text);
                exit();
            }

            $address = urlencode($data->text);
            $url = GOOGLE_API_URL_FIND_PLACE.$address.GOOGLE_API_KEY;
            $update = json_decode(file_get_contents($url), true);

            $results = $update['results'];
            $status  = $update['status'];

            if ($status == 'OK')
            {
                $results_num = count($results);
                $text        = "<b>Найдено ".$results_num." ".$place."</b>\n\r\n\r";
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

                if ($results_num > 1){
                    $text .= "Какое из них прикрепить к загружаемой фотографии?\n\r\n\r";
                }
                else {
                    $text .= "Нажми на кнопку под нужным местом, чтобы его прикрепить.\n\r\n\r";
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

                $text  = "<b>Нет нужного места?</b>\n\r\n\r";
            }
            else
            {
                $text  = "<b>Я не смог найти «".$input_text."» 😥</b> \n\r\n\r";
            }

            $text .= "Попробуй:\n\r";
            $text .= "- Сформулировать запрос по-другому;\n\r";
            $text .= "- Самостоятельно прикрепить локацию.";
            $request->createInlineKeyboard([Keyboards::$inlineHowToAttachPlace]);
            $request->sendMessage($text);
            $request->hideKeyboard();
            exit();
        }

        if ($input_text == 'Загрузить Point' or $input_text == 'Удалить место')
        {
            $text = "Сперва нужно загрузить фотографию.";
            $request->createReplyKeyboard(Keyboards::$replyDefault);
            $request->sendMessage($text);
            exit();
        }

        $text = "Неизвестная команда, если возникли какие-либо вопросы, то пиши мне - @StPawlo";
        $request->createReplyKeyboard(Keyboards::$replyDefault);
        $request->sendMessage($text);
    }
}
