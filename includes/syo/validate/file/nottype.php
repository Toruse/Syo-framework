<?php
/**
 * Класс проверяет допустимый тип файла 
 * 
 * Create 11.09.2014
 * Update 11.09.2014
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.0
 *  
 * @package syo
 * @subpackage validate
 */
class Syo_Validate_File_NotType extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param)
    {
        $this->_name='file_nottype';
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
        $result=true;
        if (is_array($value['type']))
        {
            foreach ($value['type'] as $key=>$type)
                foreach ($this->_param['type'] as $ptype)
                    if (preg_match("#".$ptype."#is",$type)) 
                        $result=false;
        }
        else 
        {
            foreach ($this->_param['type'] as $ptype)
                if (preg_match("#".$ptype."#is",$value['type'])) 
                    $result=false;
        }
        if ($result && isset($this->_param['message'])) $this->addError($this->_param['message']);
        return $result;
    }    
}
?>