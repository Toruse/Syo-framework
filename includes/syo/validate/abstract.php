<?php
/**
 * Базовый класс для проверки корректности данных.
 */
abstract class Syo_Validate_Abstract
{
    /**
     * Переменная хранит настройки валидатора.
     * @var array 
     */
    protected $_param=array();
    /**
     * Содержит список сообщений.
     * @var array 
     */
    protected $_message=array();
    /**
     * Содержит список ошибок.
     * @var array 
     */
    protected $_error=array();
    /**
     * Имя валидатора.
     * @var string 
     */
    protected $_name='';
    
    /**
     * Метод проверки полученного значения.
     */
    abstract function isVerify($value);

    /**
     * Возвращает имя валидатора.
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Устанавливает имя валидатора.
     * @param string $name
     */
    public function setName($name='')
    {
        $this->_name=$name;
    }
    
    /**
     * Возвращает список сообщений.
     * @return array
     */
    public function getMessage()
    {
        return $this->_message;
    }
    
    /**
     * Добавляет в список сообщение.
     * @param string $str
     */
    public function addMessage($str)
    {
        $this->_message[]=$str;
    }

    /**
     * Возвращает список ошибок.
     * @return array
     */
    public function getError()
    {
        return $this->_error;
    }
    
    /**
     * Добавляет сообщение об ошибке в список.
     * @param string $str
     */
    public function addError($str)
    {
        $this->_error[]=$str;
    }
}  
?>
