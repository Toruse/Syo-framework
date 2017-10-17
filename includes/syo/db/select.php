<?php
/**
 * Класс для генерации SQL-запроса.
 * 
 * Create 13.03.2014
 * Update 13.03.2014
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.0
 * 
 * @package syo
 * @subpackage db
 */
class Syo_Db_Select
{
    /**
     * Хранит контейнеры с параметрами для генерации запроса.
     * @var array 
     */
    protected $_parts=array(
        'DISTINCT'=>false,
        'COLUMNS'=>array(),
        'UNION'=>array(),
        'SELECT'=>array(),
        'FROM'=>array(),
        'WHERE'=>array(),
        'GROUP'=>array(),
        'HAVING'=>array(),
        'ORDER'=>array(),
        'LIMIT_COUNT'=>null,
        'LIMIT_OFFSET'=>null,
        'FOR_UPDATE'=>false
    );
    
    /**
     * Ключевые слова JOIN.
     * @var array 
     */
    protected $_join=array(
        'INNER_JOIN',
        'LEFT_JOIN',
        'RIGHT_JOIN',
        'FULL_JOIN',
        'CROSS_JOIN',
        'NATURAL_JOIN',
    );
    
    /**
     * Хранит сгенерированный запрос.
     * @var string
     */
    protected $_query;
    
    /**
     * Добавляет в FROM таблицу и её ассоциацию.
     * @param string $table - имя таблицы или массив содержащий имя и ассоциацию таблицы
     * @return Syo_Db_Select
     */
    public function From($table)
    {
        if (is_array($table))
        {
            $this->_parts['FROM'][]="'".$table[0]."' AS ".$table[1];
        }
        else
        {
            $this->_parts['FROM'][]=$table;
        }
        return $this;
    }
    
    /**
     * Указывает по какому полю выполнить сортировку.
     * @param string $name - имя поля
     * @param string $asc - в каком порядке сортировать
     * @return Syo_Db_Select
     */
    public function Order($name,$asc=null)
    {
        if ($asc===null)
        {
            $this->_parts['ORDER'][]=$name;
        }
        else
        {
            $this->_parts['ORDER'][]=$name.' '.$asc;            
        }
        return $this;
    }
    
    /**
     * Генерирует запрос.
     * @return string
     */
    public function Query()
    {
        $this->_bild();
        return $this->_query;
    }
    
    /**
     * Добавляет поля в SELECT.
     * @return Syo_Db_Select
     */
    public function Select()
    {
        $args=func_get_args();
        foreach ($args as $ars)
        {
            if (is_array($ars))
            {
                foreach ($ars as $key=>$value)
                {
                    $this->_parts['SELECT'][]=$key." AS ".$value;
                }
            }
            else
            {
                $this->_parts['SELECT'][]=$ars;
            }            
        }
        return $this;
    }
    
    /**
     * Указываем лимиты для выборки.
     * @param integer $count - с какой записи начать выборку
     * @param integer $offset - какое количество выбрать
     * @return Syo_Db_Select
     */
    public function Limit($count,$offset=null)
    {
        $this->_parts['LIMIT_COUNT']=$count;
        $this->_parts['LIMIT_OFFSET']=$offset;
        return $this;        
    }

    /**
     * Генерирует оператор LIMIT.
     * @return string
     */
    protected function _limit()
    {
        $str='';
        if ($this->_parts['LIMIT_OFFSET']!==null)
        {
            if ($this->_parts['LIMIT_OFFSET']!==null) $str.=' LIMIT '.$this->_parts['LIMIT_OFFSET'];
            if ($this->_parts['LIMIT_COUNT']!==null) $str.=','.$this->_parts['LIMIT_COUNT'];        
        }
        else
        {
            if ($this->_parts['LIMIT_COUNT']!==null) $str.=' LIMIT '.$this->_parts['LIMIT_COUNT'];
        }
        return $str;
    }
    
    /**
     * Добавляет условие к WHERE.
     * @return Syo_Db_Select
     */
    public function Where()
    {
        $args=func_get_args();
        $this->_WhereIf($args);
        return $this;        
    }

    /**
     * Добавляет скобку в условии WHERE.
     * @return Syo_Db_Select
     */
    public function WhereBlockBegin()
    {
        $this->_WhereBlockBegin('AND');
        return $this;        
    }

    /**
     * Закрывает скобки в условии WHERE.
     * @return Syo_Db_Select
     */
    public function WhereBlockEnd()
    {
        $this->_WhereBlockEnd();
        return $this;        
    }

