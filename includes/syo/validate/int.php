<?php
/**
 * Класс проверяет, является ли переменная целым числом.
 * 
 * Create 13.03.2014
 * Update 13.03.2014
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.0
 * 
 * @package syo
 * @subpackage validate
 */
class Syo_Validate_Int extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param="")
    {
        $this->_name='int';
        if (is_array($param))
        {
            $this->_param=$param;
        }
    }

    /**
     * Проверяет, является ли переменная целым числом.
     * @param variant $value
     * @return boolean
     */
    public function isVerify($value)
    {
        if (filter_var($value,FILTER_VALIDATE_INT)===FALSE)
        {
            if (isset($this->_param['message']['noint'])) $this->addError($this->_param['message']['noint']);
            return true;
        }
        else 
        {
            return false;
        }
    }
}
?>