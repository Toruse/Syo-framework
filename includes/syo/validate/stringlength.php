<?php
/**
 * Класс для проверки длинны строки 
 */
class Syo_Validate_StringLength extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param)
    {
        $this->_name='stringlength';
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
        if ($this->isMin($value) || $this->isMax($value) || $this->isLength($value))
        {
            return true;
        }
        return false;
    }
    
    /**
     * Проверяет на минимум.
     * @param string $value
     * @return boolean
     */
    private function isMin($value)
    {
        if (isset($this->_param['parameter']['min']) && $this->_param['parameter']['min']>strlen($value))
        {
            if (isset($this->_param['message']['min'])) $this->addError($this->_param['message']['min']);
            return true;
        }        
        return false;
    }

    /**
     * Проверяем на максимум
     * @param string $value
     * @return boolean
     */
    private function isMax($value)
    {
        if (isset($this->_param['parameter']['max']) && $this->_param['parameter']['max']<strlen($value))
        {
            if (isset($this->_param['message']['max'])) $this->addError($this->_param['message']['max']);
            return true;
        }        
        return false;
    }

    /**
     * Проверяем длину строки
     * @param string $value
     * @return boolean
     */
    private function isLength($value)
    {
        if (isset($this->_param['parameter']['length']) && $this->_param['parameter']['length']!=strlen($value))
        {
            if (isset($this->_param['message']['length'])) $this->addError($this->_param['message']['length']);
            return true;
        }        
        return false;
    }
}
?>