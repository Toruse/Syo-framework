<?php
/**
 * Класс проверяет получен ли файл методом POST.
 * 
 * Create 09.09.2014
 * Update 09.09.2014
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.0
 *  
 * @package syo
 * @subpackage validate
 */
class Syo_Validate_File_Post extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param=NULL)
    {
        $this->_name='file_post';
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
        if (is_array($value['tmp_name']))
        {
            foreach ($value['tmp_name'] as $key=>$name)
                if (!is_uploaded_file($name))
                {
                    if (isset($this->_param['message'])) $this->addError($this->_param['message']);
                    return true;
                }
        }
        else 
        {
            if (!is_uploaded_file($value['tmp_name']))
            {
                if (isset($this->_param['message'])) $this->addError($this->_param['message']);
                return true;
            }            
        }
        return false;
    }
}
?>