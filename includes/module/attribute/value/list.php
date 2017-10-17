<?php
/**
 * Класс для работы со значениями элемента типа список через базу данных.
 * 
 CREATE TABLE IF NOT EXISTS `attribute_value_list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` int(10) unsigned NOT NULL,
  `entity_id` int(10) unsigned NOT NULL,
  `value` int(10) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `attribute_id` (`attribute_id`)
);

 CREATE TABLE IF NOT EXISTS `attribute_list` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `attribute_id` int(10) unsigned NOT NULL,
  `name` tinytext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `attribute_id` (`attribute_id`)
);

ALTER TABLE `attribute_list`
  ADD CONSTRAINT `attribute_list_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `attribute_value_list`
  ADD CONSTRAINT `attribute_value_list_ibfk_1` FOREIGN KEY (`attribute_id`) REFERENCES `attributes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
 */
class Module_Attribute_Value_List extends Module_Attribute_Value_Native
{
    /**
     * Хранит имя таблицы со списком.
     * @var string 
     */
    private $nameTableList='attribute_list';
    
    /**
     * Указывает добавлять список к значениям или нет.
     * @var boolean 
     */
    private $viewList=TRUE;
    
    /**
     * Переопределяем конструктор.
     * @param array $param
     */
    public function __construct($param=NULL) 
    {
        if (is_null($param))
        {
            parent::__construct(array('name'=>'list','nameTableValue'=>'attribute_value_list'));
        }
        else
        {
            parent::__construct($param);
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
        $data=parent::findAttributes($entity_id,$sampling);
        //Добавляем списки к значениям
        if ($this->viewList && isset($data[0]))
        {
            //Находим id атрибутов
            $attr=array();
            foreach ($data as $el)
            {
                $attr[$el['attribute_id']]=$el['attribute_id'];
            }
            $attr=array_merge(array(),$attr);
            //Находим списки в таблице.
            $nameTableList=$this->prefix.$this->nameTableList;
            $sql="SELECT * FROM ".$nameTableList." WHERE attribute_id IN (".implode(',',$attr).")";
            $list=Syo_Db_Pdo::getInstance()->fetchAll($sql);
            //Группируем списки по атрибутам
            $attr=array();
            foreach ($list as $el)
            {
                $attr[$el['attribute_id']][$el['id']]=$el['name'];
            } 
            //Добавляем списки к значениям
            foreach ($data as $key=>$el)
            {
                if (isset($attr[$el['attribute_id']])) $data[$key]['list']=$attr[$el['attribute_id']];
            }
        }
        return $data;
    }
    
    /**
     * Добавляет список в таблицу.
     * @param number $attribute_id - id атрибута
     * @param array $list - добавляемый список
     * @return boolean
     */
    public function insertList($attribute_id,$list)
    {
        if (is_array($list))
        {
            //Если передан массив
            //Генерируем параметр VALUES
            $values=array();
            foreach ($list as $value) 
            {
                $values[]="('".$attribute_id."','".$value."')";
            }
            //Выполняем добавление
            $sql="INSERT INTO ".$this->prefix.$this->nameTableList." (attribute_id,name) VALUES ".implode(",",$values);
            if (Syo_Db_Pdo::getInstance()->query($sql))
            {
                return TRUE;
            }            
        }
        else
        {
            //Если передано число
            //Выполняем добавление
            $sql="INSERT INTO ".$this->prefix.$this->nameTableList." (attribute_id,name) VALUES ('".$attribute_id."','".$list."')";
            if (Syo_Db_Pdo::getInstance()->query($sql))
            {
                return TRUE;
            }
        }
        return FALSE; 
    }
    
    /**
     * Выполняем обновления элемента из списка.
     * @param array $extrafields - параметры списка
     * @return boolean
     */
    public function updateList($extrafields)
    {
        //Переносим id значения в раздел условия запроса.
        $set=NULL;
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
        $sql="UPDATE ".$this->prefix.$this->nameTableList." SET ".$set." WHERE id='".$id."'";
        if (Syo_Db_Pdo::getInstance()->query($sql))
        {
            return TRUE;
        }
        return FALSE;        
    }

    /**
     * Удаляет элемент списка из таблицы.
     * id=null - удаляет все элементы списка из таблицы
     * id=number - удаляет указанные элементы списка
     * id=array - удаляет элементы списка указанные в массиве
     * @param number $id - id элемента из списка
     * @return boolean
     */
    public function deleteList($id=NULL)
    {
        $nameTable=$this->prefix.$this->nameTableList;
        $sql="DELETE FROM ".$nameTable;
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
     * Выбирает из таблицы списки.
     * @param number $attribute_id - id списка или массив 
     * @return array
     */
    public function selectList($attribute_id=NULL)
    {
        $sql="SELECT * FROM ".$this->prefix.$this->nameTableList;
        if (!is_null($attribute_id))
        {
            if (is_array($attribute_id))
            {
                $sql.=" WHERE attribute_id IN (".implode(',',$attribute_id).")";
            }
            else
            {
                $sql.=" WHERE attribute_id=".$attribute_id;                
            }
        }
        return Syo_Db_Pdo::getInstance()->fetchAll($sql);        
    }
    
    /**
     * Устанавливает добавлять список к значениям.
     * @param boolean $viewList
     */
    public function setViewList($viewList)
    {
        $this->viewList=$viewList;
    }
}
?>