<?php
/**
 * Класс проверяет на наличие букв и цифр в переменной
 */
class Syo_Validate_Alnum extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param="")
    {
        $this->_name='alnum';
        if (is_array($param))
        {
            $this->_param=$param;
        }
    }

    /**
     * Проверяет на наличие букв и цифр в переменной.
     * @param variant $value
     * @return boolean
     */
    public function isVerify($value)
    {
        $pattern="/^[a-zA-ZА-Яа-яЁё0-9]*$/i";
        if (!preg_match($pattern,$value))
        {
            if (isset($this->_param['message']['noalnum'])) $this->addError($this->_param['message']['noalnum']);
            return true;
        }
        return false;
    }
}
?>