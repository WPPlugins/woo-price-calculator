<?php

namespace WSF\Form;

use WSF\WSF;

class CalculatorForm {
    
    private $wsf;
    
    private $form;
    
    private $calculatorModel;
    
    public function __construct(WSF $wsf) {
        $this->wsf = $wsf;
        
        /* MODELS */
        $this->calculatorModel = $this->wsf->get('\\WSF\\Model', 'Model', 'CalculatorModel', array($this->wsf));
        
        /* HELPERS */
        $this->calculatorHelper  = $this->wsf->get('\\WSF\\Helper', 'Helper', 'CalculatorHelper', array($this->wsf));
        $this->wooCommerceHelper = $this->wsf->get('\\WSF\\Helper', 'Helper', 'WooCommerceHelper', array($this->wsf));
        
        $this->form[] = array(
            'name' => 'name'
        );
        
        $this->form[] = array(
            'name' => 'description'
        );
        
        $this->form[] = array(
            'name'      => 'fields',
            'default'   => array()
        );
        
        $this->form[] = array(
            'name'      => 'products',
            'default'   => array()
        );
        
        $this->form[] = array(
            'name'      => 'product_categories',
            'default'   => array()
        );
        
        $this->form[] = array(
            'name'      => 'options',
            'default'   => array()
        );
        
        $this->form[] = array(
            'name'      => 'field_orders',
            'default'   => array(),
        );
        
        $this->form[] = array(
            'name' => 'formula'
        );
        
        $this->form[] = array(
            'name' => 'redirect'
        );
        
        $this->form[] = array(
            'name' => 'empty_cart'
        );
        
        $this->form[] = array(
            'name' => 'type'
        );
        
        $this->form[] = array(
            'name' => 'theme'
        );
        
        $this->form[] = array(
            'name' => 'system_created'
        );

    }
    
    public function check($record, $params = array()){
        
        $errors = array();
        
        /* Controllo che il nome non sia vuoto */
        if(empty($record['name'])){
            $errors[] = "- Name must not be empty.";
        }

        $errors     = array_merge($errors, $this->calculatorHelper->checkCalculatorDuplicate($record, $params['id']));
    
        return $errors;
    }
    
    public function getForm(){
        return $this->form;
    }
    
    public function setForm($form){
        $this->form = $form;
    }
}

