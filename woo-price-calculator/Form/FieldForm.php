<?php

namespace WSF\Form;

use WSF\WSF;

class FieldForm {
    
    private $wsf;
    
    private $form;
    
    public function __construct(WSF $wsf) {
        $this->wsf = $wsf;
        
        $this->form[] = array(
            'name' => 'label'
        );
        
        $this->form[] = array(
            'name' => 'short_label'
        );
        
        $this->form[] = array(
            'name' => 'description'
        );
        
        $this->form[] = array(
            'name' => 'type'
        );
        
        $this->form[] = array(
            'name' => 'checkbox_check_value'
        );
        
        $this->form[] = array(
            'name' => 'checkbox_uncheck_value'
        );
        
        $this->form[] = array(
            'name' => 'picklist_items'
        );
        
        $this->form[] = array(
            'name' => 'radio_items'
        );
        
        $this->form[] = array(
            'name' => 'items_list_id'
        );
        
        $this->form[] = array(
            'name' => 'checkbox_default_status'
        );
        
        $this->form[] = array(
            'name' => 'numeric_default_value'
        );
        
        $this->form[] = array(
            'name' => 'numeric_max_value'
        );
        
        $this->form[] = array(
            'name' => 'numeric_max_value_error'
        );
        
        $this->form[] = array(
            'name' => 'numeric_min_value'
        );
                
        $this->form[] = array(
            'name' => 'numeric_min_value_error'
        );
        
        $this->form[] = array(
            'name' => 'numeric_decimals'
        );
        
        $this->form[] = array(
            'name' => 'numeric_decimal_separator'
        );
        
        $this->form[] = array(
            'name' => 'text_default_value'
        );
        
        $this->form[] = array(
            'name' => 'text_regex'
        );
        
        $this->form[] = array(
            'name' => 'text_regex_error'
        );
        
        $this->form[] = array(
            'name' => 'system_created'
        );
    }
    
    public function check($record, $params = array()){

        $errors = array();
        if(empty($record['label'])){
            $errors[] = "- " . $this->wsf->trans('Field Label must not be empty');
        }
        
        if(empty($record['type'])){
            $errors[] = "- " . $this->wsf->trans('Field Type must not be empty');
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

