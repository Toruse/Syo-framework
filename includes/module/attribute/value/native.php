<?php
/**
 * Класс для работы со значениями элемента через базу данных.
 * 
CREATE TABLE IF NOT EXISTS `attribute_value_string` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `value` tinytext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `attribute_id` (`attribute_id`)
); 

ALTER TABLE `attribute_value_string`
  ADD CONSTRAINT `attribute_value_string_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
 */
class Module_Attribute_Value_Native 
{
    /**
     * Тип класса-значения.
     * @var string 
     */
    private $name='string';
    
    /**
     * Префикс таблицы.
     * @var string 
     */
    protected $prefix='';
    
    /**
     * Хранит имя таблицы атрибутов.
     * @var string
     */
    private $nameTable='attributes';
    
    /**
     * Хранит имя таблицы значений.
     * @var string 
     */
    private $nameTableValue='attribute_value_string';
    
    /**
     * Конструктор.
     * @param array $param - указываем имя, префикс, имя таблицы значений и имя таблицы атрибутов.
     */
    public function __construct($param=NULL) 
    {
        if (!is_null($param))
        {
            if (isset($param['name'])) $this->name=$param['name'];
            if (isset($param['prefix'])) $this->prefix=$param['prefix'];
            if (isset($param['nameTable'])) $this->nameTable=$param['nameTable'];
            if (isset($param['nameTableValue'])) $this->nameTableValue=$param['nameTableValue'];
        }
    }
    
    /**
     * Выполняет поиск атрибутов и их значения.
     * entity_id=null - находит все атрибуты на основе найденных значений
     * entity_id=number - находит атрибуты и их значения для указанного элемента
     * entity_id=array - находит атрибуты со значениями элементов указанные в массиве
     * @param number $entity_id - id элемента для которого выполняетесь поиск атрибутов
     * @param string $sampling - находит только атрибуты фильтры или редактируемые атрибуты ('filter','edit','all')
     * @return array
     */
    public function findAttributes($entity_id=NULL,$sampling=NULL)
    {
        //Генерируем имена таблиц
        $nameTable=$this->prefix.$this->nameTable;
        $nameTableValue=$this->prefix.$this->nameTableValue;
        //Генерируем запрос к базе
        $sql="SELECT ".$nameTableValue.".entity_id AS entity_id,".$nameTableValue.".attribute_id AS attribute_id,".$nameTableValue.".id AS value_id,
".$nameTable.".type AS type,".$nameTable.".name AS name,".$nameTableValue.".value AS value,".$nameTable.".edit AS edit,".$nameTable.".filter AS filter
FROM ".$nameTableValue." LEFT JOIN ".$nameTable." ON ".$nameTableValue.".attribute_id=".$nameTable.".id";
        //Добавляем к запросу условие, какие значения выбрать из таблицы
        $where=NULL;
        if (!is_null($entity_id))
        {
            if (is_array($entity_id))
            {
                $where[]=$nameTableValue.".entity_id IN (".implode(',',$entity_id).")";
            }
            else
            {
                $where[]=$nameTableValue.".entity_id=".$entity_id;                
            }
        }
        //Добавляем к запросу условие, находить только атрибуты фильтры или редактируемые атрибуты
        if (!is_null($sampling))
        {
            switch ($sampling)
            {
                case 'filter':
                    $where[]=$nameTable.'.filter=1';
                break;
                case 'edit':
                    $where[]=$nameTable.'.edit=1';
                break;
                case 'all':
                    $where[]=$nameTable.'.edit=1 AND '.$nameTable.'.filter=1';
                break;
            }
        }
        if (!is_null($where))
        {
            $sql.=" WHERE ".implode(' AND ',$where);
        }
        //Выполняем поиск
        return Syo_Db_Pdo::getInstance()->fetchAll($sql);
    }
    
    /**
     * Проверяет наличие значения в таблице.
     * @param number $attribute_id - id атрибута
     * @return boolean
     */
    public function checkExistence($attribute_id)
    {
        $sql="SELECT id FROM ".$this->prefix.$this->nameTableValue." WHERE attribute_id=".$attribute_id;
        $result=Syo_Db_Pdo::getInstance()->query($sql);
	if ($result->rowCount()==0)
        {
            return FALSE;
        }
        return TRUE;        
    }
    
    /**
     * Добавляет значение в таблицу.
     * @param array $extrafields - параметры значения
     * @return boolean
     */
    public function insertValue($extrafields)
    {
        $data_keys=array_keys($extrafields);
        $data_values=array_values($extrafields);
        $sql="INSERT INTO ".$this->prefix.$this->nameTableValue." (".implode(",",$data_keys).") VALUES ('".implode("','",$data_values)."')";
        if (Syo_Db_Pdo::getInstance()->query($sql))
        {
            return TRUE;
        }
        return FALSE;        
    }
    
    /**
     * Обновляет параметры значения в таблице.
     * @param array $extrafields - параметры значения
     * @return boolean
     */
    public function updateValue($extrafields)
    {
        $set=NULL;
        //Переносим id значения в раздел условия запроса.
        if (isset($extrafields['id']))
        {
            $id=$extrafields['id'];
            unset($extrafields['id']);
        }
        else
        {
            return FALSE;
        }
        //Генерируем параметры SET
        foreach ($extrafields as $key=>$value)
        {
            $set.=((is_null($set))?'':',').$key."='".$value."'";
        }
        //Выполняем запрос
        $sql="UPDATE ".$this->prefix.$this->nameTableValue." SET ".$set." WHERE id='".$id."'";
        if (Syo_Db_Pdo::getInstance()->query($sql))
        {
            return TRUE;
        }
        return FALSE;        
    }
    
    /**
     * Удаляет значения из таблицы.
     * id=null - удаляет все значения из таблицы
     * id=number - удаляет указанное значение
     * id=array - удаляет значения указанные в массиве
     * @param number $id - id значения
     * @return boolean
     */
    public function deleteValue($id=NULL)
    {
        $nameTableValue=$this->prefix.$this->nameTableValue;
        $sql="DELETE FROM ".$nameTableValue;
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
        if (Syo_Db_Pdo::getInstance()->query($sql))
        {
            return TRUE;
        }
        return FALSE;        
    }
    
    /**
     * Устанавливает значение префикса.
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix=$prefix;
    }
    
    /**
     * Возвращает имя класса-значения.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
}
?>