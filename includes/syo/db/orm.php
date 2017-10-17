<?php
/**
 * Класс для объектно-реляционное отображение.
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
class Syo_Db_Orm
{
    /**
     * Хранит значения полей в записи
     * @var array 
     */
    private $vars=array();
    
    /**
     * Имя таблицы
     * @var srting
     */
    private $table=null;
    
    /**
     * Имя ключевого поля в таблице
     * @var string 
     */
    private $nameId='id';
    
    /**
     * Конструктор.
     * @param string $name - имя таблицы
     */
    public function __construct($name=null)
    {
        if (is_null($name))
        {
            $this->table=strtolower(get_class($this).'s');
        }
        else 
        {
            $this->table=$name;     
        }
        $this->vars[$this->nameId]=null;
    }
    
    /**
     * Устанавливает ключевое поле.
     * @param string $nameId - название ключевого поля
     */
    public function setNameId($nameId='id')
    {
        unset($this->vars[$this->nameId]);
        $this->nameId=$nameId;
        $this->vars[$this->nameId]=null;
    }

    /**
     * Устанавливает значение ключевого поля.
     * @param number $id - значение
     */
    public function setId($id)
    {
        $this->vars[$this->nameId]=$id;
    }

    /**
     * Возвращает значение ключевого поля.
     * @return number
     */
    public function getId()
    {
        return $this->vars[$this->nameId];
    }

    /**
     * Сбрасывает значение ключевого поля на NULL.
     */
    public function setIdNull()
    {
        $this->vars[$this->nameId]=null;
    }
    
    /**
     * Сетер класса.
     * @param index $key
     * @param variant $var
     * @return boolean
     */
    public function __set($key,$var)
    {
        $this->vars[$key]=$var;
        return true;
    }
    
    /**
     * Гетер класса.
     * @param index $key
     * @return variant
     */
    public function __get($key)
    {
        return $this->vars[$key];
    }

    /**
     * Unset класса.
     * @param index $key
     */
    public function __unset($key) 
    {
        unset($this->vars[$key]);
    }
    
    /**
     * Isset класса. 
     * @param index $key
     * @return boolean
     */
    public function __isset($key) 
    {
        return isset($this->vars[$key]);
    }
    
    /**
     * Загружает запись из базы данных.
     * @param number $id - id записи
     * @return boolean
     */
    public function load($id=null)
    {
        if (is_null($id))
        {
            if (is_numeric($this->vars[$this->nameId]))
            {
                $sql="SELECT * FROM ".$this->table." WHERE ".$this->nameId."=".$this->vars[$this->nameId];
            }
            else 
            {
                return FALSE;
            }                    
        }
        else 
        {
            if (is_numeric($id))
            {
                $sql="SELECT * FROM ".$this->table." WHERE ".$this->nameId."=".$id;
            }
            else 
            {
                return FALSE;
            }                    
        }
        $result=Syo_Db_Pdo::getInstance()->fetchOne($sql);
        if (!$result) return FALSE;
        $this->vars=$result;
        return TRUE;   
    }
    
    /**
     * Создаёт или обновляет запись в базе данных.
     * @return PDOStatement or FALSE
     */
    public function save()
    {
        if ($this->existence())
        {
            return $this->update();
        }
        else
        {
            return $this->insert();     
        }
    }
    
    /**
     * Проверяет наличие записи в базе данных.
     * @return boolean
     */
    public function isExists()
    {
        return $this->existence();
    }
    
    /**
     * Проверяет наличие записи в базе данных.
     * @return boolean
     */
    private function existence()
    {
        if (is_null($this->vars[$this->nameId]))
        {
            return FALSE;
        }
        $sql="SELECT COUNT(*) as count FROM ".$this->table." WHERE ".$this->nameId."='".$this->vars[$this->nameId]."'";
        $result=Syo_Db_Pdo::getInstance()->fetchOne($sql);
        if ($result['count']==0)
        {
            return FALSE;
        }
        else 
        {
            return TRUE;
        }
    }
    
    /**
     * Oбновляет запись в базе данных.
     * @return PDOStatement or FALSE
     */
    private function update()
    {
        $set=null;
        foreach ($this->vars as $key=>$value)
        {
            if ($key!=$this->nameId) $set.=((is_null($set))?'':',').$key."='".$value."'";
        }
        $sql="UPDATE ".$this->table." SET ".$set." WHERE ".$this->nameId."='".$this->vars[$this->nameId]."'";
        return Syo_Db_Pdo::getInstance()->query($sql);                    
    }
    
    /**
     * Вставляет запись в базу данных.
     * @return PDOStatement or FALSE
     */
    private function insert()
    {
        $vars_keys=array_keys($this->vars);
        $vars_values=array_values($this->vars);
        $sql="INSERT INTO ".$this->table." (".implode(",",$vars_keys).") VALUES ('".implode("','",$vars_values)."')";
        return Syo_Db_Pdo::getInstance()->query($sql);                    
    }
    
    /**
     * Удаляет запись из базы данных.
     * @param number $id - id записи
     * @return PDOStatement or FALSE
     */
    public function delete($id=null)
    {
        if (is_null($id))
        {
            if (!is_null($this->vars[$this->nameId]))
            {
                $sql="DELETE FROM ".$this->table." WHERE ".$this->nameId."=".$this->vars[$this->nameId];
                return Syo_Db_Pdo::getInstance()->query($sql);
            }
            return FALSE;
        }        
        else
        {
            $sql="DELETE FROM ".$this->table." WHERE ".$this->nameId."=".$id;
            return Syo_Db_Pdo::getInstance()->query($sql);                        
        }
    }
}
?>