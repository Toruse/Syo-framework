<?php
/**
 * Класс для работы с авторизацией пользователя.
 * 
 *  
 * Create 13.05.2015
 * Update 13.05.2015
 * 
 * @author Torus <notpad@mail.ru>
 * @version 2.0.0
 * 
 * @package module
 * @subpackage auth
 * 
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `login` varchar(30) NOT NULL,
  `password` varchar(32) NOT NULL,
  `email` tinytext,
  `dateregister` datetime NOT NULL,
  `lastvisit` datetime NOT NULL,
  `block` enum('block','unblock') DEFAULT 'unblock',
  PRIMARY KEY (`id`),
  KEY `login` (`login`)
);

CREATE TABLE IF NOT EXISTS `users_info` (
  `user_id` int(10) unsigned NOT NULL,
  `name` tinytext NOT NULL,
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `user_id` (`user_id`)
);

CREATE TABLE IF NOT EXISTS `user_hashes` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int(10) unsigned NOT NULL,
  `hash` varchar(32) NOT NULL,
  `ip` int(11) unsigned NOT NULL DEFAULT '0',
  `date` datetime NOT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `hash` (`hash`)
);

ALTER TABLE `users_info`
  ADD CONSTRAINT `users_info_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE `user_hashes`
  ADD CONSTRAINT `user_hashes_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

 */
class Module_Auth_User
{

    /**
     * Имя таблицы "Пользователей"
     * @var string 
     */
    private $table='users';
    
    /**
     * Таблица для хранения хеша пользователя.
     * @var string
     */
    private $table_hashes='user_hashes';
    
    /**
     * Имя таблицы с дополнительной информацией о пользователе .
     * @var string 
     */
    private $table_info='users_info';
    
    /**
     * Массив хранит информацию о пользователе.
     * @var array 
     */
    protected $user_data=array();
    
    /**
     * Pattern для проверки логина.
     * @var string
     */
    private $login_pattern='/^[a-z0-9_-]{3,30}$/';
    
    /**
     * Содержит список возникших ошибок.
     * @var array 
     */
    private $error=null;
    
    /**
     * Конструктор
     * @param string $nametable - имя таблицы с пользователями
     * @param string $nametableinfo - имя таблицы с дополнительной информацией о пользователе
     */
    public function __construct($nametable=null,$nametableinfo=null)
    {
        if (!is_null($nametable))
        {
            $this->table=$nametable;
        }
        if (!is_null($nametableinfo))
        {
            $this->table_info=$nametableinfo;
        }
    }

    /**
     * Возвращает данные о пользователе.
     * @return array
     */
    public function getData()
    {
       return $this->user_data;
    }

    /**
     * Догружает данных о пользователе, и возвращает их.
     * @return array
     */
    public function getInfoData()
    {
       //Выполняем загрузку данных из базы
       $sql="SELECT * FROM ".$this->table_info." WHERE user_id='".$this->user_data['id']."' LIMIT 1";
       $result[$this->table_info]=Syo_Db_Pdo::getInstance()->fetchOne($sql);
       //Добавляем данные о пользователе в общий массив
       $this->transferData($result);
       return $this->user_data;
    }
    
    /**
     * Метод для добавления переменной.
     * @param string $varname - имя переменной.
     * @param variant $value - значение переменной.
     * @return boolean
     */
    function __set($varname,$value)
    {
        $this->user_data[$varname]=$value;
        return true;
    }

    /**
     * Метод для получения значения переменной.
     * @param string $varname - имя переменной.
     * @return variant - значение переменной.
     */
    function __get($varname)
    {
        if (isset($this->user_data[$varname])==false)
        {
                return null;
        }
        return $this->user_data[$varname];
    }

    /**
     * Удаляет по указанному ключу переменную.
     * @param string $varname - имя переменной.
     */
    function __unset($varname) 
    {
        unset($this->user_data[$varname]);
    }

    /**
     * Проверяет переменную на существование.
     * @param string $varname - имя переменной.
     * @return boolean
     */
    function __isset($varname) 
    {
        return isset($this->user_data[$varname]);
    }
    
    /**
     * Устанавливает данные о пользователе.
     * @param array $data
     */
    public function setData($data)
    {
        $this->transferData($data);
    }

