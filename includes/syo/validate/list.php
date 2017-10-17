<?php
/**
 * Класс проверяет на наличие значения в указанном списке.
 */
class Syo_Validate_List extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param="")
    {
        $this->_name='list';
        if (is_array($param))
        {
            $this->_param=$param;
        }
    }

    /**
     * Выполняет поиск значения в списке.
     * @param string $value
     * @return boolean
     */
    public function isVerify($value)
    {
        if (!in_array($value,$this->_param['parameter']['list']))
        {
            if (isset($this->_param['message']['nolist'])) $this->addError($this->_param['message']['nolist']);
            return true;
        }
        return false;
    }
}
?>