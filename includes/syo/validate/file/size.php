<?php
/**
 * Класс проверяет файл на допустимый размер.
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
class Syo_Validate_File_Size extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param)
    {
        $this->_name='file_size';
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
        if (is_array($value['size']))
        {
            foreach ($value['size'] as $key=>$size)
                if ($this->isMin($size) || $this->isMax($size))
                    return true;
        }
        else
        {
            if ($this->isMin($value['size']) || $this->isMax($value['size']))
                return true;
        }
        return false;
    }
    
    /**
     * Проверяет файл на минимальный размер.
     * @param variant $value
     * @return boolean
     */
    private function isMin($value)
    {
        if (isset($this->_param['parameter']['min']) && $this->_param['parameter']['min']>$value)
        {
            if (isset($this->_param['message']['min'])) $this->addError($this->_param['message']['min']);
            return true;
        }        
        return false;
    }

    /**
     * Проверяет файл на максимальный размер.
     * @param variant $value
     * @return boolean
     */
    private function isMax($value)
    {
        if (isset($this->_param['parameter']['max']) && $this->_param['parameter']['max']<$value)
        {
            if (isset($this->_param['message']['max'])) $this->addError($this->_param['message']['max']);
            return true;
        }        
        return false;
    }
}
?>