    /**
     * Добавляет скобку с OR в условии WHERE.
     * @return Syo_Db_Select
     */
    public function orWhereBlockBegin()
    {
        $this->_WhereBlockBegin('OR');
        return $this;        
    }

    /**
     * Закрывает скобки с OR в условии WHERE.
     * @return Syo_Db_Select
     */
    public function orWhereBlockEnd()
    {
        $this->_WhereBlockEnd();
        return $this;        
    }
    
    /**
     * Добавляет условие с OR к WHERE.
     * @return Syo_Db_Select
     */
    public function orWhere()
    {
        $args=func_get_args();
        $this->_WhereIf($args,'OR');
        return $this;        
    }
    
    /**
     * Добавляет условие к WHERE.
     * @param array $args - массив с параметрами для генерации условий выборки
     * @param string $type - тип условия (AND, OR)
     */
    protected function _WhereIf($args,$type='AND')
    {
        //Определяем количество элементов в условии.
        $count=count($args);
        $str='';
        //Определяем нужно ли нам отступить от предыдущего условия.
        $block=end($this->_parts['WHERE']);
        $block=preg_replace(array("/^(\S+)\s+/"),array(""),$block);
        if ($block=='(') $type=''; else $type=$type.' ';
        //Условие виде строки
        if ($count==1)
        {
            $str=$type.$args[0];
            $this->_parts['WHERE'][]=$str;            
        }
        //Получено имя поля и значение для сравнения.
        elseif ($count==2)
        {
            $str=$type.$args[0]."='".$args[1]."'";
            $this->_parts['WHERE'][]=$str;
        }
        //Получено имя таблицы, имя поля и значение для сравнения
        elseif (($count>2) and ($count<=3))
        {
            $str.=$args[0].'.'.$args[1]."='".$args[2]."'";
            $this->_parts['WHERE'][]=$type.$str;
        }
    }

    /**
     * Добавляет скобку в условии WHERE.
     * @param string $type - тип AND, OR
     */
    protected function _WhereBlockBegin($type='AND')
    {
        $this->_parts['WHERE'][]=$type.' (';
    }

    /**
     * Закрывает скобки в условии WHERE.
     */
    protected function _WhereBlockEnd()
    {
        $this->_parts['WHERE'][]=')';
    }
    
    /**
     * Генерирует запрос.
     */
    protected function _bild()
    {
        $str='';
        $str.=$this->_select();
        $str.=$this->_from();
        $str.=$this->_where();
        $str.=$this->_order();
        $str.=$this->_limit();
        $this->_query=$str;
    }
    
    /**
     * Перезагружаем метод __toString.
     * @return string
     */
    public function __toString()
    {
        $this->_bild();
        return $this->_query;
    }
    
    /**
     * Генерирует FROM запроса.
     * @return string
     */
    protected function _from()
    {
        $str='';
        if (count($this->_parts['FROM'])!=0)
        {        
            $str.=' FROM '.implode(',',$this->_parts['FROM']);
        }
        return $str;
    }
    
    /**
     * Генерирует SELECT запроса.
     * @return string
     */
    protected function _select()
    {
        $str='';
        if (count($this->_parts['SELECT'])==0)
        {
            $str="SELECT *";
        }
        else
        {
            $str.='SELECT '.implode(',',$this->_parts['SELECT']);
        }
        return $str;
    }

    /**
     * Генерирует WHERE запроса.
     * @return string
     */
    protected function _where()
    {
        $str='';
        if (count($this->_parts['WHERE'])>0)
        {
            $this->_parts['WHERE'][0]=preg_replace(array("/^(\S+)\s+/"),array(""),$this->_parts['WHERE'][0]);
            $str.=' WHERE '.implode(' ',$this->_parts['WHERE']);
        }
        return $str;
    }
    
    /**
     * Генерирует ORDER запроса.
     * @return string
     */
    protected function _order()
    {
        $str='';
        if (count($this->_parts['ORDER'])>0)
        {
            $str.=' ORDER BY '.implode(',',$this->_parts['ORDER']);
        }
        return $str;        
    }
    
    /**
     * Сбрасывает установленные параметры для генерации запроса.
     */
    public function Clear()
    {
        $this->_parts=array(
            'DISTINCT'=>false,
            'COLUMNS'=>array(),
            'UNION'=>array(),
            'SELECT'=>array(),
            'FROM'=>array(),
            'WHERE'=>array(),
            'GROUP'=>array(),
            'HAVING'=>array(),
            'ORDER'=>array(),
            'LIMIT_COUNT'=>null,
            'LIMIT_OFFSET'=>null,
            'FOR_UPDATE'=>false
        );
    }
}
?>