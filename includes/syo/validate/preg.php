<?php
/**
 * Класс для проверки значения на соответствие с регулярным выражением.
 */
class Syo_Validate_Preg extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param="")
    {
        $this->_name='preg';
        if (is_array($param))
        {
            $this->_param=$param;
        }
    }

    /**
     * Проверяет значения на соответствие с регулярным выражением.
     * @param variant $value
     * @return boolean
     */
    public function isVerify($value)
    {
        if ((!isset($this->_param['parameter']['pattern'])) || (!preg_match($this->_param['parameter']['pattern'],$value)))
        {
            if (isset($this->_param['message']['nopreg'])) $this->addError($this->_param['message']['nopreg']);
            return true;
        }
        return false;
    }
}
?>