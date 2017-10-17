<?php
  //Инициализируем константы
  require 'define.php';
    
  //Подключаем модуль с классом приложения
  require SITEPATH.LIBPATH.DIRSEP.COREPATH.DIRSEP.'application.php';
  
  //Инициализируем приложение
  $application=new Syo_Application('application');
    
  //Устанавливаем шаблоны роутеров
  require 'router.php';
 
  //Запускаем приложение
  $application->Run();
?>