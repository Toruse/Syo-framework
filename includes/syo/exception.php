<?php
/**
 * Класс для обработки ошибок.
 */
class Syo_Exception extends Exception
{
    /**
     * Хранит количество произошедших ошибок.
     * @var integer 
     */
    private static $countError=0;

    /**
     * Конструктор.
     * 5.3.0
     * @param type $message
     * @param type $code
     */
    public function __construct($message,$code=0)
    {
        parent::__construct($message,$code);
    }

    /**
     * 
    public function __construct($message,$code=0,Exception $previous=null)
    {
        parent::__construct($message,$code,$previous);
    }
     */
    
    /**
     * Переопределённый метод, вывода ошибки в строку.
     * @return string
     */
    public function __toString()
    {
        //Нужно ли выводить сообщения?
        $tmp_config=Syo_Registry::getInstance()->get('config');
        if ($tmp_config['application']['display_errors'])
        {
            //Определяем, в каком виде выводить ошибки.
            $viewErr=$tmp_config['application']['view_errors'];
            switch ($viewErr)
            {
                case 'HTML':
                    //Загружаем шаблон оформления (выполняется только раз).
                    $tpl='';
                    if (self::$countError==0)
                    {
                        $tpl=file_get_contents(SITEPATH.LIBPATH.DIRSEP.COREPATH.DIRSEP.'templates'.DIRSEP.'exception'.DIRSEP.'errorCodeJS.tpl');
                    }
                    //Считаем ошибку.
                    self::$countError++;
                    //Загружаем шаблон и формируем строку с ошибкой.
                    $tplline=file_get_contents(SITEPATH.LIBPATH.DIRSEP.COREPATH.DIRSEP.'templates'.DIRSEP.'exception'.DIRSEP.'errorCodelineJS.tpl');
                    $tplline=sprintf($tplline,self::$countError,$this->getCode(),$this->getMessage(),str_replace(DIRSEP,DIRSEP.DIRSEP,$this->getFile()),$this->getLine());
                    return $tpl.$tplline;
                break; 
                case 'TXT':
                    $tplline=self::$countError.' Error:'.$this->getCode().' '.$this->getMessage().' '.$this->getFile().' '.$this->getLine()."<br>\n";
                    return $tplline;                    
                break;
                default:
                    $tplline=self::$countError.' Error:'.$this->getCode().' '.$this->getMessage().' '.$this->getFile().' '.$this->getLine()."<br>\n";
                    return $tplline;                    
            }
        }
        else
        {
            return '';
        }
    }
    
    /**
     * Пользовательский обработчик ошибок.
     * @param type $errno
     * @param type $errstr
     * @param type $errfile
     * @param type $errline
     */
    public static function errorHandler($errno,$errstr,$errfile,$errline)
    {
        //Определяем, в каком виде выводить ошибки.
        $tmp_config=Syo_Registry::getInstance()->get('config');
        $viewErr=$tmp_config['application']['view_errors'];
        switch ($viewErr)
        {
            case 'HTML':
                //Загружаем шаблон оформления (выполняется только раз).
                if (self::$countError==0)
                {
                    $tpl=file_get_contents(SITEPATH.LIBPATH.DIRSEP.COREPATH.DIRSEP.'templates'.DIRSEP.'exception'.DIRSEP.'errorCodeJS.tpl');
                    echo $tpl;
                }
                //Считаем ошибку.
                self::$countError++;
                //Загружаем шаблон и формируем строку с ошибкой.
                $tplline=file_get_contents(SITEPATH.LIBPATH.DIRSEP.COREPATH.DIRSEP.'templates'.DIRSEP.'exception'.DIRSEP.'errorCodelineJS.tpl');
                $tplline=sprintf($tplline,self::$countError,$errno,$errstr,str_replace(DIRSEP,DIRSEP.DIRSEP,$errfile),$errline);
                echo $tplline;
                return;
            case 'TXT':
                $tplline=self::$countError.' Error:'.$errno.' '.$errstr.' '.$errfile.' '.$errline."<br>\n";
                echo $tplline;
                return;                    
            break;
            default:
                $tplline=self::$countError.' Error:'.$errno.' '.$errstr.' '.$errfile.' '.$errline."<br>\n";
                echo $tplline;
                return;                    
        }
    }
}
?>