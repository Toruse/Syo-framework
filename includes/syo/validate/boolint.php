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
class Syo_Validate_BoolInt extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param="")
    {
        $this->_name='boolint';
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
        $options=array(
            'options'=>array(
                'min_range'=>0,
                'max_range'=>1,
            )
        );
        if (filter_var($value,FILTER_VALIDATE_INT,$options)===FALSE)
        {
            if (isset($this->_param['message']['noboolint'])) $this->addError($this->_param['message']['noboolint']);
            return true;            
        }
        else 
        {
            return false;
        }
    }
}
?>