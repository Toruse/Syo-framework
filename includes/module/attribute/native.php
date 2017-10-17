<?php
/**
 * Класс для работы с атрибутами элемента через базу данных.
 * 
 CREATE TABLE IF NOT EXISTS `attributes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `edit` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `filter` tinyint(1) unsigned NOT NULL DEFAULT '0',
  `type` varchar(50) NOT NULL,
  PRIMARY KEY (`id`)
);
 */
class Module_Attribute_Native
{
    /**
     * Префикс таблицы.
     * @var string
     */
    private $prefix='';
    
    /**
     * Хранит имя таблицы атрибутов.
     * @var string
     */
    private $attributes='attributes';
    
    /**
     * Хранит классы со значениями атрибутов.
     * @var array 
     */
    private $valueAttributes=array();
    
    /**
     * Конструктор.
     * @param array $param - передаёт префикс и имя таблицы.
     */
    public function __construct($param=NULL) 
    {
        if (!is_null($param))
        {
            if (isset($param['prefix'])) $this->prefix=$param['prefix'];
            if (isset($param['nameTable'])) $this->attributes=$param['nameTable'];
        }
    }
    
    /**
     * Добавляет атрибут в таблицу.
     * @param array $extrafields - массив со значениями атрибута
     * @return boolean
     */
    public function insertAttribute($extrafields)
    {
        $data_keys=array_keys($extrafields);
        $data_values=array_values($extrafields);
        $sql="INSERT INTO ".$this->prefix.$this->attributes." (".implode(",",$data_keys).") VALUES ('".implode("','",$data_values)."')";
        if (Syo_Db_Pdo::getInstance()->query($sql))
        {
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Обновления данных атрибута.
     * @param array $extrafields - массив со значениями атрибута
     * @return boolean
     */
    public function updateAttribute($extrafields)
    {
        $set=NULL;
        unset($extrafields['type']);
        //Удаляем из массива id, чтобы его передать в условие отбора запроса 
        if (isset($extrafields['id']))
        {
            $id=$extrafields['id'];
            unset($extrafields['id']);
        }
        else
        {
            return FALSE;
        }
        //Генерируем раздел SET
        foreach ($extrafields as $key=>$value)
        {
            $set.=((is_null($set))?'':',').$key."='".$value."'";
        }
        //Выполняем запрос
        $sql="UPDATE ".$this->prefix.$this->attributes." SET ".$set." WHERE id='".$id."'";
        if (Syo_Db_Pdo::getInstance()->query($sql))
        {
            return TRUE;
        }
        return FALSE;        
    }
    
    /**
     * Удаляем атрибут из таблицы.
     * id=null - удалить все атрибуты
     * id=number - удалить атрибут с указным id
     * id=array - удалить атрибуты указанные в массиве
     * @param number $id
     * @return boolean
     */
    public function deleteAttribute($id=NULL)
    {
        $nameTable=$this->prefix.$this->attributes;
        $sql="DELETE FROM ".$nameTable;
        //Указываем, какие атрибуты будут удалены
        if (!is_null($id))
        {
            if (is_array($id))
            {
                $sql.=" WHERE id IN (".implode(',',$id).")";
            }
            else
            {
                $sql.=" WHERE id=".$id;                
            }
        }
        //Выполняем удаление
        if (Syo_Db_Pdo::getInstance()->query($sql))
        {
            return TRUE;
        }
        return FALSE;        
    }
    
    /**
     * Добавляем значение атрибута в таблицу.
     * @param array $extrafields - параметры значения
     * @param string $type - тип значения
     * @return boolean
     */
    public function insertValue($extrafields,$type='string')
    {
        if (isset($extrafields['entity_id']))
        {
            $this->valueAttributes[$type]->insertValue($extrafields);
            return TRUE;
        }
        return FALSE;
    }    

    /**
     * Обновляет значение атрибута в таблице.
     * @param array $extrafields - параметры значения
     * @param string $type - тип значения
     */
    public function updateValue($extrafields,$type='string')
    {
        $this->valueAttributes[$type]->updateValue($extrafields);
    }

    /**
     * Удаляет значение из таблицы.
     * id=null - удалить все значения
     * id=number - удалить значения с указным id
     * id=array - удалить значения указанные в массиве
     * @param string $type - тип значения
     * @param number $id
     */
    public function deleteValue($type='string',$id=NULL)
    {
        $this->valueAttributes[$type]->deleteValue($id);
    }
    
    /**
     * Выполняет поиск атрибутов и их значения.
     * id=null - находит все атрибуты на основе найденных значений
     * id=number - находит атрибуты и их значения для указанного элемента
     * id=array - находит атрибуты со значениями элементов указанные в массиве
     * @param number $id - id элемента для которого выполняетесь поиск атрибутов
     * @param string $sampling - находит только атрибуты фильтры или редактируемые атрибуты ('filter','edit','all')
     * @return array
     */
    public function findAttributes($id=NULL,$sampling=NULL)
    {
        $data=array();
        foreach ($this->valueAttributes as $value)
        {
            $data=array_merge($data,$value->findAttributes($id,$sampling));
        }
        return $data;
    }
    
    /**
     * Выполняет поиск атрибутов и их значения, выполняя группировку данных.
     * id=null - находит все атрибуты на основе найденных значений
     * id=number - находит атрибуты и их значения для указанного элемента
     * id=array - находит атрибуты со значениями элементов указанные в массиве
     * @param number $id - id элемента для которого выполняетесь поиск атрибутов
     * @param string $sampling - находит только атрибуты фильтры или редактируемые атрибуты ('filter','edit','all')
     * @return array
     */
    public function findGroupAttributes($id=NULL,$sampling=NULL)
    {
        $data=array();
        foreach ($this->valueAttributes as $value)
        {
            foreach ($value->findAttributes($id,$sampling) as $el)
            {
                $data[$el['entity_id']][$el['attribute_id']]['type']=$el['type'];
                $data[$el['entity_id']][$el['attribute_id']]['name']=$el['name'];
                $data[$el['entity_id']][$el['attribute_id']]['edit']=$el['edit'];
                $data[$el['entity_id']][$el['attribute_id']]['filter']=$el['filter'];
                if (isset($el['list'])) $data[$el['entity_id']][$el['attribute_id']]['list']=$el['list'];
                    else $data[$el['entity_id']][$el['attribute_id']]['list']=NULL;
                $data[$el['entity_id']][$el['attribute_id']]['one_value']=$el['value'];
                $data[$el['entity_id']][$el['attribute_id']]['value'][$el['value_id']]=$el['value'];
            }
        }
        return $data;
    }

    /**
     * Возвращает все атрибуты из таблицы.
     * @return array
     */
    public function findAllAttributes()
    {
        $sql="SELECT * FROM ".$this->prefix.$this->attributes;
        return Syo_Db_Pdo::getInstance()->fetchAll($sql);
    }

    /**
     * Генерирует стандартный набор классов для работы со значениями атрибутов.
     */
    public function generateStandard()
    {
        $this->addValueInt();
        $this->addValueFloat();
        $this->addValueString();
        $this->addValueDatetime();
        $this->addValueText();
        $this->addValueList();
    }
    
    /**
     * Возвращает класс со значениями на основе указанного типа.
     * @param string $type - тип значения
     * @return Syo_Attribute_Value_Native - класс со значениями
     */
    public function getValue($type='string')
    {
        return $this->valueAttributes[$type];
    }
    
    /**
     * Добавляет к атрибутам класс-значения нового типа.
     * @param Syo_Attribute_Value_Native $value - класс-значения
     * @return Syo_Attribute_Native - this
     */
    public function addValue($value)
    {
        $this->valueAttributes[$value->getName()]=$value;
        return $this;
    }
    
    /**
     * Добавляет к атрибутам класс-значения целого типа.
     * @return Syo_Attribute_Native - this
     */
    public function addValueInt()
    {
        $value=new Syo_Attribute_Value_Int();
        $value->setPrefix($this->prefix);
        $this->addValue($value); 
        return $this;
    }

    /**
     * Добавляет к атрибутам класс-значения действительного типа.
     * @return Syo_Attribute_Native - this
     */
    public function addValueFloat()
    {
        $value=new Syo_Attribute_Value_Float();
        $value->setPrefix($this->prefix);
        $this->addValue($value);
        return $this;
    }
    
    /**
     * Добавляет к атрибутам класс-значения типа строка.
     * @return Syo_Attribute_Native - this
     */
    public function addValueString()
    {
        $value=new Syo_Attribute_Value_String();
        $value->setPrefix($this->prefix);
        $this->addValue($value);
        return $this;
    }

    /**
     * Добавляет к атрибутам класс-значения типа дата.
     * @return Syo_Attribute_Native - this
     */
    public function addValueDatetime()
    {
        $value=new Syo_Attribute_Value_Datetime();
        $value->setPrefix($this->prefix);
        $this->addValue($value);
        return $this;
    }
    
    /**
     * Добавляет к атрибутам класс-значения текстового типа.
     * @return Syo_Attribute_Native - this
     */
    public function addValueText()
    {
        $value=new Syo_Attribute_Value_Text();
        $value->setPrefix($this->prefix);
        $this->addValue($value);
        return $this;
    }
    
    /**
     * Добавляет к атрибутам класс-значения типа список.
     * @return Syo_Attribute_Native - this
     */
    public function addValueList()
    {
        $value=new Syo_Attribute_Value_List();
        $value->setPrefix($this->prefix);
        $this->addValue($value);
        return $this;
    }
    
    /**
     * Удаляет все классы-значения.
     * @return Syo_Attribute_Native - this
     */
    public function clearValue()
    {
        $this->valueAttributes=array();
        return $this;        
    }
    
    /**
     * Указывает префикс таблицы атрибутов.
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix=$prefix;
    }
}
?>