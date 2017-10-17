<?php
/**
 * Класс для работы с пришедшими запросами от клиента.
 */
class Syo_Controller_Request_Http
{    
    /**
     * Перегружаем метод get. Выполняет поиск переменной по всем массивам (args,GET,POST).
     * @param string $key - имя переменной
     * @return variant - значение переменной
     */
    public function __get($key)
    {
        $args=Syo_Registry::getInstance()->get('args');
        switch (true) {
            case isset($args[$key]):
                return $args[$key];
            case isset($_GET[$key]):
                return $_GET[$key];
            case isset($_POST[$key]):
                return $_POST[$key];
            default:
                return null;
        }
    }

    /**
     * Перегружаем метод set. Блокируем возможность изменять значение переменной.
     * @param string $key
     * @param variant $value
     * @throws Syo_Exception
     */
    public function __set($key, $value)
    {
        throw new Syo_Exception('Error Syo_Controller_Request_Http: Not set param.');
    }
    
    /**
     * Выполняет поиск переменной по всем массивам (args,GET,POST).
     * @param string $key - имя переменной
     * @return variant - значение переменной
     */
    public function getParam($key)
    {
        $args=Syo_Registry::getInstance()->get('args');
        if (($args!=null) && isset($args[$key]))
        {
            return $args[$key];
        }
        elseif (isset($_GET[$key]))
        {
            return $_GET[$key];
        }
        elseif (isset($_POST[$key]))
        {
            return $_POST[$key];
        }
        return null;
    }
    
    /**
     * Устанавливает значение для переменной из массива $args.
     * @param string $key - имя переменной
     * @param variant $value - значение переменной
     */
    public function setParam($key,$value)
    {
        $args=Syo_Registry::getInstance()->get('args');
        if (($value===null) && isset($args[1][$key])) 
        {
            unset($args[1][$key]);
        }
        elseif ($value!==null)
        {
            $args[1][$key]=$value;
        }
        Syo_Registry::getInstance()->set('args',$args);
    }

    /**
     * Выполняет поиск переменной по массиву $_POST.
     * @param string $key - имя переменной. Если указан NULL, возвращает весь массив POST. 
     * @return variant - значение переменной
     */
    public function getPost($key=null)
    {
        if ($key===null)
        {
            return $_POST;
        }
        return (isset($_POST[$key]))?$_POST[$key]:null;
    }

    /**
     * Устанавливает значение для переменной из массива POST.
     * @param string $key - имя переменной
     * @param variant $value - значение переменной
     */
    public function setPost($key,$value)
    {
        if (($value===null) && isset($_POST[$key])) 
        {
            unset($_POST[$key]);
        }
        elseif ($value!==null)
        {
            $_POST[$key]=$value;
        }
    }
    
    /**
     * Выполняет поиск переменной по массиву $_GET.
     * @param string $key - имя переменной. Если указан NULL, возвращает весь массив GET. 
     * @return variant - значение переменной
     */    
    public function getGet($key=null)
    {
        if ($key===null)
        {
            return $_GET;
        }
        return (isset($_GET[$key]))?$_GET[$key]:null;
    }

    /**
     * Устанавливает значение для переменной из массива GET.
     * @param string $key - имя переменной
     * @param variant $value - значение переменной
     */
    public function setGet($key,$value)
    {
        if (($value===null) && isset($_GET[$key])) 
        {
            unset($_GET[$key]);
        }
        elseif ($value!==null)
        {
            $_GET[$key]=$value;
        }
    }
    
    /**
     * Определяем, является ли полученный запрос GET.
     * @return boolean
     */
    public function isGet()
    {
        if ($this->getMethod()=='GET') 
        {
            return true;
        }
        return false;
    }

    /**
     * Определяем, является ли полученный запрос POST.
     * @return boolean
     */
    public function isPost()
    {
        if ($this->getMethod()=='POST') 
        {
            return true;
        }
        return false;        
    }
    
    /**
     * Определяем, является ли полученный запрос AJAX.
     * @return boolean
     */
    public function isAJAX()
    {
        if ($this->getServer('HTTP_X_REQUESTED_WITH')=='XMLHttpRequest') 
        {
            return true;
        }
        return false;                
    }
    /**
     * Определяем, является ли полученный запрос PUT.
     * @return boolean
     */
    public function isPut()
    {
        if ($this->getMethod()=='PUT') 
        {
            return true;
        }
        return false;        
    }    

    /**
     * Определяем, является ли полученный запрос OPTIONS.
     * @return boolean
     */
    public function isOptions()
    {
        if ($this->getMethod()=='OPTIONS') 
        {
            return true;
        }
        return false;        
    }    
    
    /**
     * Определяем, является ли полученный запрос DELETE.
     * @return boolean
     */
    public function isDelete()
    {
        if ($this->getMethod()=='DELETE') 
        {
            return true;
        }
        return false;        
    }    

    /**
     * Определяем, является ли полученный запрос TRACE.
     * @return boolean
     */
    public function isTrace()
    {
        if ($this->getMethod()=='TRACE') 
        {
            return true;
        }
        return false;        
    }

    /**
     * Определяем, является ли полученный запрос CONNECT.
     * @return boolean
     */
    public function isConnect()
    {
        if ($this->getMethod()=='CONNECT') 
        {
            return true;
        }
        return false;        
    }    


    /**
     * Определяем, является ли полученный запрос HEAD.
     * @return boolean
     */
    public function isHead()
    {
        if ($this->getMethod()=='HEAD') 
        {
            return true;
        }
        return false;        
    } 
    
    /**
     * Получаем метод запроса.
     * @return type
     */    
    public function getMethod()
    {
        return $this->getServer('REQUEST_METHOD');
    }

    /**
     * Получаем значения з глобальной переменной $_SERVER или получаем глобальный массив $_SERVER.
     * @param variant $key - имя переменной 
     * @param variant $default - указываем значение по умолчанию при отсутствии возвращаемых данных
     * @return variant
     */
    public function getServer($key=null,$default=null)
    {
        if (null===$key) 
        {
            return $_SERVER;
        }
        return (isset($_SERVER[$key]))?$_SERVER[$key]:$default;
    }
    
    /**
     * Получаем данные, отправленные клиентом.
     * @return variant
     */
    public function getInputData($typeMetod="JSON")
    {
        switch ($typeMetod)
        {
            case 'JSON':
                return json_decode($this->InputPhpData(),TRUE);
            break;
            default:
                return FALSE;
            break;
        } 
    }

    /**
     * Получаем данные, отправленные клиентом.
     * @return variant
     */
    private function InputPhpData()
    {
        return file_get_contents('php://input');         
    }
    
    /**
     * Метод устанавливает или получает быстрое сообщение.
     * @param variant $value - сообщение или группа сообщений
     * @return variant|boolean - сообщение или группа сообщений
     */
    public function FlashMessage($value=NULL)
    {
        $session=Syo_Session::getInstance();
        $path=$session->getPath();
        $session->setPath('');
        if (is_null($value))
        {
            //Возвращаем сообщения
            $res_value=$session->FlashMessage;
            unset($session->FlashMessage);
            $session->setPath($path);
            return $res_value;            
        }
        else
        {
            //Устанавливаем сообщения
            $session->FlashMessage=$value;
            $session->setPath($path);
            return TRUE;
        }
    }
}
?>