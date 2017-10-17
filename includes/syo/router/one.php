<?php
/**
 * Класс Роутер – хранит параметры для вызова контролера и шаблон URL запроса. 
 */
class Syo_Router_One 
{
    /**
     * Имя роутера.
     * @var string 
     */
    private $name=null;
    
    /**
     * Установленный шаблон роутера.
     * @var string
     */
    private $pattern    = null;
    
    /**
     * Директорию контролера.
     * @var string 
     */
    private $directory  = null;
    /**
     * Расположение контролера в модуле.
     * @var string 
     */
    private $addons    = null;
    /**
     * Наименование котролера.
     * @var string
     */
    private $controller = null;
    /**
     * Содержит действие.
     * @var string 
     */
    private $action     = null;
    /**
     * Хранит полученные параметры.
     * @var array 
     */
    private $parameters = array();
    
    /**
     * Хранит регулярное выражение URL.
     * @var string 
     */
    private $regexp     = null;
    /**
     * Хранит регулярное выражение для поиска параметров.
     * @var string 
     */
    private $regexpInput= null;
    /**
     * Расширение файла в URLе.
     * @var string 
     */
    private $format     = 'html';
    
    /**
     * Метод конвертирует шаблон в регулярное выражение.
     * @param string $pattern - шаблон
     * @return \Syo_Router_One
     */
    public function setPattern($pattern)
    {
        $this->regexp=str_replace(array('/','.'),array('\/','\.'),$pattern);
        $this->regexp=str_replace(')',')?',$this->regexp);
        $this->regexpInput=preg_replace('/<[\w-]+>/','<([\w-]+)>',$this->regexp);
        $this->regexpInput="/{$this->regexpInput}/i";
        $this->regexp=preg_replace('/<[\w-]+>/','([\w-]+)',$this->regexp);
        $this->regexp="/^{$this->regexp}$/i";       
        $this->pattern=$pattern;
        return $this;
    }
    
    /**
     * Указываем название роутера.
     * @param string $name - имя роутера.
     * @return \Syo_Router_One
     */
    public function setName($name)
    {
        if (is_null($this->getName())) $this->name=$name;
        return $this;
    }
    
    /**
     * Возвращает название роутера.
     * @return string - имя роутера. 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Возвращает шаблон роутера.
     * @return string - шаблон роутера
     */
    public function getPattern()
    {
        return $this->pattern;
    }

    /**
     * Устанавливает директорию контролера.
     * @param string $directory - директория контролера.
     * @return \Syo_Router_One
     */
    public function setDirectory($directory)
    {
        if (is_null($this->getDirectory())) $this->directory=$directory;
        return $this;
    }
    
    /**
     * Возвращает директорию контролера.
     * @return string - директория контролера.
     */
    public function getDirectory()
    {
        return $this->directory;
    }

    /**
     * Устанавливает имя модуля.
     * @param string $addons - имя модуля
     * @return \Syo_Router_One
     */
    public function setAddons($addons)
    {
        if (is_null($this->getAddons())) $this->addons=$addons;
        return $this;
    }
    
    /**
     * Возвращает имя модуля.
     * @return string - имя модуля
     */
    public function getAddons()
    {
        return $this->addons;
    }

    /**
     * Устанавливает имя контролера.
     * @param string $controller - имя контролера
     * @return \Syo_Router_One
     */
    public function setController($controller)
    {
        if (is_null($this->getController())) $this->controller=$controller;
        return $this;
    }
    
    /**
     * Возвращает имя контролера.
     * @return string - имя контролера
     */
    public function getController()
    {
        return $this->controller;
    }
    
    /**
     * Указывает действие в контролере.
     * @param string $action - действие
     * @return \Syo_Router_One
     */
    public function setAction($action)
    {
        if (is_null($this->getAction())) $this->action = $action;
        return $this;
    }
    
    /**
     * Возвращает действие в контролере.
     * @return string - действие 
     */
    public function getAction() 
    {
        return $this->action;
    }
    
    /**
     * Добавляет параметр в список.
     * @param string $name - имя переменной
     * @param variant $value - значение переменной
     * @return \Syo_Router_One
     */
    public function addParameter($name,$value)
    {
        $this->parameters[$name]=$value;
        return $this;
    }
    
    /**
     * Устанавливает параметры.
     * @param array $param
     * @return \Syo_Router_One
     */
    public function setParameter($param)
    {
        $this->parameters=$param;
        return $this;        
    }
    
    /**
     * Возвращает полученный список переменных  виде массива.
     * @return array - список переменных
     */
    public function getParameters()
    {
        return $this->parameters;
    }
    
    /**
     * Возвращает регулярное выражение данного роутера.
     * @return string - регулярное выражение
     */
    public function getRegexp()
    {
        return $this->regexp;
    }
    
    /**
     * Возвращает регулярное выражение для изъятия переменных из URL.
     * @return string - регулярное выражение
     */
    public function getRegexpInput()
    {
        return $this->regexpInput;
    }

    /**
     * Устанавливает формат файла в URL.
     * @param string $format - формат
     * @return \Syo_Router_One
     */
    public function setFormat($format)
    {
        $this->format = $format;
        return $this;
    }
    
    /**
     * Возвращает формат файла в URL.
     * @return string - формат
     */
    public function getFormat()
    {
        return $this->format;
    }
    
    /**
     * Выполняет проверку присутствия шаблона в роутере.
     * @return boolean
     */
    public function isValid()
    {
        if (is_null($this->getPattern()) || is_null($this->getName())) return false;
        return true;
    }
    
    /**
     * Устанавливает параметры для директории, модуля, контролера, действия, формата.
     * Kонстанты: directory, addons, controller, action, format.
     * @param array $param
     * @return \Syo_Router_One
     */
    public function setRoute($param=array())
    {
        if (is_array($param))
        {
            if (isset($param['directory'])) 
            {
                $this->setDirectory($param['directory']);
                unset($param['directory']);
            }
            if (isset($param['addons'])) 
            {
                $this->setAddons($param['addons']);
                unset($param['addons']);
            }
            if (isset($param['controller'])) 
            {
                $this->setController($param['controller']);
                unset($param['controller']);
            }
            if (isset($param['action'])) 
            {
                $this->setAction($param['action']);
                unset($param['action']);
            }
            if (isset($param['format'])) 
            {
                $this->setFormat($param['format']);
                unset($param['format']);
            }
            $this->setParameter($param);
        }
        return $this;
    }
}
?>