<?php

namespace WSF\Form;

use WSF\WSF;

class RegexForm {
    
    private $wsf;
    
    private $form;
    
    private $calculatorModel;
    
    public function __construct(WSF $wsf) {
        $this->wsf = $wsf;
        
        $this->form[] = array(
            'name' => 'name'
        );
        
        $this->form[] = array(
            'name' => 'regex'
        );

    }
    
    public function check($record, $params = array()){
        
        $errors = array();
        
        if(empty($record['name'])){
            $errors[]   = "- Name must not be empty.";
        }
        
        if(@preg_match($record['regex'], "") === false){
            $error          = error_get_last();
            $errors[]       = "- Regex Error: {$error['message']}.";
        }

        return $errors;
    }
    
    public function getForm(){
        return $this->form;
    }
    
    public function setForm($form){
        $this->form = $form;
    }
}

