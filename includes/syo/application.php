<?php
/**
 * Класс для инициализации и запуска приложения.
 */
class Syo_Application
{
    /**
     * Имя файла конфигурации.
     * @var string 
     */
    protected $nameConfig;
    /**
     * Список директорий для поиска классов.
     * @var array
     */
    protected static $libPath=array();

    /**
     * Конструктор.
     * @param string $fileCofig - имя файла конфигурации
     */
    public function __construct($fileCofig='application')
    {
        //Регистрируем функцию автозагрузки классов.
        spl_autoload_register(array($this,'LoadClass'));
        //Добавляем для поиска директорию с библиотекой фреймворка.
        self::addPathLib(LIBPATH);
        //Указываем имя конфигурационного файла.
        $this->setNameConfig($fileCofig);
        //Создаем роутер и помешаем в регист.
        $router=new Syo_Router();
        Syo_Registry::getInstance()->set('router',$router);
    }
    
    /**
     * Запускает приложение на выполнение.
     */
    public function Run()
    {
        //Загружаем конфигурационный файл и помешаем его регист.
        $config=Syo_Config::Load($this->getNameConfig());
        Syo_Registry::getInstance()->set('config',$config);
        //Проверяем версию PHP.
        $this->VersionPHP();
        //Указываем, какие сообщения выводить.
        $this->setErrors();        
        //Изымаем роутер из регистра
        //Выполняем анализ, подготовку контроллера, и запускаем его на выполнение.
        Syo_Registry::getInstance()->get('router')->delegate();
    }
    
    /**
     * Метод для автоматической загрузки классов.
     * @param string $className - имя класса.
     * @return boolean
     */
    private function LoadClass($className)
    {
        //Проходим список директорий.
        foreach (self::$libPath as $path)
        {
            //Генерируем путь, для подключения класса.
            $file=SITEPATH.$path.DIRSEP.strtolower(str_replace(array('_','\\'),DIRSEP,$className)).'.php';
            //Подключаем соответствующий класс.
            if (file_exists($file))
            {
               require_once $file;
               return true;
            }
        }
        return false;        
    }
    
    /**
     * Проверяет версию PHP.
     * @throws Syo_Exception
     */
    public function VersionPHP()
    {
        try
        {
            //Берём из конфигурации минимально требуемую версию PHP.
            $config=Syo_Registry::getInstance()->get('config');
            $version=$config['application']['phpversion'];
            //Проверяем.
            if (version_compare(phpversion(),$version,'<')==true)
            {
                throw new Syo_Exception('PHP '.$version.' Only');
            }
        }
        catch (Syo_Exception $e)
        {
            echo $e;
            exit();
        }
    }
    
    /**
     * Устанавливает имя конфигурационного файла.
     * @param string $str - - имя файла
     */
    protected function setNameConfig($str)
    {
        $this->nameConfig=$str;
    }
    
    /**
     * Возвращает имя конфигурационного файла.
     * @return string
     */
    protected function getNameConfig()
    {
        return $this->nameConfig;
    }
    
    /**
     * Устанавливает, какие сообщения об ошибках нужно подавать на вывод.
     */
    public function setErrors()
    {
        //Параметр берётся из файла конфигурации.
        $tmp_config=Syo_Registry::getInstance()->get('config');
        error_reporting($tmp_config['application']['display_errors']);
        //Указываем функцию генерирующую вывод ошибки.
        set_error_handler(array('Syo_Exception','errorHandler'));
    }
    
    /**
     * Добавляет в список имя директории, где нужно выполнять поиск классов.
     * @param string $path
     */
    public static function addPathLib($path)
    {
        self::$libPath[]=$path;
    }

    /**
     * Извлекает имя директории, из списка поиска классов, по указанному ключу.
     * @param number $index - ключ
     * @return string - имя директории
     */
    public static function getPathLib($index)
    {
        return self::$libPath[$index];
    }

    /**
     * Изменяет имя директории по заданному ключу в списке «поиска классов».
     * @param number $index - ключ
     * @param string $path - имя директории
     */
    public static function setPathLib($index,$path)
    {
        self::$libPath[$index]=$path;
    }

    /**
     * Удаляет указанное имя директории.
     * @param number $index - ключ
     */
    public static function delPathLib($index)
    {
        unset(self::$libPath[$index]);
    }
    
    /**
     * Возвращает из регистра роутер.
     * @return Syo_Router
     */
    public function getRouter()
    {
        return Syo_Registry::getInstance()->get('router');
    }
}
?>
