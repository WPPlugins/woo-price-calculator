<?php

namespace WSF\Controller;

use WSF\WSF;

class FieldController {
    var $wsf;
    
    var $db;
    
    public function __construct(WSF $wsf){
        $this->wsf = $wsf;
        $this->db = $this->wsf->getDB();

        $this->calculatorModel      = $this->wsf->get('\\WSF\\Model', 'Model', 'CalculatorModel', array($this->wsf));
        $this->fieldModel           = $this->wsf->get('\\WSF\\Model', 'Model', 'FieldModel', array($this->wsf));
        $this->regexModel           = $this->wsf->get('\\WSF\\Model', 'Model', 'RegexModel', array($this->wsf));
        
        $this->fieldHelper          = $this->wsf->get('\\WSF\\Helper', 'Helper', 'FieldHelper', array($this->wsf));
        $this->tableHelper          = $this->wsf->get('\\WSF\\Helper', 'Helper', 'TableHelper', array($this->wsf));
        
        $currentAction              = $this->wsf->getCurrentActionName();

    }
    
    public function indexAction(){
        $this->wsf->execute('index', 'index');

        $this->wsf->renderView('fields/list.php', array(
            'list_header'    => array(
                'label'         => $this->wsf->trans('wpc.field.list.label'),
                'name'          => $this->wsf->trans('wpc.field.list.name'),
                'type'          => $this->wsf->trans('wpc.field.list.type'),
                'description'   => $this->wsf->trans('wpc.field.list.description'),
                'actions'       => $this->wsf->trans('wpc.actions'),
            ),
            'list_rows'      => $this->fieldModel->get_field_list(),
        ));
    }

    public function deleteAction(){
        $id = $this->wsf->requestValue('id');
               
        $error  = $this->fieldHelper->checkFieldUsage($id);
        
        if(!empty($error)){
            $this->wsf->execute('index', 'index');
            $this->wsf->renderView('fields/field_error.php', array(
                'error'     => $error,
            ));
        }else{
            $this->fieldModel->delete($id);
        }
        
        $this->wsf->execute('field', 'index');
    }
    
    public function formAction(){
        $this->wsf->execute('index', 'index');
        
        $fieldForm              = $this->wsf->get('\\WSF\\Form', 'Form', 'FieldForm', array($this->wsf));
        $errors                 = array();
        $picklistItemsData      = array();
        $radioItemsData         = array();
        
        $id                     = $this->wsf->requestValue('id');
        $task                   = $this->wsf->requestValue('task');
        $form                   = null;
        
        if(!empty($id)){
            $record                     = $this->fieldModel->get_field_by_id($id);
            $options                    = json_decode($record->options, true);
            
        }else{
            /*WPC-FREE*/
            if($this->wsf->getLicense() != 1){
                if($this->fieldModel->getFieldCount() >= (int)base64_decode("NQ==")){
                    $this->wsf->renderView('fields/field_error.php', array(
                        'error'     => base64_decode($this->wsf->trans('wpc.fields.max')),
                    ));

                    return;
                }
            }
            /*/WPC-FREE*/
        }
        
        $form = $this->wsf->requestForm($fieldForm, array(
            'label'                             => $this->wsf->isset_or($record->label, ''),
            'short_label'                       => $this->wsf->isset_or($record->short_label, ''),
            'description'                       => $this->wsf->isset_or($record->description, ''),
            'type'                              => $this->wsf->isset_or($record->type, ''),
            'checkbox_check_value'              => $this->wsf->isset_or($options['checkbox']['check_value'], ''),
            'checkbox_uncheck_value'            => $this->wsf->isset_or($options['checkbox']['uncheck_value'], ''),
            
            'items_list_id'                     => $this->wsf->isset_or($options['items_list_id'], 1),
            'picklist_items'                    => $this->wsf->isset_or($options['picklist_items'], ""),
            'radio_items'                       => $this->wsf->isset_or($options['radio']['radio_items'], ""),
            

            'checkbox_default_status'           => $this->wsf->isset_or($options['checkbox']['default_status'], 0),

            'numeric_default_value'             => $this->wsf->isset_or($options['numeric']['default_value'], ""),
            'numeric_max_value'                 => $this->wsf->isset_or($options['numeric']['max_value'], ""),
            'numeric_max_value_error'           => $this->wsf->isset_or($options['numeric']['max_value_error'], ""),
            'numeric_min_value'                 => $this->wsf->isset_or($options['numeric']['min_value'], ""),
            'numeric_min_value_error'           => $this->wsf->isset_or($options['numeric']['min_value_error'], ""),
            'numeric_decimals'                  => $this->wsf->isset_or($options['numeric']['decimals'], ""),
            'numeric_decimal_separator'         => $this->wsf->isset_or($options['numeric']['decimal_separator'], ""),

            'text_default_value'                => $this->wsf->isset_or($options['text']['default_value'], ""),
            'text_regex'                        => $this->wsf->isset_or($options['text']['regex'], ""),
            'text_regex_error'                  => $this->wsf->isset_or($options['text']['regex_error'], ""),

            'system_created'                    => $this->wsf->isset_or($record->system_created, 0),
        ));
        

        if($this->wsf->isPost() && $task == 'field_form'){
            $form                       = $this->wsf->requestForm($fieldForm);
            $errors                     = array_merge($fieldForm->check($form, array('id' => $id)), $errors);
            
            $picklistItemsData          = json_decode($this->wsf->requestValue('picklist_items'), true);
            $radioItemsData             = json_decode($this->wsf->requestValue('radio_items'), true);
                
            if(count($errors) == 0){
                $insertId     = $this->fieldModel->save($form, $id);

                $id           = (empty($insertId))?$id:$insertId;

                $record       = $this->fieldModel->get_field_by_id($id);
                $options      = json_decode($record->options, true);

                $this->wsf->renderView('app/form_completed.php', array(
                    'recordName' => $this->wsf->trans('Field'),
                    'mode'       => $this->wsf->trans('created'),
                    'url'        => $this->wsf->adminUrl(array('controller' => 'field'))
                ));
            }
        }else{
            $picklistItemsData          = $this->fieldHelper->get_field_picklist_items($record);
            $radioItemsData             = $this->fieldHelper->get_field_radio_items($record);
        }

        $this->wsf->renderView('fields/field.php', array(
            'title'                             => $this->wsf->trans('Add'),
            'errors'                            => $errors,
            'form'                              => $form,
            
            
            'id'                                => $id,
            
            'picklist_items_data'               => $picklistItemsData,
            'radio_items_data'                  => $radioItemsData,
        ));

    }

}
