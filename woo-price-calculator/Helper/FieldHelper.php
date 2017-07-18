<?php

namespace WSF\Helper;

use WSF\WSF;

class FieldHelper {
    var $wsf;
    
    var $fieldModel;
    
    public function __construct(WSF $wsf){
        $this->wsf = $wsf;
        
        /* MODELS */
        $this->calculatorModel      = $this->wsf->get('\\WSF\\Model', 'Model', 'CalculatorModel', array($this->wsf));
        $this->fieldModel           = $this->wsf->get('\\WSF\\Model', 'Model', 'FieldModel', array($this->wsf));
    }
    /*
     * Ritorna il tipo di campo
     */
    public function get_field_type($type){
            if($type == "checkbox"){
                return $this->wsf->trans("Checkbox");
            }else if($type == "numeric"){
                return $this->wsf->trans("Numeric");
            }else if($type == "picklist"){
                return $this->wsf->trans("Picklist");
            }else if($type == "slider"){
                return $this->wsf->trans("Slider");
            }else if($type == "text"){
                return $this->wsf->trans("Text");
            }else if($type == "date"){
                return $this->wsf->trans("wpc.date");
            }else if($type == "time"){
                return $this->wsf->trans("wpc.time");
            }else if($type == "datetime"){
                return $this->wsf->trans("wpc.datetime");
            }else if($type == "radio"){
                return $this->wsf->trans("wpc.radio");
            }

            return null;
    }
    
    /*
     * Ritorna una serie di campi utilizzando un'array di id
     */
    public function get_fields_by_ids($ids){
        $fields = array();

        if(!empty($ids)){
            foreach($ids as $id){
                $fields[] = $this->fieldModel->get_field_by_id($id);
            }
        }
        return $fields;
    }
        
    /*
     * Ritorna gli elementi da utilizzare in un menù a tendina
     */
    public function get_field_picklist_items($field){
        if(empty($field->options)){
            return array();
        }
        
        $options = json_decode($field->options, true);

        return json_decode($options['picklist_items'], true);
    }
        
    public function get_field_radio_items($field){
        if(empty($field->options)){
            return array();
        }
        
        $options        = json_decode($field->options, true);
        $ret_items      = array();
        
        if(isset($options['radio'])){
            $ret_items  = json_decode($options['radio']['radio_items'], true);
        }
        
        return $ret_items;
    }
    
    /* Controllo se la cancellazione del campo può generare problemi,
     * è utilizzato da un simulatore?
     */
    public function checkFieldUsage($id){

        $calculators        = $this->calculatorModel->get_list();
        $calculatorLabels   = array();
        $fieldIds           = array();
        
        foreach($calculators as $calculator){
            $calculatorFieldIds       = array();
            
            if($calculator->type == "simple"){
                $calculatorFieldIds       = json_decode($calculator->fields, true);
            }else if($calculator->type == "excel"){
                $fields         = json_decode($calculator->fields, true);

                foreach($fields['input'] as $coord => $fieldId){
                    $calculatorFieldIds[]     = $fieldId;
                }
            }
            
            $fieldIds   = array_merge($fieldIds, $calculatorFieldIds);
            
            if(in_array($id, $calculatorFieldIds)){
                $calculatorLabels[]       = $calculator->name;
            }
        }

        $fieldIds       = array_unique($fieldIds);

        if(in_array($id, $fieldIds)){
            $this->wsf->execute('index', 'index');
            
            $error          = $this->wsf->trans('wpc.field.delete.error') . 
                                "<br/><br/>";
            
            foreach($calculatorLabels as $calculatorLabel){
                $error      .= "- {$calculatorLabel}<br/>";
            }
            
            return $error;
        }
        
        return null;
    }
    
    public function getShortLabel($simulatorField){
        if(empty($simulatorField->short_label)){
            return $simulatorField->label;
        }
        
        return $simulatorField->short_label;
    }
    
   
    /* Ritorna il valore di default inserito nel campo */
    public function getFieldDefaultPriceValue($simulatorField){
        $options	= json_decode($simulatorField->options, true);

        if($simulatorField->type == 'checkbox'){
            if($options['checkbox']['default_status'] == 1){
                    return $options['checkbox']['check_value'];
            }else{
                    return $options['checkbox']['uncheck_value'];
            }
        }else if($simulatorField->type == 'numeric'){
            return $options['numeric']['default_value'];
        }else if($simulatorField->type == 'picklist'){
            
            $picklistItems = $this->get_field_picklist_items($simulatorField);
            
            foreach($picklistItems as $key => $item){
                    return $item['value'];
            }
            
        }else if($simulatorField->type == 'text'){
            return $options['text']['default_value'];
        }else if($simulatorField->type == 'radio'){
            
            $radioItems    = $this->get_field_radio_items($simulatorField);
            foreach($radioItems as $key => $item){
                    return $item['value'];
            }
            
        }

        return 0;
	}

}
