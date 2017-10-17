<?php
/** 
CREATE TABLE IF NOT EXISTS `constants` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `identifier` varchar(64) NOT NULL,
  `value` varchar(256) NOT NULL,
  `description` varchar(512) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `identifier` (`identifier`)
);
 */
/**
 * Класс для работы с константами, сохранённые в базе данных.
 */
class Module_Const_Native
{
    /**
     * Префикс таблицы с константами.
     * @var string 
     */
    private $prefix='';
    
    /**
     * Имя таблицы с константами.
     * @var string 
     */
    private $tableName='';
    
    /**
     * Массив для хранения констант.
     * @var array 
     */
    private $constArray=NULL;
    
    /**
     * Конструктор.
     */
    public function __construct($tableName='constants',$prefix="") 
    {
        //Устанавливает имя таблицы с константами.
        $this->setTableName($tableName);
        $this->setPrefix($prefix);
        //Выполняем загрузку констант из базы данных.
        $this->init();
    }
    
    
    /**
     * Загружает константы из базы данных.
     */
    private function init()
    {
        $sql="SELECT identifier,value,id,description FROM ".$this->prefix.$this->tableName." ORDER BY identifier";
        $this->constArray=Syo_Db_Pdo::getInstance()->fetchAssoc($sql); 
    }
    
    /**
     * Устанавливает префикс таблицы констант.
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix=$prefix;
    }
    
    /**
     * Устанавливает имя таблицы с константами.
     * @param string $name
     */
    public function setTableName($name)
    {
        $this->tableName=$name;
    }
    
    /**
     * Возвращает значение константы.
     * @param string $identifier - имя константы
     * @param variant $default - значение константы по умолчанию
     * @return variant 
     */
    public function getConst($identifier,$default=NULL)
    {
        if (isset($this->constArray[$identifier]))
            return $this->constArray[$identifier]['value'];
        else 
            return $default;
    }
    
    /**
     * Возвращает Id константы.
     * @param string $identifier - имя константы
     * @return integer | null
     */
    public function getIdConst($identifier)
    {
        if (isset($this->constArray[$identifier]))
            return $this->constArray[$identifier]['id'];
        else 
            return NULL;
    }
    
    /**
     * Устанавливает значение константы.
     * @param string $identifier - имя константы
     * @param variant $value - значение константы
     * @return variant
     */
    public function setConst($identifier,$value=NULL)
    {
        if (isset($this->constArray[$identifier]))
        {
            $sql="UPDATE ".$this->prefix.$this->tableName." SET value='".$value."' WHERE identifier='".$identifier."'";
            Syo_Db_Pdo::getInstance()->query($sql);
        }
        else
        {
            $sql="INSERT INTO ".$this->prefix.$this->tableName
                ." (identifier,value) VALUES ('".$identifier."','".$value."')";
            Syo_Db_Pdo::getInstance()->query($sql);
            $this->constArray[$identifier]['id']=Syo_Db_Pdo::getInstance()->getLastId();
        }
        $this->constArray[$identifier]['value']=$value;
        return $this->constArray[$identifier]; 
    }
    
    /**
     * Создаёт новую константу.
     * @param string $identifier - имя константы
     * @param variant $value - значение константы
     * @param string $description - описание константы
     * @return variant
     */
    public function createConst($identifier,$value=NULL,$description='')
    {
        if (isset($this->constArray[$identifier]))
        {
            $sql="UPDATE ".$this->prefix.$this->tableName." SET value='".$value."',description='".$description."' WHERE identifier='".$identifier."'";
            Syo_Db_Pdo::getInstance()->query($sql);
        }
        else
        {
            $sql="INSERT INTO ".$this->prefix.$this->tableName
                ." (identifier,value,description) VALUES ('".$identifier."','".$value."','".$description."')";
            Syo_Db_Pdo::getInstance()->query($sql);
            $this->constArray[$identifier]['id']=Syo_Db_Pdo::getInstance()->getLastId();
        }
        $this->constArray[$identifier]['value']=$value;
        $this->constArray[$identifier]['description']=$description;
        return $this->constArray[$identifier];            
    }
    
    /**
     * Удаляет константу.
     * @param string $identifier - имя константы
     */
    public function deleteConst($identifier)
    {
        if (isset($this->constArray[$identifier]))
        {
            $sql="DELETE FROM ".$this->prefix.$this->tableName." WHERE identifier='".$identifier."'";
            Syo_Db_Pdo::getInstance()->query($sql);
            unset($this->constArray[$identifier]);
        }    
    }
    
    /**
     * Удаляет все константы.
     */
    public function cleanConst()
    {
        $sql="TRUNCATE TABLE ".$this->prefix.$this->tableName;
        Syo_Db_Pdo::getInstance()->query($sql);
        $this->constArray=NULL;
    }
}
?>