<?php
/**
 * Класс предназначен для генерации HTML-кода меню.
 */
class Module_Menu_Html
{
    /**
     * Список меню.
     * @var array 
     */
    protected $data=array();
    
    /**
     * Список атрибутов HTML для корня меню.
     * @var array 
     */
    protected $attributes=array();
    
    /**
     * Тип списка
     * @var string 
     */
    protected $typeTag='ul';

    /**
     * Устанавливает HTML-атрибуты.
     * @param array $key or string - имя атрибута или список атрибутов с параметрами виде массива.
     * @param string $param - значение атрибута.
     */
    public function setAttributes($key,$param=NULL)
    {
        if (is_array($key))
        {
            $this->attributes=array_merge($this->attributes,$key);
        }
        else
        {
            $this->attributes[$key]=$param;
        }   
    }
    
    /**
     * Устанавливает список меню.
     * @param array $listMenu - список меню
     */
    public function setMenuData($listMenu=array())
    {
        $this->data=$listMenu;        
    }
        
    /**
     * Генерация HTML меню.
     * @return string
     */
    public function render()
    {
        //Задаём корень UL , и указываем атрибуты
        $output='<'.$this->typeTag.$this->getStrAttributes().'>';
        $prev_level=0;
        //Получаем уровень элемента меню
        $levels=$this->getLevels();
        //Выполняем если список меню не пустой.
        if ($counts=count($this->data))
        {
            //Перебираем меню, и формируем HTML
            $i=0;
            $open_ul=0;
            foreach ($this->data as $item)
            {
                $i++;
                //Закрываем тег если разница в уровнях 1
                $item_level=$item['level']-$levels['min'];
                if ($i!=1 && $prev_level==$item_level)
                {
                    $output.='</li>';
                }
                //Закрываем тег если разница в уровнях больше 1
                if ($item_level<$prev_level)
                {
                    $difference=$prev_level-$item_level;
                    $output.=$this->tags('</'.$this->typeTag.'></li>',$difference);
                    $open_ul=$open_ul-$difference;
                }
                //Открываем тег если есть разница в уровнях
                if ($item_level>$prev_level)
                {
                    $output.=$this->tags('<'.$this->typeTag.'>');
                    ++$open_ul;
                }
                //Выводим элемент меню виде HTML
                $output.=$this->item($item);
                //В конце закрываем открытые теги.
                if ($counts==$i)
                {
                    if ($open_ul>1)
                    {
                        $output.=$this->tags('</'.$this->typeTag.'></li>',$open_ul-1);
                        $output.=$this->tags('</'.$this->typeTag.'>');
                    }
                    elseif ($open_ul==1)
                    {
                        $output.=$this->tags('</li></'.$this->typeTag.'>');
                    }
                    else
                    {
                        $output.=$this->tags('</li>');
                    }
                }
                $prev_level=$item_level;
            }
        }
        $output.='</'.$this->typeTag.'>';
        return $output;
    }

    /**
     * Выводим элемент меню виде HTML.
     * @param array $iteration_data - элемент меню
     * @return string
     */
    protected function item($iteration_data)
    {
        return '<li>'.$iteration_data['name'];
    }

    /**
     * Определяет максимальное и минимальное значение уровня в меню.
     * @return array
     */
    protected function getLevels()
    {
        $result=array('min'=>PHP_INT_MAX,'max'=>0);
        if (count($this->data))
        {
            foreach ($this->data as $item)
            {
                if ($result['min']>$item['level'])
                {
                    $result['min']=$item['level'];
                }
                if ($result['max']<$item['level'])
                {
                    $result['max']=$item['level'];
                }
            }
        }
        return $result;
    }

    /**
     * Генерирует строку из повторяющихся  тегов.
     * @param string $tag - тег
     * @param integer $amount - количество повторений
     * @return string
     */
    protected function tags($tag,$amount=1)
    {
        $output='';
        if ($amount>0)
        {
            for($i=1;$i<=$amount;$i++)
            {
                $output.=$tag;
            }
        }
        return $output;
    } 
    
    /**
     * Возвращает сгенерированную строку атрибутов тега.
     * @return string
     */
    protected function getStrAttributes()
    {
        $result='';
        foreach ($this->attributes as $key=>$param)
        { 
            $result.=' '.$key.'="'.$param.'"';
        }
        return $result;
    }
    
    /**
     * Устанавливает тип списка.
     * @param string $type
     */
    public function setType($type='ul')
    {
        $this->typeTag=$type;
    }
}
?>