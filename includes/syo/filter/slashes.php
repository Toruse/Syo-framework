<?php
/**
 * Класс экранирует кавычки.
 */
class Syo_Filter_Slashes extends Syo_Filter_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param='')
    {
        //Устанавливаем имя фильтра
        $this->_name='slashes';
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
        if (!get_magic_quotes_gpc())
        {
            $value=addslashes($value);
        }
        return $value;
    }
}
?>