<?php
/**
 * Класс для проверки переменной на пустоту.
 */
class Syo_Validate_NoEmpty extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param="")
    {
        $this->_name='noempty';
        if (is_array($param))
        {
            $this->_param=$param;
        }
    }

    /**
     * Проверяет переменную на пустоту.
     * @param variant $value
     * @return boolean
     */
    public function isVerify($value)
    {
        if (empty($value))
        {
            if (isset($this->_param['message']['empty'])) $this->addError($this->_param['message']['empty']);
            return true;
        }
        return false;
    }
}
?>