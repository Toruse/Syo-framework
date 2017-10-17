<?php
/**
 * Класс для работы с сессиями.
 */
class Syo_Session
{
    /**
     * Одиночка.
     * @var Syo_Session 
     */
    protected static $instance;

    protected $_params=array(
        'path'=>null,
    );
    
    /**
     * Гарантируем, что у класса есть только один экземпляр, и предоставляет к нему глобальную точку доступа. 
     * @return Syo_Session
     */
    public static function getInstance()
    {
        if (self::$instance===null)
        {
            self::$instance=new Syo_Session();
        }
        return self::$instance;
    }
    
    /**
     * Конструктор.
     */
    protected function __construct()
    {
        //Запускаем сессию.
       session_start();
    }
    
    /**
     * Перезагружаем метод возврата значения переменной.
     * @param variant $key
     * @return variant
     */
    public function __get($key)
    {
        if (isset($_SESSION[$this->getKey($key)])==false)
        {
                return null;
        }
        return $_SESSION[$this->getKey($key)];
    }

    /**
     * Перезагружаем метод установки переменных.
     * @param variant $key
     * @param variant $value
     * @return boolean
     */
    public function __set($key,$value)
    {
        $_SESSION[$this->getKey($key)]=$value;
        return true;
    }
    
    /**
     * Перезагружаем метод уничтожения переменной класса.
     * @param variant $key
     */
    public function __unset($key) 
    {
        unset($_SESSION[$this->getKey($key)]);
    }
    
    /**
     * Перезагружаем метод проверки на существование переменной класса.
     * @param variant $key
     * @return variant
     */
    public function __isset($key) 
    {
        return isset($_SESSION[$this->getKey($key)]);
    }
    
    /**
     * Возвращает массив сессии.
     */
    public function getSession()
    {
        return $_SESSION;
    }
    
    /**
     * Устанавливает группировку переменных сессии.
     * @param string $path
     */
    public function setPath($path=null)
    {
        $this->_params['path']=$path;
    }
    
    /**
     * Возвращает название группы переменных данной сессии.
     * @return string
     */
    public function getPath()
    {
        return $this->_params['path'];
    }
    
    /**
     * Возвращает полное имя переменной в сессии.
     * @param string $key
     * @return string
     */
    protected function getKey($key)
    {
        return ($this->getPath()==null?$key:$this->getPath().'.'.$key);
    }
    
    /**
     * Удаляет все переменные из сессии. 
     * @return boolean
     */
    public function removeSession()
    {
        foreach ($_SESSION as $key=>$value)
        {
            unset($_SESSION[$key]);
        }
        return true;
    }
}

?>