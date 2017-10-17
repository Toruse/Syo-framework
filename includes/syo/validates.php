<?php
/**
 * Syo_Validates - Класс для проверки полученных данных на корректность ввода.
 * 
 * Create 13.03.2014
 * Update 27.01.2015
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.2
 */
class Syo_Validates
{
    /**
     * Список с классами валидации.
     * @var array Syo_Validate_Abstract
     */
    protected $validation=array();
    /**
     * Список с произошедшими при проверке ошибками.
     * @var array 
     */
    protected $error=array();
    
    /**
     * Добавляем в список валидатор.
     * @param Syo_Validate_Abstract $valid
     * @return \Syo_Validates|boolean
     */
    public function addVerify($valid)
    {
        if (!empty($valid)) 
        {
            $this->validation[]=$valid;
            return $this;
        }
        return false;
    }
    
    /**
     * Выполняем валидацию.
     * @param variant $value - значение для проверки
     * @return boolean
     */
    public function isVerify($value)
    {
        $result=FALSE;
        foreach ($this->validation as $validate)
        {
            if ($validate->isVerify($value))
            {
                $this->addError($validate->getError());
                $result=TRUE;
            }
        }
        return $result;
    }
    
    /**
     * Добавляем возникшую ошибку в список.
     * @param array $error - описание ошибки
     */
    protected function addError($error)
    {
        if (is_array($error))
        {
            $this->error=array_merge($this->error,$error);
        }
        else
        {
            $this->error[]=$error;
        }
    }
    
    /**
     * Возвращает список с произошедшими ошибками.
     * @return array - список с произошедшими ошибками
     */
    public function getError()
    {
        return $this->error;
    }
}
?>