<?php
/**
 * Класс проверяет, является ли переменная целым числом и больше нуля.
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
class Syo_Validate_UInt extends Syo_Validate_Abstract
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
     * Проверяет, является ли переменная целым числом и больше нуля.
     * @param variant $value
     * @return boolean
     */
    public function isVerify($value)
    {
        $options=array(
            'options'=>array(
                'min_range'=>0,
            )
        );
        if (filter_var($value,FILTER_VALIDATE_INT,$options)===FALSE)
        {
            if (isset($this->_param['message']['nouint'])) $this->addError($this->_param['message']['nouint']);
            return true;
        }
        else 
        {
            return false;
        }
    }
}
?>