    /**
     * Проверяет корректность передаваемых данных в таблицу
     * @return boolean
     */
    private function check()
    {
        if (isset($this->user_data['id']))
        {
            $valid=new Syo_Validate_UInt();
            if ($valid->isVerify($this->user_data['id']))
            {
                return FALSE;
            }
        }
        if (isset($this->user_data['login']))
        {
            $valid=new Syo_Validate_Preg(array('parameter'=>array('pattern'=>$this->login_pattern)));
            if ($valid->isVerify($this->user_data['login']))
            {
                return FALSE;
            }
        }
        else
        {
            return FALSE;
        }
        if (isset($this->user_data['password']))
        {
            $valid=new Syo_Validate_NoEmpty();
            if ($valid->isVerify($this->user_data['password']))
            {
                return FALSE;
            }
        }
        else
        {
            return FALSE;
        }
        if (isset($this->user_data['email']))
        {
            $valid=new Syo_Validate_Email();
            if ($valid->isVerify($this->user_data['email']))
            {
                return FALSE;
            }
        }
        if (isset($this->user_data['ip']))
        {
            $valid=new Syo_Validate_Ip();
            if ($valid->isVerify($this->user_data['ip']))
            {
                return FALSE;
            }
        }
        if (isset($this->user_data['dateregister']))
        {
            $valid=new Syo_Validate_Date();
            if ($valid->isVerify($this->user_data['dateregister']))
            {
                return FALSE;
            }
        }
        if (isset($this->user_data['lastvisit']))
        {
            $valid=new Syo_Validate_Date();
            if ($valid->isVerify($this->user_data['dateregister']))
            {
                return FALSE;
            }
        }
        if (isset($this->user_data['block']))
        {
            if (!in_array($this->user_data['block'],array("block","unblock")))
            {
                return FALSE;
            }
        }
        return TRUE;
    }
    
    /**
     * Убираем из полученных данных теги и спецсимволы или экранируем их.
     */
    public function filter()
    {
        $filters=new Syo_Filters();
        $filters->addFilter(new Syo_Filter_Trim())->addFilter(new Syo_Filter_Htmlspecialchars());
        if (isset($this->user_data['login']))
        {
            $this->user_data['login']=$filters->isFilter($this->user_data['login']);
        }
        if (isset($this->user_data['password']))
        {
            $this->user_data['password']=$filters->isFilter($this->user_data['password']);
        }
        if (isset($this->user_data['email']))
        {
            $this->user_data['email']=$filters->isFilter($this->user_data['email']);
        }
        if (isset($this->user_data['ip']))
        {
            $this->user_data['ip']=$filters->isFilter($this->user_data['ip']);
        }
        if (isset($this->user_data['dateregister']))
        {
            $date=new Syo_Filter_Date(array('parameter'=>array('format'=>'Y-m-d H:i:s')));
            $this->user_data['dateregister']=$date->isFilter($this->user_data['dateregister']);
        }
        if (isset($this->user_data['lastvisit']))
        {
            $date=new Syo_Filter_Date(array('parameter'=>array('format'=>'Y-m-d H:i:s')));
            $this->user_data['lastvisit']=$date->isFilter($this->user_data['lastvisit']);
        }
    }
    
    /**
     * Выполняет регистрацию пользователя.
     * @return boolean
     */
    public function register()
    {
        //Фильтруем данные
        $this->filter();
        //Проверяем данные
        if ($this->check())
        {
            //Проверяем существование пользователя
            if (!$this->checkExistence())
            {
                //Шифруем пароль 
                $password=$this->hashPassword($this->user_data['password']);
                //Добавляем пользователя в таблицу
                $sql="INSERT INTO ".$this->table." SET login='".$this->user_data['login']."',password='".$password."',dateregister=NOW()".",lastvisit=NOW(),block='unblock'";
                if (isset($this->user_data['email'])) $sql.=",email='".$this->user_data['email']."'";
                if (Syo_Db_Pdo::getInstance()->query($sql))
                {
                    //Пользователь успешно добавлен
                    //Передаём в общий массив id пользователя
                    $this->user_data['id']=Syo_Db_Pdo::getInstance()->getLastId();
                    $this->user_data[$this->table_info]['user_id']=$this->user_data['id'];
                    //Если есть дополнительные данные о пользователе, сохраняем их.
                    $keys=array_keys($this->user_data[$this->table_info]);
                    $values=array_values($this->user_data[$this->table_info]);
                    $sql="INSERT INTO ".$this->table_info." (".implode(",",$keys).") VALUES ('".implode("','",$values)."');";
                    if (Syo_Db_Pdo::getInstance()->query($sql))
                    {
                        return TRUE;
                    }
                    else
                    {
                        return FALSE;     
                    }
                }
                else
                {
                    return FALSE;     
                }
            }
            else
            {
                return FALSE;
            }
        }
        else 
        {
            return FALSE;
        }
        return FALSE;
    }
    
