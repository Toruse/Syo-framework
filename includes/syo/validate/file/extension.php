<?php
/**
 * Класс проверяет на наличие определённого расширения файла.
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
class Syo_Validate_File_Extension extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param)
    {
        $this->_name='fileextension';
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
        if (is_array($value['name']))
        {
            foreach ($value['name'] as $key=>$name)
            {
                $part_parts=pathinfo($name);
                if (!empty($part_parts['extension']))
                    foreach ($this->_param['extension'] as $exten)
                        if (preg_match("#".$exten."#is",'.'.$part_parts['extension'])) 
                        {   
                            if (isset($this->_param['message'])) $this->addError($this->_param['message']);
                            return true;
                        }
            }
        }
        else 
        {
            $part_parts=pathinfo($value['name']);
            if (!empty($part_parts['extension']))
                foreach ($this->_param['extension'] as $exten)
                    if (preg_match("#".$exten."#is",'.'.$part_parts['extension'])) 
                    {   
                        if (isset($this->_param['message'])) $this->addError($this->_param['message']);
                        return true;
                    }
        }
        return false;
    }    
}
?>