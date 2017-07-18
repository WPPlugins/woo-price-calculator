<?php

namespace WSF\Controller;

use WSF\WSF;

class CalculatorController {
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
        
        /* HELPERS */
        $this->calculatorHelper     = $this->wsf->get('\\WSF\\Helper', 'Helper', 'CalculatorHelper', array($this->wsf));
        $this->wooCommerceHelper    = $this->wsf->get('\\WSF\\Helper', 'Helper', 'WooCommerceHelper', array($this->wsf));
        
        $this->MAX_EMPTY_COLUMNS    = 100;
        $this->MAX_EMPTY_ROWS       = 50;
    }
    
    public function indexAction(){
        $this->wsf->execute('index', 'index');
        
        $this->wsf->renderView('calculator/list.php', array(
            'list_header'    => array(
                'name'              => $this->wsf->trans('wpc.calculator.list.name'),
                'description'       => $this->wsf->trans('wpc.calculator.list.description'),
                'type'              => $this->wsf->trans('wpc.calculator.list.type'),
                'actions'           => $this->wsf->trans('wpc.actions'),
            ),
            'list_rows'      => $this->calculatorModel->get_list(),
        ));
        
        
    }
    
    public function loaderAction(){
        $this->wsf->execute('index', 'index');
        
        $this->wsf->renderView('calculator/load.php', array());

    }
    
    
    
    public function deleteAction(){
        $id = $this->wsf->requestValue('id');
        
        $this->calculatorModel->delete($id);
        
        $this->wsf->execute('calculator', 'index');
    }
    
    public function addAction(){
        $this->wsf->execute('index', 'index');
        
        $id             = $this->wsf->requestValue('id', null);
        $calculatorForm = $this->wsf->get('\\WSF\\Form', 'Form', 'CalculatorForm', array($this->wsf));
        $form           = $this->wsf->requestForm($calculatorForm);
        
        $task           = $this->wsf->requestValue('task');
        $mapping        = $this->wsf->requestValue('mapping');
        $type           = $this->wsf->requestValue('type');
        $loader_fields  = array();
        
        if(empty($type)){
            $type = "simple";
        }
        
        $fields                 = $this->fieldModel->get_field_list();
        
        $products               = $this->wooCommerceHelper->get_woocommerce_products();
        $productCategories      = $this->wooCommerceHelper->getWooCommerceProductCategories();
        
        $themes                 = $this->calculatorHelper->get_themes();
        
        $errors                 = array();
        $warnings               = array();
        
        if($this->wsf->isPost() && $task == 'calculator'){
            if($type == "simple" || ($type == "excel" && $mapping != 1)){
                $errors         = $calculatorForm->check($form, array('id' => null));
                $warnings       = $this->calculatorHelper->checkProductPrices($form['products']);
                
                if($type == "excel"){
                    $form['options']         = $_SESSION['woo-price-calculator']['admin']['loader_fields'];
                }
                
                /* In questo modo riesco a prendere l'ordine dei campi */
                if(!empty($form['field_orders'])){
                    $form['fields']         = explode(",", $form['field_orders']);
                }
            
                if(count($errors) == 0){
                        $id     = $this->calculatorModel->save($form, $id);
                        $this->wsf->renderView('app/form_completed.php', array(
                            'recordName' => $this->wsf->trans('Calculator'),
                            'mode'       => $this->wsf->trans('created'),
                            'url'        => $this->wsf->adminUrl(array('controller' => 'calculator'))
                        ));
                }
            }
        }else{          
            if($type == "excel"){
            }
        
            
        }

        $this->wsf->renderView('calculator/calculator.php', array(
            'id'                        => $id,
            'title'                     => $this->wsf->trans('Add'),
            'action'                    => 'add',
            
            'errors'                    => $errors,
            'warnings'                  => $warnings,
            
            'form'                      => $form,

            'fields'                    => $fields,
            'orderedFields'             => $this->calculatorHelper->orderFields($form['fields']),
            
            'products'                  => $products,
            'productCategories'         => $productCategories,
            
            'themes'                    => $themes,
            
            'loader_fields'             => $loader_fields,
            
            'type'                      => $type,
        ));

    }
    
    public function editAction(){
        $this->wsf->execute('index', 'index');
        
        $calculatorForm         = $this->wsf->get('\\WSF\\Form', 'Form', 'CalculatorForm', array($this->wsf));
        
        $id                     = $this->wsf->requestValue('id');
        $task                   = $this->wsf->requestValue('task');
        $fields                 = $this->fieldModel->get_field_list();
        
        $products               = $this->wooCommerceHelper->get_woocommerce_products();
        $productCategories      = $this->wooCommerceHelper->getWooCommerceProductCategories();
       
        $themes                 = $this->calculatorHelper->get_themes();
        $calculator             = $this->calculatorModel->get($id);
        $calculatorType         = $this->wsf->isset_or($calculator->type, "simple");
        
        $calculatorFields       = json_decode($this->wsf->isset_or($calculator->fields, "{}"), true);
        $calculatorOptions      = json_decode($this->wsf->isset_or($calculator->options, "{}"), true);
           
        $form = $this->wsf->requestForm($calculatorForm, array(
            'name'                  => $calculator->name,
            'description'           => $this->wsf->isset_or($calculator->description, ""),
            'fields'                => $calculatorFields,
            'products'              => json_decode($this->wsf->isset_or($calculator->products, "{}"), true),
            'product_categories'    => json_decode($this->wsf->isset_or($calculator->product_categories, "{}"), true),
            'options'               => $calculatorOptions,
            'formula'               => $this->wsf->isset_or($calculator->formula, ""),
            'redirect'              => $this->wsf->isset_or($calculator->redirect, 0),
            'empty_cart'            => $this->wsf->isset_or($calculator->empty_cart, 0),
            'type'                  => $calculatorType,
            'theme'                 => $this->wsf->isset_or($calculator->theme, ""),
            'system_created'        => $this->wsf->isset_or($calculator->system_created, 0),
        ));
            
        $errors         = array();
        $warnings       = array();
        
        if($this->wsf->isPost() && $task == 'calculator'){
            $form               = $this->wsf->requestForm($calculatorForm);
            $errors             = $calculatorForm->check($form, array('id' => $id));
            
            $warnings           = $this->calculatorHelper->checkProductPrices($form['products']);

            /* In questo modo riesco a prendere l'ordine dei campi */
            if(!empty($form['field_orders'])){
                $form['fields']     = explode(",", $form['field_orders']);
            }

            if(count($errors) == 0){       

                    /* Non modifico le informazioni delle opzioni */
                    if($calculatorType == 'excel'){
                        $form['options']     = $calculatorOptions;
                    }
        
        
                    $this->calculatorModel->save($form, $id);
                    
                    $calculator         = $this->calculatorModel->get($id);
                    $form['fields']     = json_decode($calculator->fields, true);
                    
                    $this->wsf->renderView('app/form_completed.php', array(
                        'recordName' => $this->wsf->trans('Calculator'),
                        'mode'       => $this->wsf->trans('saved'),
                        'url'        => $this->wsf->adminUrl(array('controller' => 'calculator'))
                    ));
            }

        }

        $this->wsf->renderView('calculator/calculator.php', array(
            'id'                        => $id,
            'title'                     => $this->wsf->trans('Edit'),
            'action'                    => 'edit',
            'form'                      => $form,
            
            'errors'                    => $errors,
            'warnings'                  => $warnings,

            'fields'                    => $fields,
            'orderedFields'             => $this->calculatorHelper->orderFields($form['fields']),
            
            'products'                  => $products,
            'productCategories'         => $productCategories,
            
            'themes'                    => $themes,

            'loader_fields'             => $fields,
            
            'type'                      => $calculator->type,

        ));

    }
        
}
