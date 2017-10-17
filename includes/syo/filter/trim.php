<?php
/**
 * Класс убирает пробелы из начала и конца строки.
 */
class Syo_Filter_Trim extends Syo_Filter_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param='')
    {
        //Устанавливаем имя фильтра
        $this->_name='trim';
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
        return trim($value);
    }
}
?>