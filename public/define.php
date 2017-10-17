<?php  
  //указываем констаныт
  /**
   * Тип разделителя.
   */
  define('DIRSEP', DIRECTORY_SEPARATOR);
  
  /**
   * Узнаём путь до файлов сайта
   */
  $site_path=realpath(dirname(__FILE__).DIRSEP.'..'.DIRSEP).DIRSEP;
  //$site_path=realpath(dirname(__FILE__).DIRSEP).DIRSEP;
  
  /**
   * Узнаём путь к каталогу сайта.
   */
  define ('SITEPATH',$site_path);
  
  /**
   * Указываем путь к библиотеке
   */
  define ('LIBPATH','includes');

  /**
   * Указываем путь к ядру framework
   */
  define ('COREPATH','syo');
  
  /**
   * Указываем каталог с приложением.
   */
  define ('APPPATH','app');

  /**
   * Каталог аддона центральной страницы.
   */
  define ('HOMEPATH','home');

  /**
   * Путь к административной части сайта.
   */
  define ('ROUTEADMIN','admin');

  /**
   * Каталог центральной страницы.
   */
  define ('PATHPUBLIC','public_html');
  
  /**
   * Каталог для хранения пользовательских файлов.
   */
  define ('PATHFILE','files');

?>