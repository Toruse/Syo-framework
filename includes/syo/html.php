<?php
/**
 * Класс предназначен для генерации элементов HTML.
 */
class Syo_Html
{  
    /*
     * Хранит название тега.
     * @var string
     */
    private $tag_name;
    
    /**
     * Указываем, закрывать тег или нет.
     * @var boolean 
     */
    private $tag_closing=false;
    
    /**
     * Список тегов, которые нужно закрыть.
     * @var array 
     */
    private $closing_list=array('input','img','hr','br','meta','link');
    
    /**
     * Переменная хранит атрибуты тега.
     * @var array 
     */
    private $attributes=array();
  
    /**
     * Конструктор.
     * @param string $tag - имя тега
     * @param array $attrs - атрибуты тега
     * @param boolean $closing - закрыть тега или нет
     */
    public function __construct($tag,$attrs=array(),$closing=null)
    {
        $this->tag_name=$tag;
        //Определяем, закрывается тел или нет
        if (is_null($closing))
            $this->tag_closing=in_array($tag,$this->closing_list);
        else
            $this->tag_closing=$closing;
        //Сохраняем атрибуты
        $attrs['text']=(empty($attrs['text']))?'':$attrs['text'];
        $this->attributes=$attrs;
    }
    
    /**
     * Выполняет построение тега, и выводит его виде строки.
     * @return string
     */
    public function __toString()
    {
        return $this->render();
    }
  
    /**
     * Добавляет атрибут или несколько атрибутов к тегу.
     * @param variant $attr
     * @param string $value
     */
    public function setAttribute($attr,$value=null)
    {
        if (is_array($attr))
            $this->attributes=array_merge($this->attributes,$attr);
        else
            $this->attributes=array_merge($this->attributes,array($attr=>$value));
    }
    
    /**
     * Удаляет указанный атрибут из списка атрибутов тега.
     * @param string $name
     */
    public function removeAttribute($name)
    {
        unset($this->_attributes[$name]);
    }
    
    /**
     * Устанавливает содержимое тега
     * @param string $text
     */
    public function setText($text="")
    {
        $this->attributes['text']=$text;
    }
    
    /**
     * Добавляет данные к содержимому тега.
     * @param string $text
     */
    public function addText($text="")
    {
        $this->attributes['text'].=$text;
    }

    /**
     * Выполняем построение тега.
     * @return string
     */
    public function render()
    {
        $output='<'.$this->tag_name;
        //Добавляем атрибуты.
        foreach ($this->attributes as $attr=>$value)
        {
            if ($attr=='text') continue;
            $output.=' '.$attr.'="'.$value.'"';
        }
        // Закрываем тег
        if ($this->tag_closing)
            $output.='/>';
        else
            $output.='>'.$this->attributes['text'].'</'.$this->tag_name.'>';
        return $output;
    }
  
    /**
     * Клонирует объект.
     * @return \Syo_Html
     */
    public function _clone()
    {
        return new Syo_Html($this->tag_name,$this->attributes,$this->tag_closing);
    }
  
    /**
     * Проверяет, является ли объект Syo_Html
     * @param Syo_Html $obj
     * @return boolean
     */
    private function check_class($obj)
    {
        return (@get_class($obj)==__class__);
    }
  
    /**
     * Добавляет новый тег к содержанию данного тега.
     * @return \Syo_Html
     */
    public function addTag()
    {
        //Получаем теги, указанные в функции
        $elems = func_get_args();
        //Перебираем теги
        foreach ($elems as $tag)
        {
            if ($this->check_class($tag))
            {
                //Добавляем
                $this->attributes['text'].=$tag->render()."\n";
            }
        }
        return $this;
    }

    /**
     * Добавляет новый тег к началу содержимого данного тега.
     * @return \Syo_Html
     */
    public function addTagBefore()
    {
        //Получаем теги, указанные в функции
        $elems=func_get_args();
        //Разворачиваем массив
        $elems=array_reverse($elems);
        //Перебираем теги 
        foreach ($elems as $tag)
        {
            if ($this->check_class($tag))
            {
                //Добавляем
                $this->attributes['text']=$tag->render().$this->attributes['text'];
            }
        }
        return $this;
    }
}
?>
