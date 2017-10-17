<?php
class Syo_Template_Base
{
    protected $_tpl=array();
    protected $_path="";
    protected $_ext=".tpl";
    protected $_defaulttpl="";
    
    public function __construct($dir=null,$ext=null)
    {
        if ($dir!==null) $this->_path=$dir;
        if ($ext!==null) $this->_ext=$ext;        
    }
    
    public function setPath($dir)
    {
        if ($dir!="") $this->_path=$dir;        
    }
    
    protected function _load($namefile)
    {
        $nametpl=$namefile;
        $namefile=$this->_path.DIRSEP.$namefile.$this->_ext;
        try
        {
            if (file_exists($namefile))
            {
                $this->_tpl[$nametpl]['tpl']=file_get_contents($namefile);
                $this->_tpl[$nametpl]['vars']=array();
                $this->_defaulttpl=$nametpl;
            }
            else
            {
                throw new Syo_Exception('Error Template: Not found file tpl!');
            }
        }
        catch (Syo_Exception $e)
        {
            echo $e;
            exit();
        }
    }
    
    public function addTpl($nametpl)
    {
        if ($nametpl!='') $this->_load($nametpl);
    }

    public function removeTpl($nametpl)
    {
        if ($nametpl!='') unset($this->_tpl[$nametpl]);
    }
    
    public function setTpl($nametpl)
    {
        if ($nametpl!="") $this->_defaulttpl=$nametpl;
    }

    public function setVars($nametpl,$vars=array())
    {
        $this->_tpl[$nametpl]['vars']=$vars;
        
    }
    
    public function removeVars($nametpl,$index=null)
    {
        $this->_tpl[$nametpl]['vars']=array();
    }
    
    protected function _out($nametpl)
    {
        $tpl=$this->_tpl[$nametpl]['tpl'];
        foreach ($this->_tpl[$nametpl]['vars'] as $key=>$data)
        {
            $tpl=str_replace('{'.$key.'}',$data,$tpl);
        }
        return $tpl;
    }
    
    public function __toString()
    {
        return $this->_out($this->_defaulttpl);
    }
}
?>