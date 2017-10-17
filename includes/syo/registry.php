<?php

class Syo_Registry Implements ArrayAccess
{
    protected static $instance;
    private $_vars=array();

    public static function getInstance()
    {
        if (self::$instance===null)
        {
            self::$instance=new Syo_Registry();
        }
        return self::$instance;
    }
    
    function __set($key,$var)
    {
        if (isset($this->_vars[$key])==true)
        {
                throw new Exception('Unable to set var `' . $key . '`. Already set.');
        }
        $this->_vars[$key]=$var;
        return true;
    }

    function set($key,$var)
    {
        $this->_vars[$key]=$var;
        return true;
    }
    
    function __get($key)
    {
        if (isset($this->_vars[$key])==false)
        {
                return null;
        }
        return $this->_vars[$key];
    }

    function get($key)
    {
        if (isset($this->_vars[$key])==false)
        {
                return null;
        }
        return $this->_vars[$key];
    }

    function __unset($key) 
    {
        unset($this->_vars[$key]);
    }

    function delete($key) 
    {
        unset($this->_vars[$key]);
    }
    
    function __isset($key) 
    {
        return isset($this->_vars[$key]);
    }

    function is_set($key) 
    {
        return isset($this->_vars[$key]);
    }
    
    function offsetExists($offset)
    {
        return isset($this->_vars[$offset]);
    }

    function offsetGet($offset) 
    {
        return $this->_vars[$offset];
    }

    function offsetSet($offset,$value)
    {
        $this->_vars[$offset]=$value;
    }

    function offsetUnset($offset)
    {
        unset($this->_vars[$offset]);
    }
}

?>
