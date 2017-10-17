<?php
/**
 * Базовый контроллер для сайта.
 * 
 * Create 26.08.2014
 * Update 26.08.2014
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.0
 * 
 * @package module
 * @subpackage app
 */
class Module_App_Native extends Syo_Controller
{
    /**
     * Инициализирует контроллер.
     */
    public function init() 
    {
        
    }

    /**
     * Событие по умолчанию.
     */
    public function indexAction() 
    {
        
    }
    
    /**
     * Метод выполняется перед выводом шаблона.
     */
    public function beforeTemplate()
    {
        $aHeader=Syo_Registry::getInstance()->get('headerTpl');
        if (is_array($aHeader))
            $this->view->header=implode('',$aHeader);
        else
            $this->view->header='';
    } 
    
    /**
     * Загружает файл конфигурации аддона.
     * @param string $name - имя файла содержащий конфигурацию аддона.
     */
    public function LoadConfig($name=NULL)
    {
        if (is_null($name))
            //Загружаем файл конфигурации аддона
            $this->config[$this->getAddon()]=Syo_Config::Load($this->getAddon(),APPPATH.DIRSEP.$this->getAddon().DIRSEP.'configs');
        else
            //Загружаем файл с глобальной конфигурацией
            $this->config[$name]=Syo_Config::Load($name,APPPATH.DIRSEP.$this->getAddon().DIRSEP.'configs');
    }
    
    /**
     * Загружает глобальный файл конфигурации аддона.
     * @param string $name - имя в массиве конфигурации
     * @param string $file - имя файла содержащий конфигурацию аддона
     */
    public function LoadGlobalConfig($name,$file=NULL)
    {
        if (is_null($file))
        {
            $this->config[$name]=Syo_Config::Load($name);
        }
        else
        {
            $this->config[$name]=Syo_Config::Load($file);            
        }
    }
    
    /**
     * Добавляет стили к выводимой странице.
     * @param array | string  $name - имя стиля
     * @param string $directory - директория расположения
     * @param string $ie - условие для IE
     */
    public function addCssHtml($name,$directory=NULL,$ie=NULL)
    {   
        $aHeader=Syo_Registry::getInstance()->get('headerTpl');
        if (!is_null($directory)) $directory.='/';
        if (is_null($ie)) 
        {
            $sHeaderBegin='';
            $sHeaderEnd='';            
        }
        else 
        {
            $sHeaderBegin='<!--[if '.$ie.']>'."\n";
            $sHeaderEnd='<![endif]-->'."\n";            
        }
        if (is_array($name))
        {
            foreach ($name as $value) 
            {
                $aHeader[md5($directory.$value.'.css')]=$sHeaderBegin.='<link rel="stylesheet" type="text/css" href="/css/'.$directory.$value.'.css">'."\n".$sHeaderEnd;                
            }
        }
        else 
        {
            $aHeader[md5($directory.$name.'.css')]=$sHeaderBegin.'<link rel="stylesheet" type="text/css" href="/css/'.$directory.$name.'.css">'."\n".$sHeaderEnd;            
        }
        Syo_Registry::getInstance()->set('headerTpl',$aHeader);
    }
    
    /**
     * Добавляет js-скрипты к выводимой странице.
     * @param array | string  $name - имя js-скрипта
     * @param string $directory - директория расположения
     * @param string $ie - условие для IE
     */
    public function addJsHtml($name,$directory=NULL,$ie=NULL)
    {
        $aHeader=Syo_Registry::getInstance()->get('headerTpl');
        if (!is_null($directory)) $directory.='/';
        if (is_null($ie)) 
        {
            $sHeaderBegin='';
            $sHeaderEnd='';            
        }
        else 
        {
            $sHeaderBegin='<!--[if '.$ie.']>'."\n";
            $sHeaderEnd='<![endif]-->'."\n";            
        }
        if (is_array($name))
        {
            foreach ($name as $value) 
            {
                $aHeader[md5($directory.$value.'.js')]=$sHeaderBegin.'<script type="text/javascript" src="/js/'.$directory.$value.'.js"></script>'."\n".$sHeaderEnd;
            }
        }
        else 
        {
            $aHeader[md5($directory.$name.'.js')]=$sHeaderBegin.'<script type="text/javascript" src="/js/'.$directory.$name.'.js"></script>'."\n".$sHeaderEnd;
        }
        Syo_Registry::getInstance()->set('headerTpl',$aHeader);
    }
}
?>
