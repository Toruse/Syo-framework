<?php
/**
 * Класс проверяет на корректность email.
 */
class Syo_Validate_Email extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param="")
    {
        $this->_name='email';
        if (is_array($param))
        {
            $this->_param=$param;
        }
    }

    /**
     * Проверяет на корректность email.
     * @param string $value
     * @return boolean
     */
    public function isVerify($value)
    {
        if (function_exists('filter_var')) 
        {
            if (filter_var($value,FILTER_VALIDATE_EMAIL)===FALSE)
            {
                if (isset($this->_param['message']['noemail'])) $this->addError($this->_param['message']['noemail']);
                return true;
            }
        } 
        else 
        {
            $pattern="/^([a-z0-9_\.-]+)@([a-z0-9_\.-]+)\.([a-z\.]{2,6})$/";
            if (!preg_match($pattern,$value))
            {
                if (isset($this->_param['message']['noemail'])) $this->addError($this->_param['message']['noemail']);
                return true;
            }            
        }
        return false;
    }
}
?>