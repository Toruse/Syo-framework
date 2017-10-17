<?php
/**
 * Класс проверяет на пустоту имя файла
 * 
 * Create 13.03.2014
 * Update 13.09.2014
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.0
 *  
 * @package syo
 * @subpackage validate
 */
class Syo_Validate_File_NoEmpty extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param=NULL)
    {
        $this->_name='noempty';
        if (is_array($param))
        {
            $this->_param=$param;
        }
    }

    /**
     * Выполняет проверку.
     * @param variant $value
     * @return boolean
     */
    public function isVerify($value)
    {
        if (empty($value['name']))
        {
            if (isset($this->_param['message'])) $this->addError($this->_param['message']);
            return true;
        }
        return false;
    }
}
?>