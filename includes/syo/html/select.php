<?php
/**
 * Класс для работы с тегом select.
 */
class Syo_Html_Select
{
    /**
     * Хранит объект для создания HTML тега.
     * @var Syo_Html 
     */
    private $tag=null;
    
    /**
     * Конструктор.
     * @param array $attrs - атрибуты тега
     */
    public function __construct($attrs=array())
    {
        //Создаём тег select
        $this->tag=new Syo_Html('select',$attrs);
        $this->tag->setText();
    }
    
    /**
     * Добавляет атрибут или несколько атрибутов к тегу.
     * @param variant $attr - атрибуты тега
     * @param string $value - значение атрибута
     */
    public function setAttribute($attr,$value=null)
    {
        $this->tag->setAttribute($attr,$value);
    }
    
    /**
     * Добавляет тег option.
     * @param array $attrs - атрибуты тега
     */
    public function addOption($attrs=array())
    {
        //Создаём тег option
        $option=new Syo_Html('option',$attrs);
        //Добавляем тег в select
        $this->tag->addTag($option);
    }
        
    /**
     * Добавляет в select теги option на основе переданного массива
     * @param array $list - массив элементов
     * @param array $select - список выбранных элементов
     * @param array $disabled - список отключённых элементов
     */
    public function createList($list=array(),$select=array(),$disabled=array())
    {
        //Перебираем массив
        foreach ($list as $tag)
        {
            //генерируем атрибуты для тега option
            $attrs=array("value"=>$tag['id'],"text"=>$tag['name']);
            if (in_array($tag['id'],$select))
                $attrs["selected"]="selected";
            if (in_array($tag['id'],$disabled))
                $attrs["disabled"]="disabled";
            //Добавляем тег option
            $this->addOption($attrs);
        }
    }
    
    /**
     * Перегружаем метод __toString
     * @return string
     */
    public function __toString()
    {
        return $this->tag->render();
    }
}
?>