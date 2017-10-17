<?php
/**
 * Класс преобразования даты в указный формат.
 */
class Syo_Filter_Date extends Syo_Filter_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param='')
    {
        //Устанавливаем имя фильтра
        $this->_name='date';
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
        if (isset($this->_param['parameter']['format']))
            $value=date($this->_param['parameter']['format'],strtotime($value));
        else
            $value=date('Y-m-d H:i:s',strtotime($value));
        return $value;
    }
}
?>