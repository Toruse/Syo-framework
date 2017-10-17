<?php
/**
 * Класс-шаблон, который добавляет результат в шаблонный HTML-файл, и передаёт его браузеру.
 */
class Syo_Template
{
    /**
     * Имя файла View.
     * @var string 
     */
    private $template;
    /**
     * Относительный путь к View.
     * @var string 
     */
    private $_path;
    /**
     * Список переменных передаваемый в View.
     * @var array - variant 
     */
    private $_vars=array();
    /**
     * Переменная указывает выполнять или не выполнять вывод View.
     * @var boolean 
     */
    private $_render=true;
    /**
     * Имя каталога, где находятся файлы View.
     * @var string 
     */
    private $viewFolder='views';
    
    /**
     * Конструктор. Генерируем начальный путь к файлам View.
     */
    function __construct($modules=NULL)
    {
        if (is_null($modules))
        {
            $this->_path=APPPATH.DIRSEP.$this->viewFolder.DIRSEP;
        }
        else
        {
            $this->_path=APPPATH.DIRSEP.$modules.DIRSEP.$this->viewFolder.DIRSEP;            
        }
    }

    /**
     * Метод для добавления переменной к View.
     * @param string $varname - имя переменной.
     * @param variant $value - значение переменной.
     * @return boolean
     */
    function set($varname,$value)
    {
        $this->_vars[$varname]=$value;
        return true;
    }
    
    /**
     * Метод для добавления переменной к View.
     * @param string $varname - имя переменной.
     * @param variant $value - значение переменной.
     * @return boolean
     */
    function __set($varname,$value)
    {
        $this->_vars[$varname]=$value;
        return true;
    }

    /**
     * Метод для получения значения переменной.
     * @param string $varname - имя переменной.
     * @return variant - значение переменной.
     */
    function __get($varname)
    {
        if (isset($this->_vars[$varname])==false)
        {
                return null;
        }
        return $this->_vars[$varname];
    }

    /**
     * Удаляет по указанному ключу переменную из списка View.
     * @param string $varname - имя переменной.
     */
    function __unset($varname) 
    {
        unset($this->_vars[$varname]);
    }

    /**
     * Проверяет переменную на существование в списке View.
     * @param string $varname - имя переменной.
     * @return boolean
     */
    function __isset($varname) 
    {
        return isset($this->_vars[$varname]);
    }
    
    /**
     * Удаляет по указанному ключу переменную из списка View.
     * @param string $varname - имя переменной.
     * @return boolean
     */
    function remove($varname)
    {
        unset($this->vars[$varname]);
        return true;
    }

    /**
     * Возвращает глобальный путь к файлу View.
     * @return string
     */
    private function getFileName()
    {
        return SITEPATH.$this->_path.$this->template.'.php';
    }

    /**
     * Выполняет вывод шаблонного HTML-файла пользователю.
     * @return boolean
     * @throws Syo_Exception
     */
    public function render()
    {
        // Если истина, то выводим данные.
        if ($this->_render)
        {
            try
            {
                // Получаем глобальный путь к View.
                $path=$this->getFileName();
                // Проверяем наличие файла.
                if (file_exists($path)==false)
                {    
                    throw new Syo_Exception('Template `'.$path.'` does not exist.');
                }
                // Выводим данные.
                include $path;
            }
            //Выводим сообщение об ошибке.
            catch (Syo_Exception $e)
            {
                echo $e;
            }
        }
        return TRUE;
    }
    
    /**
     * Перегружается метод для вывода объекта в строку.
     * @return boolean
     */
    public function __toString() 
    {
        // Если истина, то выводим данные.
        if ($this->_render)
        {
            // Включаем буфер вывода.
            ob_start();
            try
            {
                // Получаем глобальный путь к View.
                $path=$this->getFileName();
                // Проверяем наличие файла.
                if (file_exists($path)==false)
                {    
                    throw new Syo_Exception('Template `'.$this->template.'` does not exist.');
                }
                // Выводим данные.
                include $path;
            }
            //Выводим сообщение об ошибке.
            catch (Syo_Exception $e)
            {
                ob_end_clean();
                echo $e;
            }
        }
        // Копируем содержимое буфера в строку и очищаем его.
        return ob_get_clean();
    }

    /**
     * Устанавливает имя View.
     * @param string $templateName - имя View
     */
    public function setTemplate($templateName)
    {
        $this->template=$templateName;
    }

    /**
     * Метод устанавливает путь к View.
     * @param string $Path
     */
    public function setPath($Path)
    {
        $this->_path=$Path;
    }

    /**
     * Добавляет путь к существующему адресу.
     * @param string $Path
     */
    public function addPath($Path)
    {
        $this->_path.=$Path.DIRSEP;  
    }
    
    /**
     * Устанавливает - выводить View или нет.
     * @param boolean $Bool
     */
    public function setRender($Bool)
    {
        $this->_render=$Bool;
    }

    /**
     * Проверят отлючен View или нет.
     * @return boolean
     */
    public function isRender()
    {
        return $this->_render;
    }
    
    /**
     * Переключает режим выводить View или нет.
     */
    public function switchRender()
    { 
        $this->_render=!($this->_render);
    }
}
?>