    /**
     * Выполняет шифрование пароля.
     * @param string $password
     * @return string
     */
    public function hashPassword($password)
    {
       return md5(sha1($password)."syo".md5(sha1($password)));
    }
    
    /**
     * Проверяет наличие пользователя в таблице.
     * @return boolean
     */
    private function checkExistence()
    {
        $sql="SELECT COUNT(id) as count FROM ".$this->table." WHERE login='".$this->user_data['login']."'";
        $result=Syo_Db_Pdo::getInstance()->fetchOne($sql);
        if ($result['count']>0)
        {
            return TRUE;
        }
        return FALSE;     
    }
    
    /**
     * Генерирует hash.
     * @return string
     */
    private function generateCode()
    {
        return md5(sha1(uniqid("")));
    }
    
    /**
     * Выполняет вход пользователя
     * @param boolean $ip - выполнять проверку по ip
     * @return boolean
     */
    public function login($ip=FALSE)
    {
        //Очищаем cookie
        $this->deleteCookie();
        //Выполняем фильтрацию данных
        $this->filter();
        //Проверяем данные
        if ($this->check())
        {
            //Находим пользователя
            $sql="SELECT * FROM ".$this->table." WHERE login='".$this->user_data['login']."' LIMIT 1";
            $result=Syo_Db_Pdo::getInstance()->fetchOne($sql);
            //Выполняем проверку пароля и заблокирован ли пользователь
            if (isset($result['password']))
            {
                if (($result['password']==$this->hashPassword($this->user_data['password'])) && ($result['block']=='unblock'))
                {
                    //Генерируем hash
                    $hash=$this->generateCode();
                    //Сохраняем hash и ip
                    $sql="INSERT INTO ".$this->table_hashes." SET hash='".$hash."', user_id='".$result['id']."', date=NOW()";
                    if ($ip)
                        $sql.=",ip=INET_ATON('".$_SERVER['REMOTE_ADDR']."')";
                    else
                        $sql.=",ip='0'";                        
                    if (Syo_Db_Pdo::getInstance()->query($sql)==FALSE)
                        return FALSE;
                    //Сохраняем дату визита
                    $sql="UPDATE ".$this->table." SET lastvisit=NOW() WHERE id='".$result['id']."'";
                    if (Syo_Db_Pdo::getInstance()->query($sql)==FALSE)
                        return FALSE;
                    
                    //Передаём в общий массив загруженные данные
                    $this->transferData($result);
                    //Устанавливаем cookie авторизации
                    Syo_Cookie::getInstance()->hash=$hash;
                    return TRUE;
                }
                else
                {
                    return FALSE;
                }
            }
            else
            {
                return FALSE;
            }
        }
        else
        {
            return FALSE; 
        }
    }
    
    /**
     * Выполняем выход пользователя
     */
    public function logout()
    {
        $this->deleteHash();
        $this->deleteCookie();
    }
    
    /**
     * Добавляет данные о пользователе в общий массив.
     * @param array $data
     */
    private function transferData($data)
    {
        $this->user_data=array_merge($this->user_data,$data);
    }
    
