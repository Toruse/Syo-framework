<?php
class Syo_Form_Password extends Syo_Form_Text
{    
    public function __construct($name,$post=false)
    {
        parent::__construct($name,$post);
        $this->_type='password';
    }
}
?>