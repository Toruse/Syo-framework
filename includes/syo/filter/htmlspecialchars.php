<?php
/**
 * Класс преобразует специальные символы в HTML сущности .
 */
class Syo_Filter_Htmlspecialchars extends Syo_Filter_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param='')
    {
        //Устанавливаем имя фильтра
        $this->_name='htmlspecialchars';
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
        return htmlspecialchars($value,ENT_QUOTES);
    }
}
?>