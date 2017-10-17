<?php
/**
 * Класс для работы с библиотекой PHPMailer.
 * 
 * Create 27.01.2015
 * Update 27.01.2015
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.0
 * 
 * @package module
 * @subpackage mailer
 */
//Подключаем PHPMailer
require_once __DIR__.DIRSEP.'phpmailer'.DIRSEP.'PHPMailerAutoload.php';

class Module_Mailer_Native extends PHPMailer
{
    /**
    * Путь к папке с шаблонами писем.
    * @var string
    */
    private $path = 'templates/mail/';
    
    /**
     * Хранит значения переменных для шаблона.
     * @var array 
     */
    private $vars=array();
    
    /**
     * Сетер класса.
     * @param index $key
     * @param variant $var
     * @return boolean
     */
    public function __set($key,$var)
    {
        $this->vars[$key]=$var;
        return true;
    }
    
    /**
     * Гетер класса.
     * @param index $key
     * @return variant
     */
    public function __get($key)
    {
        return $this->vars[$key];
    }

    /**
     * Unset класса.
     * @param index $key
     */
    public function __unset($key) 
    {
        unset($this->vars[$key]);
    }
    
    /**
     * Isset класса. 
     * @param index $key
     * @return boolean
     */
    public function __isset($key) 
    {
        return isset($this->vars[$key]);
    }
    
    /**
     * Загружает конфигурацию из конфигов.
     * @param array $config
     */
    public function setFromConfig($config=NULL)
    {
        if (is_null($config))
        {
            //Получаем конфигурацию приложения
            $config=Syo_Registry::getInstance()->get('config');
            //Если существуют настройки для Mailer, передаём их классу
            if (isset($config['application']['phpmailer']['host'])) $this->Host=$config['application']['phpmailer']['host'];
            if (isset($config['application']['phpmailer']['port'])) $this->Port=$config['application']['phpmailer']['port'];
            if (isset($config['application']['phpmailer']['smtpsecure'])) $this->SMTPSecure=$config['application']['phpmailer']['smtpsecure'];
            if (isset($config['application']['phpmailer']['smtpauth'])) $this->SMTPAuth=$config['application']['phpmailer']['smtpauth'];
            if (isset($config['application']['phpmailer']['username'])) $this->Username=$config['application']['phpmailer']['username'];
            if (isset($config['application']['phpmailer']['password'])) $this->Password=$config['application']['phpmailer']['password'];
            if (isset($config['application']['phpmailer']['path'])) $this->path=$config['application']['phpmailer']['path'];
        }
        else
        {
            //Устанавливаем указанные параметры из $config
            if (isset($config['host'])) $this->Host=$config['host'];
            if (isset($config['port'])) $this->Port=$config['port'];
            if (isset($config['smtpsecure'])) $this->SMTPSecure=$config['smtpsecure'];
            if (isset($config['smtpauth'])) $this->SMTPAuth=$config['smtpauth'];
            if (isset($config['username'])) $this->Username=$config['username'];
            if (isset($config['password'])) $this->Password=$config['password'];
        }
    }
    
    /**
     * Создать сообщение в формате HTML строки основанного на указанном шаблоне.
     * @param string $template - имя шаблона
     * @param string $basedir - базовый каталог
     * @param boolean $advanced - cледует ли использовать внутренний HTML в текст конвертер
     * @return string - cгенерированное сообщение
     */
    public function msgTemplate($template,$basedir='',$advanced=false)
    {
        $tpl=new Syo_Template();
        $tpl->setPath($this->path);
        $tpl->setTemplate($template);
        foreach ($this->vars as $name=>$value) 
            $tpl->set($name,$value);
        return $this->msgHTML((string)$tpl,$basedir,$advanced);
    }
}
?>