    /**
     * Проверяет, авторизован ли пользователь.
     * @return boolean
     */
    public function checkAuthorization()
    {
        //Проверяем на существование cookie
        if (isset(Syo_Cookie::getInstance()->hash))
        {
            $hash=Syo_Cookie::getInstance()->hash;
            //Выполняем проверку hash
            $valid=new Syo_Validate_Alnum();
            if ($valid->isVerify($hash))
            {
                return FALSE;
            }
            //Находим пользователя
            $sql="SELECT u.*,u_hash.id AS hash_id,u_hash.hash AS hash,INET_NTOA(u_hash.ip) AS ip "
                ."FROM ".$this->table." AS u LEFT JOIN ".$this->table_hashes." AS u_hash ON u.id=u_hash.user_id "
                ."WHERE u_hash.hash='".$hash."' LIMIT 1";
            $result=Syo_Db_Pdo::getInstance()->fetchOne($sql);
            //Выполняем проверку id, hash, ip
            if (isset($result['hash']))
            {
                if (($result['hash']==$hash) && (($result['ip']==$_SERVER['REMOTE_ADDR']) || ($result['ip']=="0.0.0.0")))
                {
                    //Добавляем данные в общий массив
                    $this->transferData($result);
                    //Обновляем cookie
                    Syo_Cookie::getInstance()->hash=$result['hash']; //Закомментировать? если path Cookie отличается от '/'
                    return TRUE;
                }
                else 
                {
                    //Авторизация не пройдена, зачищаем cookie
                    $this->deleteCookie();
                    return FALSE;
                }
            }
            else
            {
                return FALSE;
            }
        }
        else
        {
            return FALSE;
        }
    }

    /**
     * Удаляет хеш авторизации из базы данных.
     */
    public function deleteHash()
    {
        $sql="DELETE FROM ".$this->table_hashes." WHERE hash='".$this->user_data['hash']."'";
        Syo_Db_Pdo::getInstance()->query($sql); 
        return TRUE;
    }
    
    /**
     * Удаляет все хеши авторизации пользователя из базы данных.
     * @param integer $user_id - id пользователя
     * @return boolean
     */
    public function deleteHashAll($user_id=null)
    {
        //Удаляем хеш авторизации данного пользователя
        if (is_null($user_id))
        {
            if (isset($this->user_data['id']))
            {
                $sql="DELETE FROM ".$this->table_hashes." WHERE user_id='".$this->user_data['id']."'";
                Syo_Db_Pdo::getInstance()->query($sql); 
                return TRUE;
            }
            else
            {
                return FALSE;                
            }
        }
        //Удаляем хеш авторизации пользователя по указанному id
        else
        {
            $sql="DELETE FROM ".$this->table_hashes." WHERE user_id='".$user_id."'";
            Syo_Db_Pdo::getInstance()->query($sql); 
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Удаляет cookie авторизации.
     */
    public function deleteCookie()
    {
        unset(Syo_Cookie::getInstance()->hash);
    }
    
    /**
     * Удаляет пользователя из таблицы.
     * @param string $login - логин пользователя
     * @return boolean
     */
    public function deleteUser($login=null)
    {
        //Удаляем данного пользователя
        if (is_null($login))
        {
            if (isset($this->user_data['login']))
            {
                $valid=new Syo_Validate_Preg(array('parameter'=>array('pattern'=>$this->login_pattern)));
                if ($valid->isVerify($this->user_data['login']))
                {
                    return FALSE;
                }            
                $sql="DELETE FROM ".$this->table." WHERE login='".$this->user_data['login']."'";
                Syo_Db_Pdo::getInstance()->query($sql); 
                return TRUE;
            }
            else
            {
                return FALSE;                
            }
        }
        //Удаляем пользователя по указанному login
        else
        {
            $valid=new Syo_Validate_Preg(array('parameter'=>array('pattern'=>$this->login_pattern)));
            if ($valid->isVerify($login))
            {
                return FALSE;
            }
            $sql="DELETE FROM ".$this->table." WHERE login='".$login."'";
            Syo_Db_Pdo::getInstance()->query($sql); 
            return TRUE;
        }
        return FALSE;
    }
    
    /**
     * Разблокирует пользователя.
     * @return boolean
     */
    public function unBlock()
    {
        $sql="UPDATE ".$this->table." SET block='unblock' WHERE login='".$this->user_data['login']."'";
        if (Syo_Db_Pdo::getInstance()->query($sql)==FALSE)
        {
            return FALSE;
        }
        return TRUE;
    }
    
    /**
     * Блокирует пользователя.
     * @return boolean
     */
    public function Block()
    {
        $sql="UPDATE ".$this->table." SET block='block' WHERE login='".$this->user_data['login']."'";
        if (Syo_Db_Pdo::getInstance()->query($sql)==FALSE)
        {
            return FALSE;
        }
        $this->deleteCookie();
        return TRUE;        
    }
}
?>