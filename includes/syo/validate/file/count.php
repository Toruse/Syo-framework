<?php
/**
 * Класс для проверки количества загружаемых файлов.
 * 
 * Create 15.09.2014
 * Update 15.09.2014
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.0
 *  
 * @package syo
 * @subpackage validate
 */
class Syo_Validate_File_Count extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param)
    {
        $this->_name='file_count';
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
        if ($this->isMin($value) || $this->isMax($value) || $this->isCount($value))
        {
            return true;
        }
        return false;
    }
    
    /**
     * Проверяет на минимальное количество файлов.
     * @param variant $value
     * @return boolean
     */
    private function isMin($value)
    {
        if (is_array($value['name']))
        {
            if (isset($this->_param['parameter']['min']) && $this->_param['parameter']['min']>count($value['name']))
            {
                if (isset($this->_param['message']['min'])) $this->addError($this->_param['message']['min']);
                return true;
            }
        }
        else 
        {
            if (isset($this->_param['parameter']['min']) && $this->_param['parameter']['min']>1)
            {
                if (isset($this->_param['message']['min'])) $this->addError($this->_param['message']['min']);
                return true;
            }            
        }
        return false;
    }

    /**
     * Проверяет на максимальное количество файлов.
     * @param variant $value
     * @return boolean
     */
    private function isMax($value)
    {
        if (is_array($value['name']))
        {
            if (isset($this->_param['parameter']['max']) && $this->_param['parameter']['max']<count($value['name']))
            {
                if (isset($this->_param['message']['max'])) $this->addError($this->_param['message']['max']);
                return true;
            }
        }
        else 
        {
            if (isset($this->_param['parameter']['max']) && $this->_param['parameter']['max']<1)
            {
                if (isset($this->_param['message']['max'])) $this->addError($this->_param['message']['max']);
                return true;
            }            
        }
        return false;
    }
    
    /**
     * Проверяет на определённое количество файлов.
     * @param variant $value
     * @return boolean
     */
    private function isCount($value)
    {
        if (is_array($value['name']))
        {
            if (isset($this->_param['parameter']['count']) && $this->_param['parameter']['count']!=count($value['name']))
            {
                if (isset($this->_param['message']['count'])) $this->addError($this->_param['message']['count']);
                return true;
            }
        }
        else 
        {
            if (isset($this->_param['parameter']['count']) && $this->_param['parameter']['count']!=1)
            {
                if (isset($this->_param['message']['count'])) $this->addError($this->_param['message']['count']);
                return true;
            }            
        }
        return false;
    }
}
?>