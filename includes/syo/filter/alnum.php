<?php
/**
 * Класс удаляет символы кроме букв и цифр
 */
class Syo_Filter_alnum extends Syo_Filter_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param='')
    {
        //Устанавливаем имя фильтра
        $this->_name='alnum';
        //Устанавливаем параметры для фильтра
        if (is_array($param))
        {
            $this->_param=$param;
        }        
    }

    /**
     * Выполняет фильтрацию.
     * @param variant $value - данная переменная пройдёт фильтрацию
     * @return variant
     */
    public function isFilter($value)
    {
        return preg_replace("/[^a-zA-ZА-Яа-яЁё0-9]/","",$value);
    }
}
?>