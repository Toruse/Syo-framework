<?php
class Syo_Form_Image extends Syo_Form_Field
{    
    public function __construct($name,$post=false)
    {
        parent::__construct($name,$post);
        $this->_type='image';
    }
    
    public function setSrc($url='')
    {
        $this->setAttribute('src',$url);
    }

    public function setAlt($str='')
    {
        $this->setAttribute('alt',$str);
    }    
}
?>