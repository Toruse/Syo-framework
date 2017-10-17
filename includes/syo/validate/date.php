<?php
/**
 * Класс проверяет дату на корректность.
 */
class Syo_Validate_Date extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param="")
    {
        $this->_name='date';
        if (is_array($param))
        {
            $this->_param=$param;
        }
    }

    /**
     * Проверяет дату.
     * @param variant $value
     * @return boolean
     */
    public function isVerify($value)
    {
        if (strtotime($value)===FALSE)
        {
            if (isset($this->_param['message']['nodate'])) $this->addError($this->_param['message']['nodate']);
            return true;
        }
        return false;
    }
}
?>