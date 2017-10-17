<?php
/**
 * Класс для определения пути к контролеру.
 */
class Syo_Router
{
    /**
     * Список роутеров.
     * @var array Syo_Router_One
     */
    protected $routes=array();
    /**
     * Адрес  $_GET['route'].
     * @var string 
     */
    protected $address=null;

    /**
     * Конструктор Router.
     * @param string $str - адрес
     */
    public function __construct($str=NULL)
    {
        if (is_null($str))
        {
            //Устанавливаем адрес  из $_GET['route'].
            $this->setAddress($this->getURL());
        }
        else
        {
            $this->setAddress($str);
        }
    }
    
    /**
     * Находит соответствующий роутер по адресу и разбирает его.
     * @return Route (or NULL) - роутер соответствующими параметрами.
     */
    public function parsing()
    {   
        $resRoute=null;
        //Перебираем роутеры.
        foreach($this->routes as $route)
        {               
            $params=array();
            //Есть совпадение.
            if (preg_match($route->getRegexp(),$this->getAddress(),$params)) 
            {
                if (count($params)==0)
                {
                    $resRoute=$route;
                    break;
                }
                //Ищем параметры.
                $input = array();
                $pattern=str_replace(array('(',')'),array('',''),$route->getPattern());
                preg_match($route->getRegexpInput(),$pattern, $input);
                for($i=1;$i<count($input);$i++)
                {
                    if (isset($params[$i]) && ($params[$i]) && (strpos($input[$i],'<')===FALSE))
                    switch ($input[$i])
                    {
                        case 'directory':
                            $route->setDirectory($params[$i]);
                        break;
                        case 'addons':
                            $route->setAddons($params[$i]);
                        break;
                        case 'controller':
                            $route->setController($params[$i]);
                        break;
                        case 'action':
                            $route->setAction($params[$i]);
                        break;
                        default:
                            $route->addParameter($input[$i], $params[$i]);
                    }
                }
                //Возвращаем результат.
                $resRoute=$route;
                break;
            } 
        }
        if(is_null($resRoute))
        {
            return NULL;
        }
        return $resRoute;
    }
    
    /**
     * Добавляем в список новый роутер.
     * @param string $name (or Route) - имя роутера или сам объект роутер.
     * @param string $pattern - шаблон роутера.
     * @param array $param - параметры роутера для контролера. Kонстанты: directory, addons, controller, action, format.
     * @return boolean
     */
    public function addRoute($name,$pattern=NULL,$param=array())
    {
        if (is_object($name))
        {
            //Получили объект.
            if ($name->isValid())
            {
                $this->routes[]=$name;
                return TRUE;
            }
            return FALSE;                        
        }
        else 
        {
            //Получили параметры $name,$pattern,$param.
            $route=new Syo_Router_One();
            $route->setName($name)
                  ->setPattern($pattern)
                  ->setRoute($param);
            if ($route->isValid())
            {
                $this->routes[]=$route;
                return TRUE;
            }
            return FALSE;            
        }
        return FALSE;
    }

