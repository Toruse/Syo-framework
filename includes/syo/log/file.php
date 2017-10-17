<?php
/**
 * Класс для работы с файловыми логами.
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
class Syo_Log_File extends Syo_Log_Abstract
{
    /**
     * Хранит указатель на файл.
     * @var pointer 
     */
    private $file=NULL;
    
    /**
     * Конструктор. Устанавливает config, открывает файл для записи логов.
     * @param array $config
     * @return boolean
     * @throws Syo_Exception
     */
    public function __construct($config=NULL) 
    {
        parent::__construct($config);
        if (!$this->openFile())
            throw new Syo_Exception('The file could not be written to.');
        return TRUE;
    }
    
    /**
     * Записывает лог.
     * @param integer $type - тип сообщения
     * @param string $message - сообщение
     * @param string $id - идентификатор или дополнительная информация о сообщении
     * @return boolean
     */
    public function log($type,$message,$id=NULL)
    {
        if ($this->getConfigWrite() & $type)
        {
            $message.=(is_null($id)?'':" ($id)");
            return $this->writeFile($this->formatMessage($type,$message));        
        }
        return FALSE;
    }
    
    /**
     * Возвращает путь к файлу логов.
     * @return string
     */
    private function getPathFileName()
    {
        return $this->config['path'].$this->config['filename'];
    }
    
    /**
     * Возвращает путь директории для хранения логов
     * @return string
     */
    private function getPath()
    {
        return $this->config['path'];
    }
    
    /**
     * Возвращает отформатированную строку сообщения лога.
     * @param integer $type - тип сообщения
     * @param string $message - сообщение
     * @return string
     */
    private function formatMessage($type,$message)
    {
        return "[{$this->getTimestamp()}] [{$this->name_type[$type]}] {$message}".PHP_EOL;
    }
    
    /**
     * Возвращает текущее время для записи сообщения лога.
     * @return string
     */
    private function getTimeStamp()
    {
        $originalTime=microtime(true);
        $micro=sprintf("%06d",($originalTime-floor($originalTime))*1000000);
        $date=new DateTime(date('Y-m-d H:i:s.'.$micro,$originalTime));
        return $date->format($this->config['formatdate']);
    }
    
    /**
     * Открывает файл для записи логов.
     * @return mixed
     */
    private function openFile()
    {
        //Получаем путь к директории для записи логов.
        $path=$this->getPath();
        //Проверяем на существование директории, если отсутствует, создаём её.
        if (!file_exists($path)) mkdir($this->getPath(),0777,true); 
        if(file_exists($path) && !is_writable($path)) return FALSE;
        //Открываем файл на запись
        $this->file=fopen($this->getPathFileName(),'a');
        if (!$this->file) return FALSE;
        return TRUE;
    }
    
    /**
     * Записывает сообщение лога в файл.
     * @param string $message - сообщение
     * @return boolean
     */
    private function writeFile($message)
    {
        if (!is_null($this->file) && fwrite($this->file,$message))
        {
            $this->lastline=trim($message);
            $this->linecount++;
            return TRUE;
        }
        return FALSE;
    }

    /**
     * Закрывает файл с логами.
     */
    private function closeFile()
    {
        if ($this->file) 
            fclose($this->file);
    }
    
    /**
     * Деструктор.
     * @return boolean
     */
    public function __destruct()
    {
        //Закрываем файл с логами.
        $this->closeFile();
        return TRUE;
    }
}
?>