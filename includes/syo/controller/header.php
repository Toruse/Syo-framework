<?php
/**
 * Класс для отправки HTTP заголовка.
 * 
 * Create 04.05.2015
 * Update 04.05.2015
 * 
 * @author Torus <notpad@mail.ru>
 * @version 1.0.0
 * 
 * @package syo
 * @subpackage controller
 */
class Syo_Controller_Header
{
    private $versionhttp='1.1';
    
    protected $header=array();
    
    public function Redirect($url)
    {
        header("Location: ".$url);
        exit();
    }
    
    /**
     * Выполняет перенаправление браузера.
     * @param string $url - url перенаправления браузера
     * @param integer $time - значение указывающее через сколько секунд произойдёт перенаправление
     */
    public function Refresh($url='',$time=0)
    {
        header("Refresh: ".$time."; URL=".$url);
        exit();
    }
    
    public function http($name=null)
    {
        $query='HTTP/'.$this->versionhttp;
        if (is_null($name))
        {
            $query.=' 200 OK';
        }
        else
        {
            $query.=' '.$name;
        }
        header($query);
        return TRUE;
    }

    public function http200()
    {
        return $this->http('200 OK');
    }

    public function http201()
    {
        return $this->http('201 Created');
    }

    public function http202()
    {
        return $this->http('202 Accepted');
    }

    public function http204()
    {
        return $this->http('204 No Content');
    }

    public function http401()
    {
        return $this->http('401 Unauthorized');
    }

    public function http404()
    {
        return $this->http('404 Not Found');
    }

    public function http500()
    {
        return $this->http('500 Internal Server Error');
    }

    public function gotoUrl($url)
    {
        $this->Redirect($url);
    }
}
?>