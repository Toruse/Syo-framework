<?php
/**
 * Класс для генерации одноразовой операции.
CREATE TABLE IF NOT EXISTS `token` (
  `token` char(40) NOT NULL,
  `tstamp` int(10) unsigned NOT NULL,
  PRIMARY KEY (`token`)
);
 */
class Syo_Token
{
    /**
     * Имя таблицы с деревом.
     * @var string 
     */
    protected $table=NULL;
    
    /**
     * Имя таблицы с деревом.
     * @var string 
     */
    protected $token=NULL;
    
    /**
     * Время жизни token
     * @var int 
     */
    protected $delta=86400;

    /**
     * Одиночка.
     * @var Syo_Token 
     */
    protected static $instance;

    /**
     * Гарантируем, что у класса есть только один экземпляр, и предоставляет к нему глобальную точку доступа. 
     * @return Syo_Token
     */
    public static function getInstance()
    {
        if (self::$instance===null)
        {
            self::$instance=new Syo_Token();
        }
        return self::$instance;
    }
    
    /**
     * Конструктор.
     */
    protected function __construct()
    {
        //Указываем название таблицы.
        $this->table="token";
    }

    /**
     * Генерирует token.
     * @return string|boolean
     */
    public function getToken()
    {
        //Удаляем устаревшие tokenы.
        $this->endedToken();
        //Генерируем token.
        $this->token=sha1(uniqid('token',true));
        //Добавляем token в таблицу
        $sql="INSERT INTO ".$this->table." (token,tstamp) VALUES ('".$this->token."','".$_SERVER["REQUEST_TIME"]."');";
        if (Syo_Db_Pdo::getInstance()->query($sql))
        {
            return $this->token;
        }
        else
        {
            return FALSE;     
        }
    }
    
    /**
     * Удаляет устаревшие tokenы.
     */
    private function endedToken()
    {
        $sql="DELETE FROM ".$this->table." WHERE tstamp<=".($_SERVER["REQUEST_TIME"]-$this->delta);
        Syo_Db_Pdo::getInstance()->query($sql);        
    }
    
    /**
     * Проверяет доступность (активность) tokenа.
     * @param string $token
     * @return boolean
     */
    public function isVerify($token)
    {
        $this->token=$token;
        $sql="SELECT * FROM ".$this->table." WHERE token='".$this->token."'";
        if (Syo_Db_Pdo::getInstance()->fetchOne($sql))
        {
            $this->deleteToken();
            return TRUE;
        }
        else
        {
            return FALSE;     
        }
    }
    
    /**
     * Удаляет token из таблицы.
     */
    private function deleteToken()
    {
        $sql="DELETE FROM ".$this->table." WHERE token='".$this->token."'";
        Syo_Db_Pdo::getInstance()->query($sql);        
    }
}
?>