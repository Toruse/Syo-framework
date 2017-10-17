<?php
/**
 * Глобальный конфиг.
 */
return array
(
    'application'=>array
        (
            //Режим вывода ошибок
            'display_errors'=>E_ALL,
            //В каком виде выводить ошибки
            'view_errors'=>'TXT',//TXT,HTML
            //Минимальная версия PHP
            'phpversion'=>'5.2',
            //Язык приложение по умолчанию
            'languages'=>'ru',
            //Адрес сайта
            'httphost'=>'http://demo.loc',
            //Параметры cookie
            'cookie'=>array(
                'expire'=>time()+86400,
                'path'=>'/',
                'domain'=>null,
                'secure'=>null
            ),
            //Параметры для кэша
            'cache'=>array(
                'name'=>'default',
                'path'=>SITEPATH.'cache/',
                'extension'=>'.cache',
            ),
            //Настройки для модуля mailer
            'phpmailer'=>array(
                'host'=>'',
                'port'=>587,
                'smtpsecure'=>'',
                'smtpauth'=>TRUE,
                'username'=>"",
                'password'=>"",
                'path'=>'templates/mail/',
            ),
            //Настройки для сохранения логов
            'log'=>array(
                'default'=>array(
                    //Имя класса лога
                    'class'=>'Syo_Log_File',
                    //Путь к каталогу для записи логов
                    'path'=>SITEPATH.'log/',
                    //Имя файла для записи логов
                    'filename'=>'app.log',
                    //Какие сообщения записывать
                    'write'=>Syo_Log_Abstract::LOG_ALL,
                    //Формат даты
                    'formatdate'=>'Y-m-d G:i:s.u'
                ),
//                'db'=>array(
//                    //Имя класса лога
//                    'class'=>'Syo_Log_Db',
//                    //Имя таблицы логов в базе данных
//                    'table'=>'log',
//                    //Какие сообщения записывать
//                    'write'=>Syo_Log_Abstract::LOG_ALL,
//                ),
//                'mail'=>array(
//                    //Имя класса лога
//                    'class'=>'Module_Log_Mail',
//                    //Имя email на который отправляются сообщения логов.
//                    'email'=>'typikp@mail.ru',
//                    //Тема письма
//                    'subject'=>'Logs Wordkey.org.ua',
//                    //Имя файла шаблона письма
//                    'template'=>'log',
//                    //Какие сообщения записывать
//                    'write'=>Syo_Log_Abstract::LOG_ALL,
//                    //Формат даты
//                    'formatdate'=>'Y-m-d G:i:s.u'
//                )
            )
        )
);
?>