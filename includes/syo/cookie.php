<?php
/**
 * Класс для работы с Cookie.
 */
class Syo_Cookie
{
    /**
     * Одиночка.
     * @var Syo_Cookie 
     */
    protected static $instance;

    /**
     * Параметры Cookie.
     * @var array 
     */
    protected $_params=array(
        //время "жизни" переменной
        'expire'=>null,
        //путь к Cookie
        'path'=>null,
        //домен
        'domain'=>null,
        //передача Cookie через защищенное HTTPS-соединение
        'secure'=>null
    );

    /**
     * Возвращает единственный экземпляр класса. @return Singleton
     * @return Syo_Cookie
     */
    public static function getInstance()
    {
        if (self::$instance===null)
        {
            self::$instance=new Syo_Cookie();
        }
        return self::$instance;
    }

    /**
     * Конструктор
     */
    public function __construct()
    {
        //Получаем конфигурацию приложения
        $app_config=Syo_Registry::getInstance()->get('config');
        //Если существуют настройки для Cookie, передаём их классу
        if (isset($app_config['application']['cookie']))
        {
            $this->_params=$app_config['application']['cookie'];
        }
    }
    
    /**
     * Будет выполнен при чтении данных из недоступных свойств
     * @param string $key - имя свойства
     * @return variant - значение свойства
     */
    public function __get($key)
    {
        if (isset($_COOKIE[$key])==false)
        {
                return null;
        }
        return $_COOKIE[$key];
    }

    /**
     * Будет выполнен при записи данных в недоступные свойства.
     * @param string $key - имя свойства
     * @param variant $value - значение свойства
     * @return boolean
     */
    public function __set($key,$value)
    {
        $this->_setCookie($key,$value);
        return true;
    }
    
    /**
     * Будет выполнен при вызове unset() на недоступном свойстве.
     * @param string $value - имя свойства
     */
    public function __unset($value) 
    {
        $this->_removeCookie($value);
    }
    
    /**
     * Будет выполнен при использовании isset() или empty() на недоступных свойствах.
     * @param string $key - имя свойства
     * @return bool
     */
    public function __isset($key) 
    {
        return isset($_COOKIE[$key]);
    }
    
    /**
     * Возвращает массив Cookie.
     * @return array
     */
    public function getCookie()
    {
        return $_COOKIE;
    }
    
    /**
     * Сохраняет значение в Cookie.
     * @param string $name - имя Cookie
     * @param variant $value - значение Cookie
     */
    protected function _setCookie($name,$value="")
    {
        if ($this->_params['secure']!==null)
        {
            setcookie($name,$value,$this->_params['expire'],$this->_params['path'],$this->_params['domain'],$this->_params['secure']);
        }
        elseif ($this->_params['domain']!==null)
        {
            setcookie($name,$value,$this->_params['expire'],$this->_params['path'],$this->_params['domain']);
        }
        elseif ($this->_params['path']!==null)
        {
            setcookie($name,$value,$this->_params['expire'],$this->_params['path']);
        }
        elseif ($this->_params['expire']!==null)
        {
            setcookie($name,$value,$this->_params['expire']);
        }
        else
        {
            setcookie($name,$value);
        }
    }
    
    /**
     * Удаляет значение из Cookie.
     * @param string $name - имя Cookie
     */
    protected function _removeCookie($name)
    {
        if ($this->_params['secure']!==null)
        {
            setcookie($name,"",time()-3600,$this->_params['path'],$this->_params['domain'],$this->_params['secure']);
        }
        elseif ($this->_params['domain']!==null)
        {
            setcookie($name,"",time()-3600,$this->_params['path'],$this->_params['domain']);
        }
        elseif ($this->_params['path']!==null)
        {
            setcookie($name,"",time()-3600,$this->_params['path']);
        }
        elseif ($this->_params['expire']!==null)
        {
            setcookie($name,"",time()-3600);
        }
        else
        {
            setcookie($name,"",time()-3600);
        }
    }
    
    /**
     * Устанавливает время "жизни" переменной.
     * @param int $time - время "жизни" переменной
     */
    public function setTime($time)
    {
        $this->_params['expire']=$time;
    }

    /**
     * Устанавливает путь к Cookie.
     * @param string $path - путь к Cookie
     */
    public function setPath($path)
    {
        $this->_params['path']=$path;
    }

    /**
     * Устанавливает домен.
     * @param string $domain - домен
     */
    public function setDomain($domain)
    {
        $this->_params['domain']=$domain;
    }

    /**
     * Указывает, что Cookie передаются через защищенное HTTPS-соединение.
     * @param int $secure
     */
    public function setSecure($secure)
    {
        $this->_params['secure']=$secure;
    }
}

?>