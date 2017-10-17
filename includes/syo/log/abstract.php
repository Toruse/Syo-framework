<?php
/**
 * Базовый класс для работы с логами.
 * 
 * Create 27.05.2015
 * Update 28.05.2015
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.0
 * 
 * @package syo
 * @subpackage log
 */
abstract class Syo_Log_Abstract
{
    /**
     * Хранит конфигурацию лога.
     * @var array
     */
    protected $config=NULL;
    
    /**
     * Хранит последнюю добавленную строку.
     * @var string
     */
    protected $lastline=NULL;
    
    /**
     * Количество добавленных строк.
     * @var integer
     */
    protected $linecount=0;
    
    /**
     * Типы сообщения.
     */
    const LOG_NONE=0;
    const LOG_EMERGENCY=1;
    const LOG_ALERT=2;
    const LOG_CRITICAL=4;
    const LOG_ERROR=8;
    const LOG_WARNING=16;
    const LOG_NOTICE=32;
    const LOG_INFO=64;
    const LOG_DEBUG=128;
    const LOG_ALL=255;
        
    /**
     * Названия типов сообщения.
     * @var array
     */
    protected $name_type=array(
        self::LOG_EMERGENCY=>'EMERGENCY',
        self::LOG_ALERT=>'ALERT',
        self::LOG_CRITICAL=>'CRITICAL',
        self::LOG_ERROR=>'ERROR',
        self::LOG_WARNING=>'WARNING',
        self::LOG_NOTICE=>'NOTICE',
        self::LOG_INFO=>'INFO',
        self::LOG_DEBUG=>'DEBUG'
    );
    
    /**
     * Конструктор. Устанавливает настройки для класса лога.
     * @param array $config
     */
    public function __construct($config=NULL)
    {
        if (!is_null($config))
            $this->config=$config;
    }
    
    /**
     * Записывает лог.
     * @param integer $type - тип сообщения
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @return boolean
     */
    abstract public function log($type,$message,$id=NULL);

    /**
     * Записывает аварийный лог.
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @return boolean
     */
    public function emergency($message,$id=NULL)
    {
        return $this->log(self::LOG_EMERGENCY,$message,$id);
    }

    /**
     * Записывает тревожный лог.
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @return boolean
     */
    public function alert($message,$id=NULL)
    {
        return $this->log(self::LOG_ALERT,$message,$id);
    }
    
    /**
     * Записывает критический лог.
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @return boolean
     */
    public function critical($message,$id=NULL)
    {
        return $this->log(self::LOG_CRITICAL,$message,$id);        
    }
    
    /**
     * Записывает лог об ошибке.
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @return boolean
     */
    public function error($message,$id=NULL)
    {
        return $this->log(self::LOG_ERROR,$message,$id);
    }
    
    /**
     * Записывает предупреждающий лог.
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @return boolean
     */
    public function warning($message,$id=NULL)
    {
        return $this->log(self::LOG_WARNING,$message,$id);        
    }

    /**
     * Записывает уведомляющий лог.
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @return boolean
     */
    public function notice($message,$id=NULL)
    {
        return $this->log(self::LOG_NOTICE,$message,$id);
    }

    /**
     * Записывает информационный лог.
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @return boolean
     */
    public function info($message,$id=NULL)
    {
        return $this->log(self::LOG_INFO,$message,$id);
    }
    
    /**
     * Записывает отладочный лог.
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @return boolean
     */
    public function debug($message,$id=NULL)
    {
        return $this->log(self::LOG_DEBUG,$message,$id);
    }

    /**
     * Возвращает из конфига значение,  какие сообщения записывать.
     * @return integer
     */
    protected function getConfigWrite()
    {
        return ((isset($this->config['write']))?$this->config['write']:self::LOG_ALL);
    }
}
?>