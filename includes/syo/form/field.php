<?php
class Syo_Form_Field
{
    //имя элемента управления
    protected $_name;
    //тип элемента управления
    protected $_type;
    //название элемента управления
    protected $_caption;
    //значение элемента управления
    protected $_value;
    //  Обязателен  ли элемент к  заполнению 
    protected $_required; 
    //атрибуты элемента управления
    protected $_attributes=array();
    //подсказка
    protected $_hint;
    //набор проверок элемента управления
    protected $_validation=array();
    //набор фильтров элемента управления
    protected $_filter=array();
    //размешаем сообщения об ошибках
    protected $_error;    
        
    //конструктор класса
    public function __construct($name,$post=false)
    {
        $this->_name=$name;
        $this->_type='text';
        if ($post && isset($_POST[$name])) $this->setValue($_POST[$name]);
        $this->_error=array();
    }
    
    //метод для проверки корректности заполнения поля 
    public function isVerify()
    {
        $result=false;
        foreach ($this->_validation as $validate)
        {
            if ($validate->isVerify($this->_value))
            {
                $this->addErrorArray($validate->getError());
                $result=true;
            }
        }
        return $result;
    }

    public function isFilter()
    {
        foreach ($this->_filter as $filter)
        {
            $this->_value=$filter->isFilter($this->_value);
        }
    }
    
    //возвращаем элемент виде Html
    public function getHtml()
    {
        return $this->getCaption().' '.$this->getInput();
    }

    public function getInput()
    {
        $input='<input';
        if (!empty($this->_type)) $input.=' type="'.$this->_type.'"';
        if (!empty($this->_name)) $input.=' name="'.$this->_name.'" id="'.$this->_name.'"';
        if (!empty($this->_value)) $input.=' value="'.$this->_value.'"';
        foreach ($this->_attributes as $attr=>$value)
        {
            $input.=' '.$attr.'="'.$value.'"';
        }
        $input.=">\n";
        return $input;
    }
    
    public function getHtmlCaption()
    {
        return '<label>'.$this->_caption.'</label>';
    }

    public function getCaption()
    {
        return $this->_caption;
    }
    
    public function setCaption($caption)
    {
        $this->_caption=$caption;
    }
    
    public function getName()
    {
        return $this->_name;
    }

    public function setValue($value)
    {
        $this->_value=$value;
    }

    public function getValue()
    {
        return $this->_value;
    }
    
    //функции для работы с дополнительными атрибутами элемента управления
    public function addAttribute($key,$value)
    {
        if (!empty($key)) $this->_attributes[$key]=$value;
    }

    public function delAttribute($key)
    {
        unset($this->_attributes[$key]);
    }

    public function setAttribute($key,$value)
    {
        if (!empty($key)) $this->_attributes[$key]=$value;
    }

    //функции для работы с параметрами проверки элемента управления
    public function addVerify($valid)
    {
        if (!empty($valid)) 
        {
            $key=$valid->getName();
            if ($key=='')
            {
                $this->_validation[]=$valid;
            }
            else
            {
                $this->_validation[$key]=$valid;                
            }
            return true;
        }
        return false;
    }

    public function removeVerify($key)
    {
        unset($this->_validation[$key]);
    }

    public function setVerify($key,$valid)
    {
        if (!empty($valid)) 
        {
            $this->_validation[$key]=$valid;
            return true;
        }
        return false;
    }

    //функции для работы с параметрами фильтрации элемента управления
    public function addFilter($filter)
    {
        if (!empty($filter)) 
        {
            $key=$filter->getName();
            if ($key=='')
            {
                $this->_filter[]=$filter;
            }
            else
            {
                $this->_filter[$key]=$filter;                
            }
            return true;
        }
        return false;
    }

    public function removeFilter($key)
    {
        unset($this->_filter[$key]);
    }

    public function setFilter($key,$filter)
    {
        if (!empty($filter)) 
        {
            $this->_filter[$key]=$filter;
            return true;
        }
        return false;
    }
    
    //функция перевода текста с русского в транслит 
    protected function EncodeString($str)
    {
        $str=strtr($str,"абвгдеёзийклмнопрстуфхъыэ_","abvgdeeziyklmnoprstufh'iei");
        $str=strtr($str,"АБВГДЕЁЗИЙКЛМНОПРСТУФХЪЫЭ_","ABVGDEEZIYKLMNOPRSTUFH'IEI");
        $str=strtr($str, 
                    array(
                        "ж"=>"zh","ц"=>"ts","ч"=>"ch","ш"=>"sh", 
                        "щ"=>"shch","ь"=>"","ю"=>"yu","я"=>"ya",
                        "Ж"=>"ZH","Ц"=>"TS","Ч"=>"CH","Ш"=>"SH", 
                        "Щ"=>"SHCH","Ь"=>"","Ю"=>"YU","Я"=>"YA",
                        "ї"=>"i","Ї"=>"Yi","є"=>"ie","Є"=>"Ye"
                        )
        );
        return $str;
    }

    public function getError()
    {
        return $this->_error;
    }
    
    public function addError($str)
    {
        $this->_error[]=$str;
    }

    public function addErrorArray($array)
    {
        $this->_error=array_merge($this->_error,$array);
    }
}    
?>