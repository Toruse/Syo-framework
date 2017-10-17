<?php
/**
 * Класс контейнер для работы с логами.
 * 
 * Create 24.05.2015
 * Update 28.05.2015
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.0
 * 
 * @package syo
 * @subpackage log
 */
class Syo_Log
{
    /**
     * Одиночка.
     * @var Syo_Log 
     */
    protected static $instance;
    
    /**
     * Список классов логов.
     * @var array 
     */
    private $list=array();
    
    /**
     * Возвращает единственный экземпляр класса. Singleton
     * @return Syo_Log
     */
    public static function getInstance()
    {
        if (self::$instance===null)
        {
            self::$instance=new Syo_Log();
        }
        return self::$instance;
    }
    
    /**
     * Конструктор.
     * Загружает конфигурацию логов.
     */
    public function __construct($config=NULL) 
    {
        if (is_null($config))
        {
            //Получаем конфигурацию приложения
            $app_config=Syo_Registry::getInstance()->get('config');
            $config=$app_config['application']['log'];
        }
        //Создаём классы логов, и добавляем в список.
        foreach ($config as $key=>$value)
            if (isset($value['class']) && class_exists($value['class']))
            {
                $classname=$value['class'];
                unset($value['class']);
                $this->addLog(new $classname($value),$key);
            }
    }
    
    /**
     * Добавляет или заменяет класс лога в списке.
     * @param variant $log - класс лог
     * @param string $name - имя лога
     */
    public function addLog($log,$name=NULL)
    {
        if (is_null($name))
            $this->list[]=$log;
        else
            $this->list[$name]=$log;
    }
    
    /**
     * Записывает лог.
     * @param integer $type - тип сообщения
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @param string $uselog - имя лога
     * @return boolean
     */
    public function log($type,$message,$id=NULL,$uselog=NULL)
    {
        //Перебираем список логов и сохраняем сообщение
        if (is_null($uselog))
            foreach ($this->list as $key=>$log)
                $log->log($type,$message,$id);
        //Cохраняем сообщение указанному логу
        elseif (isset($this->list[$uselog]))
            $this->list[$uselog]->log($type,$message,$id);
        else 
            return FALSE;
        return TRUE;
    }
    
    /**
     * Записывает аварийный лог.
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @param string $uselog - имя лога
     * @return boolean
     */
    public function emergency($message,$id=NULL,$uselog=NULL)
    {
        //Перебираем список логов и сохраняем сообщение
        if (is_null($uselog))
            foreach ($this->list as $key=>$log)
                $log->emergency($message,$id);
        //Cохраняем сообщение указанному логу
        elseif (isset($this->list[$uselog]))
            $this->list[$uselog]->emergency($message,$id);
        else 
            return FALSE;
        return TRUE;        
    }

    /**
     * Записывает тревожный лог.
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @param string $uselog - имя лога
     * @return boolean
     */
    public function alert($message,$id=NULL,$uselog=NULL)
    {
        //Перебираем список логов и сохраняем сообщение
        if (is_null($uselog))
            foreach ($this->list as $key=>$log)
                $log->alert($message,$id);
        //Cохраняем сообщение указанному логу
        elseif (isset($this->list[$uselog]))
            $this->list[$uselog]->alert($message,$id);
        else 
            return FALSE;
        return TRUE;        
    }
    
    /**
     * Записывает критический лог.
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @param string $uselog - имя лога
     * @return boolean
     */
    public function critical($message,$id=NULL,$uselog=NULL)
    {
        //Перебираем список логов и сохраняем сообщение
        if (is_null($uselog))
            foreach ($this->list as $key=>$log)
                $log->critical($message,$id);
        //Cохраняем сообщение указанному логу
        elseif (isset($this->list[$uselog]))
            $this->list[$uselog]->critical($message,$id);
        else 
            return FALSE;
        return TRUE;        
    }
    
    /**
     * Записывает лог об ошибке.
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @param string $uselog - имя лога
     * @return boolean
     */
    public function error($message,$id=NULL,$uselog=NULL)
    {
        //Перебираем список логов и сохраняем сообщение
        if (is_null($uselog))
            foreach ($this->list as $key=>$log)
                $log->error($message,$id);
        //Cохраняем сообщение указанному логу
        elseif (isset($this->list[$uselog]))
            $this->list[$uselog]->error($message,$id);
        else 
            return FALSE;
        return TRUE;        
    }
    
    /**
     * Записывает предупреждающий лог.
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @param string $uselog - имя лога
     * @return boolean
     */
    public function warning($message,$id=NULL,$uselog=NULL)
    {
        //Перебираем список логов и сохраняем сообщение
        if (is_null($uselog))
            foreach ($this->list as $key=>$log)
                $log->warning($message,$id);
        //Cохраняем сообщение указанному логу
        elseif (isset($this->list[$uselog]))
            $this->list[$uselog]->warning($message,$id);
        else 
            return FALSE;
        return TRUE;        
    }

    /**
     * Записывает уведомляющий лог.
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @param string $uselog - имя лога
     * @return boolean
     */
    public function notice($message,$id=NULL,$uselog=NULL)
    {
        //Перебираем список логов и сохраняем сообщение
        if (is_null($uselog))
            foreach ($this->list as $key=>$log)
                $log->notice($message,$id);
        //Cохраняем сообщение указанному логу
        elseif (isset($this->list[$uselog]))
            $this->list[$uselog]->notice($message,$id);
        else 
            return FALSE;
        return TRUE;        
    }

    /**
     * Записывает информационный лог.
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @param string $uselog - имя лога
     * @return boolean
     */
    public function info($message,$id=NULL,$uselog=NULL)
    {
        //Перебираем список логов и сохраняем сообщение
        if (is_null($uselog))
            foreach ($this->list as $key=>$log)
                $log->info($message,$id);
        //Cохраняем сообщение указанному логу
        elseif (isset($this->list[$uselog]))
            $this->list[$uselog]->info($message,$id);
        else 
            return FALSE;
        return TRUE;        
    }
    
    /**
     * Записывает отладочный лог.
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @param string $uselog - имя лога
     * @return boolean
     */
    public function debug($message,$id=NULL,$uselog=NULL)
    {
        //Перебираем список логов и сохраняем сообщение
        if (is_null($uselog))
            foreach ($this->list as $key=>$log)
                $log->debug($message,$id);
        //Cохраняем сообщение указанному логу
        elseif (isset($this->list[$uselog]))
            $this->list[$uselog]->debug($message,$id);
        else 
            return FALSE;
        return TRUE;        
    }
}
?>