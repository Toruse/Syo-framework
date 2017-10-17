<?php
class Syo_Form_Checkbox extends Syo_Form_Field
{    
    private $checked=false;
    
    public function __construct($name,$post=false)
    {
        parent::__construct($name,$post);
        $this->_type='checkbox';
    }
    
    public function setChecked($checked)
    {
        if ($checked)
        {
            $this->checked=true;
        }
        else
        {
            $this->checked=false;            
        }
    }
    
    public function getInput($hidden=false,$value=0)
    {
        $hiddenform='';
        $input='<input';
        if (!empty($this->_type)) $input.=' type="'.$this->_type.'"';
        if (!empty($this->_name)) $input.=' name="'.$this->_name.'" id="'.$this->_name.'"';
        if (!empty($this->_value)) $input.=' value="'.$this->_value.'"';
        if ($this->checked) $input.=' checked';
        foreach ($this->_attributes as $attr=>$value)
        {
            $input.=' '.$attr.'="'.$value.'"';
        }
        $input.='>';
        
        if ($hidden)
        {
            $hiddenform='<input';
            $hiddenform.=' type="hidden"';
            if (!empty($this->_name)) $hiddenform.=' name="'.$this->_name.'" id="'.$this->_name.'"';
            $hiddenform.=' value="'.$value.'"';
            $hiddenform.='>';
        }

        return $hiddenform.$input;
    }
}
?>