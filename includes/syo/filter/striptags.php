<?php
/**
 * Класс убирает из строки HTML-теги.
 */
class Syo_Filter_StripTags extends Syo_Filter_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param='')
    {
        //Устанавливаем имя фильтра
        $this->_name='striptags';
        //Устанавливаем параметры для фильтра
        if (is_array($param))
        {
            $this->_param=$param;
        }        
    }

    /**
     * Выполняем фильтрацию.
     * @param variant $value - данная переменная пройдёт фильтрацию
     * @return variant
     */
    public function isFilter($value)
    {
        if (isset($this->_param['parameter']['tags']))
            $value=strip_tags($value,$this->_param['parameter']['tags']);
        else
            $value=strip_tags($value);            
        return $value;
    }
}
?>