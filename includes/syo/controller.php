<?php
/**
 * Абстрактный класс Контролера.
 */
abstract class Syo_Controller
{
    /**
     * Переменная хранить имя аддона.
     * @var string 
     */
    private $addon=null;
    
    /**
     * Переменная хранит имя контроллера.
     * @var string 
     */
    private $name=null;
    
    /**
     * Переменная хранит имя выполняемого действия.
     * @var string 
     */
    private $action=null;
    
    /**
     * Переменная для работы с видом.
     * @var Syo_Template 
     */
    public $view=null;
    /**
     * Переменная конфигурации.
     * @var array 
     */
    public $config=null;
    
    /**
     * Обработка запроса.
     * @var Syo_Controller_Request_Http 
     */
    public $request=null;
    
    /**
     * Работаем с header страницы. 
     * @var Syo_Controller_Header 
     */
    public $header=null;

    /**
     * Конструктор.
     */
    function __construct()
    {
        $this->config=Syo_Registry::getInstance()->get('config');
        $this->request=new Syo_Controller_Request_Http();
        $this->header=new Syo_Controller_Header();
    }

    /**
     * Метод выполняется при создании класса.
     */
    abstract function init();
    
    /**
     * Метод действия index.
     */
    abstract function indexAction();
    
    /**
     * Устанавливает новый View.
     * @param Syo_Template $view
     * @return boolean
     */
    public function setView($view)
    {
        $this->view=$view;
        return TRUE;
    }
    
    /**
     * Добавляет View к существующему View.
     * @param string $name - имя переменной (заранее определённое) в которую помешается View.
     * @param string $view (or Syo_Template) - имя View или объект View.
     * @param string $path - путь к View.
     */
    public function addView($name,$view,$path=NULL)
    {
        if (is_object($view))
        {
            $this->view->set($name,$view);    
        }
        else 
        {
            $template=new Syo_Template();
            $template->setTemplate($view);
            if (!is_null($path)) $template->addPath($path);
            $this->view->set($name,$template);
        }
    }
    
    /**
     * Возвращает объект View контроллера.
     * @return Syo_Template
     */
    public function getView()
    {
        return $this->view;
    }

    /**
     * Вставляет View-контроллера в макет.
     * @param string $name - имя переменной (заранее определённое) в которую помешается View.
     * @param string $file - имя файла View.
     * @param string $path - путь к View. 
     */
    public function setLayout($name,$file,$path=NULL)
    {
        $template=new Syo_Template(HOMEPATH);
        $template->setTemplate($file);
        $template->setRender($this->view->isRender());
        if (!is_null($path)) $template->addPath($path);
        $template->set($name,$this->view);
        $this->setView($template);
    }

    /**
     * Назначает имя аддона.
     * @param string $name - имя контролера
     */
    public function setAddon($name)
    {
        if (is_null($this->name)) $this->name=$name;
    }

    /**
     * Возвращает имя аддона.
     * @return string
     */
    public function getAddon()
    {
        return $this->name;
    }
    
    /**
     * Назначает имя контролера.
     * @param string $name - имя контролера
     */
    public function setName($name)
    {
        if (is_null($this->name)) $this->name=$name;
    }

    /**
     * Возвращает имя контроллера.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }
    
    /**
     * Назначает имя действия.
     * @param string $action - имя действия
     */
    public function setActionEvent($action)
    {
        if (is_null($this->action)) $this->action=$action;
    }

    /**
     * Возвращает имя действия.
     * @return string - имя действия
     */
    public function getActionEvent()
    {
        return $this->action;
    }

    /**
     * Деструктор.
     */
    public function  __destruct() 
    {

    }    
}
?>
