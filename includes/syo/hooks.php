<?php
/**
 * Класс для работы с хуками.
 * 
 * Create 09.01.2016
 * Update 09.01.2016
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.0
 * 
 * @package syo
 */
class Syo_Hooks
{
    /**
     * Одиночка.
     * @var Syo_Hooks 
     */
    private static $instance;
    
    /**
     *
     * @var array
     */
    protected $hooks=array();
    
    /**
     * Конструктор.
     * Загружает конфигурацию hooks.
     */
    private function __construct()
    {
        //Получаем конфигурацию
        $app_config=Syo_Registry::getInstance()->get('config');
        if (isset($app_config['application']['hooks']) && is_array($app_config['application']['hooks']))
            $this->addArray($app_config['application']['hooks']);
    }
    
    /**
     * Заглушка clone.
     */
    private function __clone(){}
    
    /**
     * Заглушка wakeup.
     */
    private function __wakeup(){}
    
    /**
     * Возвращает единственный экземпляр класса Singleton.
     * @return Syo_Hooks
     */    
    public static function getInstance()
    {
        if (empty(self::$instance)) 
        {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Добавить hook.
     * @param string $name - имя hook
     * @param variant $callable - вызываемый объект
     * @param integer $priority - приоритет hook
     * @return Syo_Hooks
     */
    public function add($name,$callable,$priority=10)
    {
        //Устанавливаем пустой hook если он не определён
        $this->issetHook($name);   
        //Добавляем hook
        if (is_callable($callable)) 
            $this->hooks[$name][(int) $priority][]=$callable;
        return $this;
    }
    
    /**
     * Добавляет список hooks.
     * @param array $hooks - список hook для добавления
     * @return Syo_Hooks
     */
    public function addArray($hooks)
    {
        if (is_array($hooks))
            foreach ($hooks as $hook)
            {
                if (isset($hook['name']) && isset($hook['callable']))
                    $this->add($hook['name'],$hook['callable'],(isset($hook['priority'])?$hook['priority']:10));
            }
        return $this;
    }
    
    /**
     * Вызывает hook.
     * @param string $name - имя hook
     */
    public function apply($name)
    {
        //Устанавливаем пустой hook если он не определён
        $this->issetHook($name);
        //Hook существует
        if (!empty($this->hooks[$name]))
        {
            //Сортируем по приоритету
            if (count($this->hooks[$name])>1)
                ksort($this->hooks[$name]);
            //Получаем аргументы, пришедшие в функцию
            $args=func_get_args();
            array_shift($args);
            //Выполняем hooks
            foreach ($this->hooks[$name] as $priority)
            {
                if (!empty($priority))
                    foreach ($priority as $callable) 
                        call_user_func_array($callable,$args);
            }
        }
    }
    
    /**
     * Вызывает hookfilter.
     * @param string $name - имя hookfilter
     * @param variant $value
     */
    public function applyFilter($name,$value)
    {
        //Устанавливаем пустой hookfilter если он не определён
        $this->issetHook($name);
        //Hook существует
        if (!empty($this->hooks[$name]))
        {
            //Сортируем по приоритету
            if (count($this->hooks[$name])>1)
                ksort($this->hooks[$name]);
            //Получаем аргументы, пришедшие в функцию
            $args=func_get_args();
            array_shift($args);
            //Выполняем hookfilter
            foreach ($this->hooks[$name] as $priority)
            {
                if (!empty($priority))
                    foreach ($priority as $callable) 
                        $args[0]=call_user_func_array($callable,$args);
            }
            return $args[0];
        }
        return $value;
    }
    
    /**
     * Устанавливает пустой hook, если он не определён.
     * @param string $name - имя hook
     */
    private function issetHook($name)
    {
        if (!isset($this->hooks[$name]))
            $this->hooks[$name]=array();        
        return TRUE;
    }
    
    /**
     * Получить hook.
     * @param string $name - имя hook | hookfilter
     * @return array|null
     */
    public function getHooks($name=null)
    {
        if (!is_null($name)) 
            return isset($this->hooks[$name])?$this->hooks[$name]:null;
        else
            return $this->hooks;
    }
    
    /**
     * Очищает hooks список.
     * @param string $name - имя hook
     * @return Syo_Hooks
     */
    public function clear($name=null)
    {
        if (!is_null($name) && isset($this->hooks[$name]))
            $this->hooks[$name]=array();
        else
            $this->hooks=array();
        return $this;
    }
}
?>