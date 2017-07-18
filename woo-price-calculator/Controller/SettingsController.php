<?php

namespace WSF\Controller;

use WSF\WSF;

class SettingsController {
    private $wsf;
    private $db;
    
    private $tableHelper;
    private $calculatorHelper;

    private $fieldModel;
    private $calculatorModel;
    
    private $wooCommerceHelper;
    
    public function __construct(WSF $wsf){
        if(!session_id()){
            session_start();
        }
        
        $this->wsf  = $wsf;
        $this->db   = $this->wsf->getDB();
        
        $this->tableHelper          = $this->wsf->get('\\WSF\\Helper', 'Helper', 'TableHelper', array($this->wsf));
        
        /* MODELS */
        $this->fieldModel           = $this->wsf->get('\\WSF\\Model', 'Model', 'FieldModel', array($this->wsf));
        $this->calculatorModel      = $this->wsf->get('\\WSF\\Model', 'Model', 'CalculatorModel', array($this->wsf));
        $this->settingsModel        = $this->wsf->get('\\WSF\\Model', 'Model', 'SettingsModel', array($this->wsf));
        
        /* HELPERS */
        $this->calculatorHelper     = $this->wsf->get('\\WSF\\Helper', 'Helper', 'CalculatorHelper', array($this->wsf));
        $this->wooCommerceHelper    = $this->wsf->get('\\WSF\\Helper', 'Helper', 'WooCommerceHelper', array($this->wsf));

    }
    
    public function indexAction(){
        $this->wsf->execute('index', 'index');
        
        $settingsForm           = $this->wsf->get('\\WSF\\Form', 'Form', 'SettingsForm', array($this->wsf));
        $errors                 = array();
        $picklistItemsData      = array();
        $radioItemsData         = array();
        
        $task                   = $this->wsf->requestValue('task');
        $form                   = null;

        $values                     = $this->settingsModel->getValues();

        $form = $this->wsf->requestForm($settingsForm, array(
            'cart_edit_button_class'            => $this->wsf->isset_or($values['cart_edit_button_class'], ''),
            'custom_css'                        => file_get_contents($this->wsf->getUploadPath("style/custom.css")),
        ));
        
        if($this->wsf->isPost() && $task == 'save'){
            $form                       = $this->wsf->requestForm($settingsForm);
            $errors                     = array_merge($settingsForm->check($form, array()), $errors);
                            
            if(count($errors) == 0){

                foreach($form as $key => $value){
                    if($key == "custom_css"){
                        file_put_contents($this->wsf->getUploadPath("style/custom.css"), $form['custom_css']);
                    }else{
                        $this->settingsModel->setValue($key, $value);
                    }

                }
                
                
                $this->wsf->renderView('app/form_completed.php', array(
                    'recordName' => $this->wsf->trans('wpc.settings'),
                    'mode'       => $this->wsf->trans('wpc.saved'),
                ));
            }
        }

        $this->wsf->renderView('settings/settings.php', array(
            'errors'                            => $errors,
            'form'                              => $form,
        ));
    }
    
        
}
