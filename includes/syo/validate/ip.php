<?php
/**
 * Класс проверяет на корректность ip.
 */
class Syo_Validate_Ip extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param="")
    {
        $this->_name='ip';
        if (is_array($param))
        {
            $this->_param=$param;
        }
    }

    /**
     * Проверяет на корректность ip.
     * @param string $value
     * @return boolean
     */
    public function isVerify($value)
    {
        $pattern="/((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)/";
        if (!preg_match($pattern,$value))
        {
            if (isset($this->_param['message']['noip'])) $this->addError($this->_param['message']['noip']);
            return true;
        }
        return false;
    }
}
?>