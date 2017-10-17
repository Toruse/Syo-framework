<?php
/**
 * Класс для управление привилегиями.
 * 
CREATE TABLE IF NOT EXISTS `permissions` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gp` varchar(50) NOT NULL,
  `value` varchar(50) NOT NULL,
  `description` tinytext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `value` (`value`)
);

CREATE TABLE IF NOT EXISTS `roles` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `description` tinytext NOT NULL,
  PRIMARY KEY (`id`),
  KEY `name` (`name`)
);

CREATE TABLE IF NOT EXISTS `role_perm` (
  `role_id` int(10) unsigned NOT NULL,
  `perm_id` int(10) unsigned NOT NULL,
  KEY `perm_id` (`perm_id`),
  KEY `role_id` (`role_id`)
);

ALTER TABLE `role_perm`
  ADD CONSTRAINT `role_perm_ibfk_2` FOREIGN KEY (`perm_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `role_perm_ibfk_3` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
 */
class Module_Auth_Role
{
    /**
     * Имя таблицы ролей.
     * @var string 
     */
    protected $table_roles='roles';

    /**
     * Имя таблицы разрешений.
     * @var string
     */
    protected $table_permissions='permissions';

    /**
     * Имя таблицы выполняющая связь между ролями и разрешениями.
     * @var string 
     */
    protected $table_role_perm='role_perm';

    /**
     * Общий массив для хранения ролей с расширениями.
     * @var array 
     */
    protected $role=array();

    /**
     * Конструктор.
     * @param integer $role_id - id роли
     */
    public function __construct($role_id=null)
    {
        //Загружаем роли
        if (!is_null($role_id))
        {
            $this->loadRolePermsId($role_id);
        }
    }
    
    /**
     * Загружает роль по указанному id
     * @param integer $role_id - id роли
     * @return boolean
     */
    public function loadRolePermsId($role_id)
    {
        //Проверяем role_id
        if ($this->validId($role_id))
        {
            return FALSE;
        }
        //Генерируем запрос ((роль)-(роль разрешения)-(разрешения))
        $sql="SELECT  
                 ".$this->table_roles.".id AS id,
                 ".$this->table_roles.".name AS name,
                 ".$this->table_roles.".description AS description,
                 ".$this->table_permissions.".id AS perm_id,
                 ".$this->table_permissions.".gp AS gp,
                 ".$this->table_permissions.".value AS value,
                 ".$this->table_permissions.".description AS perm_description                 
               FROM ".$this->table_roles."
               LEFT JOIN ".$this->table_role_perm." ON ".$this->table_roles.".id=".$this->table_role_perm.".role_id
               LEFT JOIN ".$this->table_permissions." ON ".$this->table_role_perm.".perm_id=".$this->table_permissions.".id 
	       WHERE ".$this->table_roles.".id='".intval($role_id)."'";
        //Добавляем данные в общий массив
        return $this->loadRolePerms($sql);
    }
    
    /**
     * Загружает роль по указанному имени
     * @param integer $role_name - имя роли
     * @return boolean
     */
    public function loadRolePermsName($role_name)
    {
        //Фильтруем имя
        $role_name=$this->filterTSH($role_name);
        //Генерируем запрос ((роль)-(роль разрешения)-(разрешения))
        $sql="SELECT  
                 ".$this->table_roles.".id AS id,
                 ".$this->table_roles.".name AS name,
                 ".$this->table_roles.".description AS description,
                 ".$this->table_permissions.".id AS perm_id,
                 ".$this->table_permissions.".gp AS gp,
                 ".$this->table_permissions.".value AS value,
                 ".$this->table_permissions.".description AS perm_description                 
               FROM ".$this->table_roles."
               LEFT JOIN ".$this->table_role_perm." ON ".$this->table_roles.".id=".$this->table_role_perm.".role_id
               LEFT JOIN ".$this->table_permissions." ON ".$this->table_role_perm.".perm_id=".$this->table_permissions.".id 
	       WHERE ".$this->table_roles.".name='".$role_name."'";
        //Добавляем данные в общий массив
        return $this->loadRolePerms($sql);
    }
    
    /**
     * Загружает роль
     * @param string $sql - запрос для загрузки роли
     * @return boolean
     */
    private function loadRolePerms($sql)
    {
        //Загружаем роль
        $result=Syo_Db_Pdo::getInstance()->fetchAll($sql);
        //Добавляем данные в общий массив
        if (isset($result[0]))
        {
            $this->role=array();
            $this->role['id']=$result[0]['id'];
            $this->role['name']=$result[0]['name'];
            $this->role['description']=$result[0]['description'];
            $this->role['permissions']=array();
            if (!is_null($result[0]['perm_id']))
            {
                foreach ($result as $el)
                {
                    $this->role['permissions'][$el['gp']][$el['value']]=array('id'=>$el['perm_id'],'description'=>$el['perm_description']);
                }
            }
            return TRUE;
        }
        else
        {
            return FALSE;
        }        
    }
    
