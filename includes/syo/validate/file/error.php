<?php
/**
 * Класс для проверки были ли допущены ошибки при загрузке файлов.
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
class Syo_Validate_File_Error extends Syo_Validate_Abstract
{
    /**
     * Конструктор.
     * @param array $param
     */
    public function __construct($param=NULL)
    {
        $this->_name='file_error';
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
            foreach ($value['error'] as $key=>$error)
                if ($this->isError($error))
                    return TRUE;
        }
        else 
        {
            return $this->isError($value['error']);
        }
        return false;
    }
    
    /**
     * Проверяет, были ли допущены ошибки при загрузке файлов.
     * @param integer $error - код ошибки
     * @return boolean
     */
    protected function isError($error)
    {
        switch ($error) 
        {
            case UPLOAD_ERR_OK:
                return false;
            break;
            case UPLOAD_ERR_NO_FILE:
                if (isset($this->_param['message'][UPLOAD_ERR_NO_FILE])) $this->addError($this->_param['message'][UPLOAD_ERR_NO_FILE]);
                return true;
            case UPLOAD_ERR_INI_SIZE:
                if (isset($this->_param['message'][UPLOAD_ERR_INI_SIZE])) $this->addError($this->_param['message'][UPLOAD_ERR_INI_SIZE]);
                return true;
            break;
            case UPLOAD_ERR_FORM_SIZE:
                if (isset($this->_param['message'][UPLOAD_ERR_FORM_SIZE])) $this->addError($this->_param['message'][UPLOAD_ERR_FORM_SIZE]);
                return true;
            break;
            default :
                return false;
        }                   
    }
}
?>