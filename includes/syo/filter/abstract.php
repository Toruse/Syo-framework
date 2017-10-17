<?php
/**
 * Базовый класс, выполняющий фильтрацию данных.
 */
abstract class Syo_Filter_Abstract
{
    /**
     * Хранить настройки для фильтрации
     * @var array
     */
    protected $_param=array();
    /**
     * Хранит имя фильтра
     * @var string
     */
    protected $_name='';
    
    /**
     * Метод выполняющий фильтрацию данных.
     */
    abstract function isFilter($value);
    
    /**
     * Возвращает название фильтра
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * Устанавливает название фильтра
     * @param string $name
     */
    public function setName($name='')
    {
        $this->_name=$name;
    }
}  
?>