    /**
     * Находит контроллер, выполняем действие, результат отправляет на просмотр.
     * @return boolean
     * @throws Syo_Exception
     */
    public function delegate()
    {
        try
        {  
            //Находи роутер
            $resRoute=$this->parsing();
            if (!is_null($resRoute))
            {
                //С роутера берём имя контролера.
                $tmpcontroller=$resRoute->getController();
                if (is_null($tmpcontroller))
                {
                    $class='IndexController';
                    $tmpcontroller='index';
                }
                elseif (stripos($tmpcontroller,'widget'))
                {
                    throw new Syo_Exception('Access to this controller is closed!');
                }
                else
                {    
                    $class=ucfirst($tmpcontroller).'Controller';
                }
                //Берём название действия.
                $tmpaction=$resRoute->getAction();
                if (is_null($tmpaction))
                {
                    $action='indexAction';
                    $tmpaction='index';
                }
                else
                {    
                    $action=$tmpaction.'Action';
                }
                //Сохраняем полученные параметры.
                Syo_Registry::getInstance()->set('args',$resRoute->getParameters());
                //Генерируем путь к файлу контролера, проверяем на наличие.
                $tmpAddons=APPPATH.DIRSEP.$resRoute->getAddons().DIRSEP;
                $tmpAddonsC=$tmpAddons.'controllers'.DIRSEP;
                $tmpDirectory=(is_null($resRoute->getDirectory()))?'':$resRoute->getDirectory().DIRSEP;
                $file=SITEPATH.$tmpAddonsC.$tmpDirectory.$tmpcontroller.'.php';
                Syo_Application::addPathLib($tmpAddons.'models');
                if (is_readable($file)==false)
                {
                    throw new Syo_Exception('Not Found file Controller');
                }
                include($file);
                //Создаём класс контролера.
                $controller=new $class();
                $controller->setAddon($resRoute->getAddons());
                $controller->setName($tmpcontroller);
                $controller->setActionEvent($tmpaction);
                if (is_callable(array($controller,$action))==false) 
                {
                    throw new Syo_Exception('Not Found Action!');
                }
                //Создаём класс просмотра.
                $controller->view=new Syo_Template($resRoute->getAddons());
                //Указываем где находиться файл просмотра.
                $controller->view->setTemplate(strtolower($tmpaction));
                $tmpAddons=(is_null($resRoute->getAddons()))?'':$resRoute->getAddons().DIRSEP;
                if (!empty($tmpDirectory)) $controller->view->addPath(strtolower($tmpDirectory));
                $controller->view->addPath(strtolower($tmpcontroller));
                //Инициализируем контроллер.
                $controller->init();
                //Выполняем действие
                $controller->$action();
                //Выполняем метод перед выводом данных
                if (method_exists($controller,'beforeTemplate'))
                {
                    $controller->beforeTemplate();
                }
                //Подаём данные на вывод.
                $controller->view->render();
                //Выполняем метод после вывода данных
                if (method_exists($controller,'afterTemplate'))
                {
                    $controller->afterTemplate();
                }
            }
            else
            {
                throw new Syo_Exception('Not Found Router!');
            }
        }
        catch (Syo_Exception $e)
        {
            echo $e;
            exit();
        }
        return TRUE;
    }

    /**
     * Возвращает адрес из переменной $_GET['route'].
     * @return string - адрес роутера.
     */
    protected function getURL()
    {
        return (empty($_GET['route']))?'':$_GET['route'];
    }
    
    /**
     * Устанавливаем адрес.
     * @param string $address
     */
    public function setAddress($address)
    {
        $this->address=$address;
    }
    
