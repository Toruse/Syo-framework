<?php
/**
 * Класс проверяет на корректность url.
 */
class Syo_Validate_Url extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param="")
    {
        $this->_name='url';
        if (is_array($param))
        {
            $this->_param=$param;
        }
    }

    /**
     * Проверяет на корректность url.
     * @param string $value
     * @return boolean
     */
    public function isVerify($value)
    {
        $pattern="/^((((https?|ftps?|gopher|telnet|nntp):\/\/)|(mailto:|news:))(%[0-9A-Fa-f]{2}|[-()_.!~*';\/?:@&=+$,A-Za-z0-9])+)([).!';\/?:,][[:blank:]])?$/i";
        if (!preg_match($pattern,$value))
        {
            if (isset($this->_param['message']['nourl'])) $this->addError($this->_param['message']['nourl']);
            return true;
        }
        return false;
    }
}
?>