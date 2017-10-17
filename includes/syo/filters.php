<?php
/**
 * Класс объединяющий несколько фильтров.
 */
class Syo_Filters
{
    /**
     * Список фильтров.
     * @var array Syo_Filter_Abstract
     */
    protected $filter=array();
    
    /**
     * Добавляем в список фильтр.
     * @param Syo_Filter_Abstract $valid
     * @return \Syo_Filters|boolean
     */
    public function addFilter($filter)
    {
        if (!empty($filter)) 
        {
            $this->filter[]=$filter;
            return $this;
        }
        return false;
    }
    
    /**
     * Выполняем фильтрацию.
     * @param variant $value - значение для проверки
     * @return boolean
     */
    public function isFilter($value)
    {
        foreach ($this->filter as $filter)
        {
            $value=$filter->isFilter($value);
        }
        return $value;
    }    
}
?>