    /**
     * Получаем адрес.
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }
}

//
//class Syo_Router
//{
//    private $_path;
//    private $_args=array();
//    private $_redirect=array();
//
//    public function __construct()
//    {
//    }
//
//    public function setPath($path)
//    {
//        try
//        {
//            $path=trim($path, '/\\');
//            $path.=DIRSEP;
//            if (is_dir($path)==false)
//            {
//                throw new Syo_Exception('Invalid controller path: `'.$path.'`');
//            }
//            $this->_path=$path;
//        }
//        catch (Syo_Exception $e)
//        {
//            echo $e;
//            exit();
//        }
//    }
//
//    private function getController()
//    {
//        $arrayParam=array();
//        $route=$this->getURL();
//        if (empty($route))
//        {
//            $route = 'index';
//        }
//        // Перенаправляем роутер
//        $route=$this->redirectRouter($route);
//        // Получаем раздельные части
//        $route = trim($route, '/\\');
//        $parts = explode('/', $route);
//        // Находим правильный контроллер
//        $cmd_path=$this->_path;
//        $cmd_module='';
//        foreach ($parts as $part)
//        {
//            $fullpath=$cmd_path.$part;
//            //Есть ли папка с таким путём?
//            if (is_dir($fullpath))
//            {
//                $cmd_path.=$part.DIRSEP;
//                $cmd_module.=$part.DIRSEP;
//                array_shift($parts);
//                continue;
//            }
//            // Находим файл
//            if (is_file($fullpath.'.php'))
//            {
//                $arrayParam['controller']=$part;
//                array_shift($parts);
//                break;
//            }
//        }
//        if (empty($arrayParam['controller']))
//        { 
//            $arrayParam['controller']='index';
//        };
//        // Получаем действие
//        $arrayParam['action']=array_shift($parts);
//        if (empty($arrayParam['action']))
//        { 
//            $arrayParam['action']='index';
//        }
//        $arrayParam['module']=trim($cmd_module,DIRSEP);
//        $arrayParam['file']=$cmd_path.$arrayParam['controller'].'.php';
//        $arrayParam['args']=$parts;
//        return $arrayParam;
//    }
//    
//    public function delegate()
//    {
//        try
//        {
//            // Анализируем путь
//            $param=$this->getController();
//            Syo_Registry::getInstance()->set('route',$param);
//            // Файл доступен?
//            if (is_readable($param['file'])==false)
//            {
//                throw new Syo_Exception('404 Not Found');
//            }
//            // Подключаем файл
//            include($param['file']);
//            // Создаём экземпляр контроллера
//            $class=$param['controller'].'Controller';
//            $args=$this->isVar($param['args']);
//            $merge_args[0]=$param['args'];
//            $merge_args[1]=$args;
//            Syo_Registry::getInstance()->set('args',$merge_args);
//            $controller=new $class();//$this->_registry
//            // Действие доступно?
//            $action=$param['action'].'Action';
//            if (is_callable(array($controller,$action))==false) 
//            {
//                throw new Syo_Exception('Not Found Action!');
//            }
//            // Выполняем действие
//            $controller->init();
//            $controller->$action();
//            $controller->view=new Syo_Template();
//            $controller->view->setLayout(strtolower($param['action']));
//            if (!empty($param['module'])) $controller->view->addPath(strtolower($param['module']));
//            $controller->view->addPath(strtolower($param['controller']));
//            $controller->view->render();
//        }
//        catch (Syo_Exception $e)
//        {
//            echo $e;
//            exit();
//        }
//    }
//    
//    private function isVar($param)
//    {
//        $tmpparam=array_chunk($param,2);
//        $args=array();
//        foreach ($tmpparam as $avalue)
//        {
//            $args[current($avalue)]=next($avalue);
//        }
//        return $args;
//    }
//    
//    public function addRoute($route,$paramcontroller)
//    {
//        $result=$this->StrToRoutestr($route);
//        $action=$this->ArrayControllerToRoutestr($paramcontroller).(($result[1]!='')?'/'.$result[1]:'');
//        $this->_redirect[$result[0]]=$action;
//    }
//
//    public function addRouteStr($routestr,$controllerstr)
//    {   
//        $this->_redirect[$routestr]=$controllerstr;
//    }
//    
//    protected function StrToRoutestr($route)
//    {
//        $route=trim($route,'/\\');
//        $parts=explode('/',$route);
//        $url=array();
//        $param=array();
//        $i=0;
//        foreach ($parts as $part)
//        {
//            if ($part[0]==':')
//            {
//                $url[]='([-_a-z0-9]+)';
//                $i++;
//                $param[]=substr($part,1);
//                $param[]='$'.$i;
//            }
//            else
//            {
//                $url[]=$part;
//            }
//        }
//        $result[]=implode('/',$url);
//        $result[]=implode('/',$param);
//        return $result;
//    }
//    
//    protected function ArrayControllerToRoutestr($paramcontroller)
//    {
//        $action['module']=$paramcontroller['module'];
//        unset($paramcontroller['module']);
//        $action['controller']=$paramcontroller['controller'];
//        unset($paramcontroller['controller']);
//        $action['action']=$paramcontroller['action'];
//        unset($paramcontroller['action']);
//        $action=array_merge($action,$paramcontroller);
//        return implode('/',$action);
//    }
//    
//    protected function redirectRouter($route)
//    {
//        foreach ($this->_redirect as $key=>$value)
//        {
//            if (preg_match("~$key~",$route))
//            {
//                $route=preg_replace("~$key~",$value,$route);
//            }
//        }
//        return $route;
//    }
//    
//    protected function getURL()
//    {
//        return (empty($_GET['route']))?'':$_GET['route'];
//    }
//
//}

?>
