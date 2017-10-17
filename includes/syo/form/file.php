<?php
class Syo_Form_File extends Syo_Form_Field
{
    protected $_dir;
    protected $_prefix="";
    protected $_extRename=array(
                            "#\.php#is",
                            "#\.phtml#is",
                            "#\.php3#is",
                            "#\.html#is",
                            "#\.htm#is",
                            "#\.hta#is",
                            "#\.pl#is",
                            "#\.xml#is",
                            "#\.inc#is",
                            "#\.shtml#is",
                            "#\.xht#is",
                            "#\.xhtml#is"
                         );
    
    public function __construct($name)
    {
        parent::__construct($name,false);
        $this->_type='file';
        if (isset($_FILES[$this->_name])) $this->_value=$_FILES[$this->_name];
    }
    
    public function setDir($dir)
    {
        $this->_dir=$dir;
    }
    
    public function setPrefix($prefix)
    {
        $this->_prefix=$prefix;
    }
    
    public function isLoadFile()
    {
        if (!empty($this->_value['name']) && !empty($this->_value['tmp_name']))
        {
            $this->_value['name']=$this->EncodeString($this->_value['name']);
            $part_parts=pathinfo($this->_value['name']);
            $ext='.'.$part_parts['extension'];
            $path=basename($this->_value['name'],$ext);
            $addext=$ext;
            foreach ($this->_extRename as $exten)
            {
                if (preg_match($exten,$ext)) $addext=".txt";
            }
            $path.=$addext;
            $path=str_replace(DIRSEP.DIRSEP,DIRSEP,$this->_dir.DIRSEP.$this->_prefix.$path);
            if (move_uploaded_file($this->_value['tmp_name'],$path))
            {
                @unlink($this->_value['tmp_name']);
                @chmod($path,0644);
            }
        }
    }
    
    public function getNameFile()
    {
        return $this->_prefix.$this->_value;
    }

    public function getNameFileEncode()
    {
        return $this->EncodeString($this->_prefix.$this->_value['name']);
    }
}
?>