    /**
     * Вставляет роль
     * @param string $name - имя роли
     * @param string $description - описание роли
     * @param boolean $load - добавить или нет созданную роль к общему массиву ролей
     * @return boolean
     */
    public function insertRole($name,$description="",$load=TRUE)
    {
        //Фильтруем данные
        $name=$this->filterTSH($name);
        $description=$this->filterTSH($description);
        //Вставляем роль в таблицу
        $sql="INSERT INTO ".$this->table_roles." (name,description) VALUES ('".$name."','".$description."')";
        if (Syo_Db_Pdo::getInstance()->query($sql))
        {
            //Загружаем роль в общий массив
            if ($load) $this->loadRolePermsId(Syo_Db_Pdo::getInstance()->getLastId());
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Обновляет данные роли в таблице.
     * @param string $name - имя роли
     * @param string $description - описание роли
     * @return boolean
     */
    public function updateRole($name,$description)
    {
        //Фильтруем данные
        $name=$this->filterTSH($name);
        $description=$this->filterTSH($description);
        //Обновляет роль в таблице
        if (isset($this->role['id']))
        {
            $sql="UPDATE ".$this->table_roles." SET name='".$name."',description='".$description."' WHERE id='".$this->role['id']."'";
            if (Syo_Db_Pdo::getInstance()->query($sql))
            {
                //Обновляем данные в общем массиве роли
                $this->role['name']=$name;
                $this->role['description']=$description;                
                return TRUE;
            }
        }
        return FALSE;        
    }
    
    /**
     * Вставляет разрешение.
     * @param string $value - имя разрешения
     * @param string $description - описание разрешения
     * @param boolean $bind - выполнить связь разрешения с ролью
     * @param string $group - группа, которой принадлежит разрешение
     * @return boolean | integer
     */
    public function insertPermission($value,$description='',$bind=TRUE,$group='undefined')
    {
        //Фильтруем поступившие данные
        $value=$this->filterTSH($value);
        $group=$this->filterTSH($group);
        $description=$this->filterTSH($description);     
        //Вставляем разрешение в таблицу
        $sql="INSERT INTO ".$this->table_permissions." (value,gp,description) VALUES ('".$value."','".$group."','".$description."')";
        if (Syo_Db_Pdo::getInstance()->query($sql))
        {
            //Возвращаем id разрешения
            $perm_id=Syo_Db_Pdo::getInstance()->getLastId();
            //Добавляем связь
            if ($bind)
            {
                $sql="INSERT INTO ".$this->table_role_perm." (role_id,perm_id) VALUES ('".$this->role['id']."','".$perm_id."')";            
                if (Syo_Db_Pdo::getInstance()->query($sql))
                {
                    //Добавляем разрешение в общий массив
                    $this->role['permissions'][$group][$value]=array('id'=>$perm_id,'description'=>$description);
                    return TRUE;
                }
            }
            //Возвращаем id добавленного разрешения
            return $perm_id;
        }
        return FALSE;
    }
    
    /**
     * Обновляет данные разрешения.
     * @param integer $value - id разрешения
     * @param string $newvalue - новое имя разрешения
     * @param string $newdescription - новое описание разрешения
     * @param string $newgroup - новоя группа
     * @return boolean
     */    
    public function updatePermission($perm_id,$newvalue,$newdescription,$newgroup)
    {
        //Проверяем $perm_id
        if ($this->validId($perm_id))
        {
            return FALSE;
        }
        //Фильтруем поступившие данные
        $newvalue=$this->filterTSH($newvalue);
        $newgroup=$this->filterTSH($newgroup);
        $newdescription=$this->filterTSH($newdescription);     
        //Обновляем разрешение
        $sql="UPDATE ".$this->table_permissions." SET value='".$newvalue."',description='".$newdescription."',gp='".$newgroup."' 
            WHERE id='".intval($perm_id)."'";
        if (Syo_Db_Pdo::getInstance()->query($sql))
        {
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Создаёт связь роли с разрешением.
     * @param integer $perm_id - id разрешения
     * @return boolean
     */
    public function insertBind($perm_id)
    {
        //Проверяем $perm_id
        if ($this->validId($perm_id))
        {
            return FALSE;
        }
        //Проверяем существования разрешения
        $sql="SELECT * FROM ".$this->table_permissions." WHERE id='".intval($perm_id)."' LIMIT 1";
        $result=Syo_Db_Pdo::getInstance()->fetchOne($sql);
        if (isset($result['id']))
        {
            //Добавляем связь
            $sql="INSERT INTO ".$this->table_role_perm." (role_id,perm_id) VALUES ('".$this->role['id']."','".$perm_id."')";            
            if (Syo_Db_Pdo::getInstance()->query($sql))
            {
                //Добавляем разрешение в общий массив
                $this->role['permissions'][$result['gp']][$result['value']]=array('id'=>$result['id'],'description'=>$result['description']);
                return TRUE;
            }
        }
        return FALSE;
    }
    
    /**
     * Удаляет разрешения.
     * @param integer $perm_id - id разрешения
     * @return boolean
     */
    public function deletePermission($perm_id)
    {
        //Проверяем perm_id
        if ($this->validId($perm_id))
        {
            return FALSE;
        }
        //Удаляем разрешение
        $sql="DELETE FROM ".$this->table_permissions." WHERE id='".$perm_id."'";
        Syo_Db_Pdo::getInstance()->query($sql); 
        //Удаляем разрешение из общего массива роли
        $this->deleteIsArrayPerm($perm_id);
        return TRUE;        
    }
    
    /**
     * Удаляет разрешение из общего массива роли
     * @param integer $perm_id - id разрешения
     * @return boolean
     */
    private function deleteIsArrayPerm($perm_id)
    {
        foreach ($this->role['permissions'] as $gkey=>$group)
        {
            foreach ($group as $pkey=>$permissions)
            {
                if ($permissions['id']==$perm_id)
                {
                    unset($this->role['permissions'][$gkey][$pkey]);
                    return TRUE;
                }
            }
        }
    }

    /**
     * Удаляет роль.
     * @param integer $role_id - id роли
     * @return boolean
     */
    public function deleteRole($role_id=null)
    {
        //Удаляет данную роль
        if (is_null($role_id))
        {
            if (isset($this->role['id']))
            {
                $sql="DELETE FROM ".$this->table_roles." WHERE id='".$this->role['id']."'";
                Syo_Db_Pdo::getInstance()->query($sql); 
                $this->role=array();
                return TRUE;
            }
            return FALSE;
        }
        //Удаляет роль по указанному role_id
        else
        {
            if ($this->validId($role_id))
            {
                return FALSE;
            }
            $sql="DELETE FROM ".$this->table_roles." WHERE id='".$role_id."'";
            Syo_Db_Pdo::getInstance()->query($sql); 
            if (isset($this->role['id']) && $this->role['id']==$role_id) $this->role=array();
            return TRUE;
        }
    }
    
    /**
     * Удаляет связь между роль и разрешением.
     * @param integer $perm_id - id разрешения
     * @return boolean
     */
    public function deleteBind($perm_id)
    {
        //Проверяем perm_id
        if ($this->validId($perm_id))
        {
            return FALSE;
        }
        //Удаляем связь
        $sql="DELETE FROM ".$this->table_role_perm." WHERE role_id='".$this->role['id']."' AND perm_id='".$perm_id."'";
        Syo_Db_Pdo::getInstance()->query($sql); 
        //Удаляем разрешение из общего массива
        $this->deleteIsArrayPerm($perm_id);
        return TRUE;        
    }
    
    /**
     * Удаляет все связи с разрешениями
     * @return boolean
     */
    public function deleteAllBind()
    {
        //Удаляем связи
        $sql="DELETE FROM ".$this->table_role_perm." WHERE role_id='".$this->role['id']."'";
        Syo_Db_Pdo::getInstance()->query($sql); 
        //Удаляем разрешение из общего массива
        $this->role['permissions']=array();
        return TRUE;        
    }

    /**
     * Проверяет доступно ли указанное разрешение.
     * @param string $value - имя разрешения
     * @param string $group - группа, которой принадлежит разрешение
     * @return boolean
     */
    public function allowed($value,$group='undefined')
    {
        return isset($this->role['permissions'][$group][$value]);
    }
    
    /**
     * Выполняет фильтрацию строки.
     * @param variant $value
     * @return variant
     */
    private function filterTSH($value)
    {
        $filters=new Syo_Filters();
        $filters->addFilter(new Syo_Filter_Trim())->addFilter(new Syo_Filter_StripTags())->addFilter(new Syo_Filter_Htmlspecialchars());
        return $filters->isFilter($value);        
    }
    
    /**
     * Выполняет проверку на целое число.
     * @param integer $value
     * @return boolean
     */
    private function validId($value)
    {
        $valid=new Syo_Validate_UInt();
        if ($valid->isVerify($value))
        {
            return TRUE;
        }
        return FALSE;
    }
}
?>