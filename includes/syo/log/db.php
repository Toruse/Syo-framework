<?php
/**
 * Класс для работы с логами в базе данных.
 * 
 * Create 28.05.2015
 * Update 14.06.2015
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.1
 * 
 * @package syo
 * @subpackage log
 */
/**
CREATE TABLE IF NOT EXISTS `log` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `data_create` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `user_id` int(10) unsigned NOT NULL,
  `type` varchar(32) NOT NULL DEFAULT 'INFO',
  `message` text NOT NULL,
  PRIMARY KEY (`id`)
);
 */
class Syo_Log_Db extends Syo_Log_Abstract
{   
    /**
     * Записывает лог.
     * @param integer $type - тип сообщения
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @return boolean
     */
    public function log($type,$message,$id=0)
    {
        if ($this->getConfigWrite() & $type)
            return $this->insertDB($this->generateSQL($type,$message,$id));        
        return FALSE;
    }
    
    /**
     * Генерирует SQL-запрос на добавление сообщение лога в базу данных.
     * @param integer $type - тип сообщения
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @return string - SQL-запрос
     */
    private function generateSQL($type,$message,$id)
    {
        if (isset($this->config['table']) && isset($this->name_type[$type]))
        {
            $filter=new Syo_Filter_Slashes();
            $message=$filter->isFilter($message);
            $id=$filter->isFilter($id);
            return "INSERT INTO ".$this->config['table']
                ." (type,message,user_id)"
                ." VALUES ('".$this->name_type[$type]."','".$message."','".$id."')";
        }
        return FALSE;
    }
    
    /**
     * Вставляет в базу данных сообщение лога.
     * @param string $sql - SQL-запрос
     * @return boolean
     */
    private function insertDB($sql)
    {
        if (empty($sql)) return FALSE;
        $result=Syo_Db_Pdo::getInstance()->query($sql);
        if ($result->rowCount()==0)
            return FALSE;
        return TRUE;
    }
}
?>