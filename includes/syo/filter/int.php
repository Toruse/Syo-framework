<?php
/**
 * Класс преобразовывает строку в число
 */
class Syo_Filter_Int extends Syo_Filter_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param='')
    {
        //Устанавливаем имя фильтра
        $this->_name='int';
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
        return intval($value);
    }
}
?>