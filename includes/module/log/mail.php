<?php
/**
 * Класс для работы с почтовыми логами.
 * 
 * Create 11.06.2015
 * Update 11.06.2015
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.0
 * 
 * @package syo
 * @subpackage log
 */
class Module_Log_Mail extends Syo_Log_Abstract
{
    /**
     * Класс e-mail (PHPMailer).
     * @var Module_Mailer_Native 
     */
    private $mail=NULL;
    
    /**
     * Конструктор. Устанавливает config, Создаёт класс mail.
     * @param array $config
     * @return boolean
     * @throws Syo_Exception
     */
    public function __construct($config=NULL) 
    {
        parent::__construct($config);
        $this->createMail();
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
            return $this->addToEmail($this->formatMessage($type,$message));        
        }
        return FALSE;
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
     * Создаёт класс Mailer для отправки сообщений логов на почтовый ящик. 
     * @return boolean
     */
    private function createMail()
    {
        //Создаём класс PHPMailer
        $this->mail=new Module_Mailer_Native();
        //Указываем отправку через SMTP
        $this->mail->isSMTP();
        $this->mail->SMTPDebug=0;
        $this->mail->Debugoutput='html';
        $this->mail->SMTPKeepAlive = TRUE;
        //Загружаем настройки из конфигурации
        $this->mail->setFromConfig();
        //Указываем от кого письмо
        $this->mail->setFrom($this->mail->Username,$this->config['subject']);
        //Указываем, кому отправить письмо
        $this->mail->addAddress($this->config['email']);
        //Указываем тему письма
        $this->mail->Subject=$this->config['subject'];
        //Указываем кодировку письма
        $this->mail->CharSet="UTF-8";
        //Содержание письма
        $this->mail->text='';
        //Указываем альтернативный текст, если почтовый клиент не поддерживает HTML
        $this->mail->AltBody='';
        return TRUE;
    }
    
    /**
     * Добавляет сообщение лога к содержанию письма.
     * @param string $message - сообщение
     * @return boolean
     */
    private function addToEmail($message)
    {
        $this->mail->text.=$message;
        $this->lastline=trim($message);
        $this->linecount++;
        return TRUE;
    }

    /**
     * Отправляет письмо с логами на почтовый ящик.
     * @return boolean
     */
    private function sendMail()
    {
        //Указываем шаблон письма
        $this->mail->msgTemplate($this->config['template']);
        //Указываем альтернативный текст, если почтовый клиент не поддерживает HTML
        $this->mail->AltBody=$this->mail->text;
        //Отправляем письмо
        return $this->mail->send();
    }

    /**
     * Деструктор.
     * @return boolean
     */
    public function __destruct()
    {
        //Отправляем письмо
        $this->sendMail();
        return TRUE;
    }
}
?>