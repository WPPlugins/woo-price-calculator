<?php

namespace WSF\Form;

use WSF\WSF;

class SettingsForm {
    
    private $wsf;
    
    private $form;
    
    public function __construct(WSF $wsf) {
        $this->wsf = $wsf;
               
        $this->form[] = array(
            'name' => 'custom_css'
        );
        
        $this->form[] = array(
            'name' => 'cart_edit_button_class',
        );
        
    }
    
    public function check($record, $params = array()){

        $errors = array();

        return $errors;
    }
    
    public function getForm(){
        return $this->form;
    }
    
    public function setForm($form){
        $this->form = $form;
    }
}

