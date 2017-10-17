<?php
/**
 * Класс Syo_Auth_User дополнен функцией для работы с привилегиями.
 CREATE TABLE IF NOT EXISTS `user_role` (
  `user_id` int(10) unsigned NOT NULL,
  `role_id` int(10) unsigned NOT NULL,
  KEY `user_id` (`user_id`),
  KEY `role_id` (`role_id`)
);

ALTER TABLE `user_role`
  ADD CONSTRAINT `user_role_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_role_ibfk_2` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
 */
class Module_Auth_Privilege extends Module_Auth_User
{
    /**
     * Имя таблицы связи пользователь-роль
     * @var string 
     */
    private $table_user_role='user_role';
    
    /**
     * Имя таблицы ролей
     * @var string 
     */
    private $table_roles='roles';
    
    /**
     * Выполняет вход пользователя
     * @param boolean $ip - выполнять проверку по ip
     * @return boolean
     */
    public function login($ip=FALSE)
    {
        if (parent::login($ip))
        {
            //Догружаем роли в общий массив пользователя
            $this->loadRoles();
            return TRUE; 
        }
        return FALSE; 
    }
    
    /**
     * Проверяет, авторизован ли пользователь.
     * @return boolean
     */
    public function checkAuthorization()
    {
        if (parent::checkAuthorization())
        {
            //Догружаем роли в общий массив пользователя
            $this->loadRoles();
            return TRUE; 
        }
        return FALSE; 
    }

    /**
     * Загружает роли пользователя.
     * @return boolean
     */
    private function loadRoles()
    {
        $this->user_data['roles']=array();
        //Генерируем запрос (пользователь-роль)-(роль)
        $sql="SELECT ".$this->table_user_role.".role_id FROM ".$this->table_user_role."
                JOIN ".$this->table_roles." ON ".$this->table_user_role.".role_id=".$this->table_roles.".id
                WHERE ".$this->table_user_role.".user_id='".$this->user_data['id']."'";
        //Загружаем список ролей
        $result=Syo_Db_Pdo::getInstance()->fetchAll($sql);
        if (isset($result[0]))
        {
            //Добавляем роли в общий массив пользователя
            foreach ($result as $el)
            {
                $role=new Module_Auth_Role($el['role_id']);
                $this->user_data['roles'][$el['role_id']]=$role;
            }
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Проверяет, обладает ли пользователь указанным разрешением.
     * @param string $perm - имя разрешения
     * @param type $group - группа, которой принадлежит разрешение
     * @return boolean
     */
    public function allowedPrivilege($perm,$group='undefined')
    {
        foreach ($this->user_data['roles'] as $role)
        {
            if ($role->allowed($perm,$group))
            {
                return TRUE;
            }
        }
        return FALSE;
    }
    
    /**
     * Устанавливает связь с ролью.
     * @param integer $role_id - id роли
     * @return boolean
     */
    public function insertBind($role_id)
    {
        //Проверяем role_id
        if ($this->validId($role_id))
        {
            return FALSE;
        }
        //Проверяем на существование роли
        $sql="SELECT * FROM ".$this->table_roles." WHERE id='".intval($role_id)."' LIMIT 1";
        $result=Syo_Db_Pdo::getInstance()->fetchOne($sql);
        if (isset($result['id']))
        {
            //Добавляем связь
            if (isset($this->user_data['id']))
            {
                $sql="INSERT INTO ".$this->table_user_role." (user_id,role_id) VALUES ('".$this->user_data['id']."','".$role_id."')";  
                if (Syo_Db_Pdo::getInstance()->query($sql))
                {
                    $role=new Syo_Auth_Role($role_id);
                    //Добавляем роль в общий массив пользователя
                    $this->user_data['roles'][$role_id]=$role;
                    return TRUE;
                }
            }
        }
        return FALSE;
    }
    
    /**
     * Проверяет обладает ли пользователь заданной ролью.
     * @param integer $role_id - id роли
     * @return boolean
     */
    public function isRole($role_id)
    {
        return isset($this->user_data['roles'][$role_id]);
    }
    
    /**
     * Удаляет связь пользователя с ролью
     * @param integer $role_id - id роли
     * @return boolean
     */
    public function deleteBind($role_id)
    {
        //Проверяем role_id
        if ($this->validId($role_id))
        {
            return FALSE;
        }
        //Удаляем связь
        if (isset($this->user_data['id']))
        {
            $sql="DELETE FROM ".$this->table_user_role." WHERE user_id='".$this->user_data['id']."' AND role_id='".$role_id."'";
            Syo_Db_Pdo::getInstance()->query($sql); 
            //Удаляем связь из общего массива пользователя
            unset($this->user_data['roles'][$role_id]);
            return TRUE;
        }
        return FALSE;        
    } 

    /**
     * Удаляет все связи пользователя с ролями
     * @return boolean
     */
    public function deleteAllBind()
    {
        if (isset($this->user_data['id']))
        {
            $sql="DELETE FROM ".$this->table_user_role." WHERE user_id='".$this->user_data['id']."'";
            Syo_Db_Pdo::getInstance()->query($sql); 
            $this->user_data['roles']=array();
            return TRUE;
        }
        return FALSE;        
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