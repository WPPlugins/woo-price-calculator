<?php

namespace WSF\Model;

use WSF\WSF;

class FieldModel {
    var $wsf;
    var $db;
    
    public function __construct(WSF $wsf){
        $this->wsf = $wsf;
        $this->db = $this->wsf->getDB();
    }
    /*
     * Ritorna tutta la lista dei campi
     */
    public function get_field_list(){
            return $this->db->get_results("SELECT * FROM " . 
                            $this->db->prefix . "woopricesim_fields");
    }

    /*
     * Ritorna un campo utilizzando l'ID
     */
    public function get_field_by_id($id){

        return $this->db->get_row(
                    $this->db->prepare(
                        "SELECT * FROM " . 
                        $this->db->prefix . "woopricesim_fields "
                        . "WHERE id = %d",
                        $id
                    )
                );
    }
    
    /*
     * Ritorna il numero di campi creati
     */
    public function getFieldCount(){
        return count($this->get_field_list());
    }
    
    public function save($data, $id = null){
        
        $record = array(
               "label"          => $data['label'],
               "short_label"    => $data['short_label'],
               "description"    => $data['description'],
               "type"           => $data['type'],
               "options"        => json_encode(array(
                    'items_list_id'     => $data['items_list_id'],
                   
                    'picklist_items'    => $data['picklist_items'],
                   
                    'checkbox' => array(
                        'check_value' => $data['checkbox_check_value'],
                        'uncheck_value' => $data['checkbox_uncheck_value'],
                        'default_status' => $data['checkbox_default_status'],
                    ),
                   'numeric' => array(
                       'default_value' => $data['numeric_default_value'],
                       'max_value' => $data['numeric_max_value'],
                       'max_value_error' => $data['numeric_max_value_error'],
                       'min_value' => $data['numeric_min_value'],
                       'min_value_error' => $data['numeric_min_value_error'],
                       'decimals' => $data['numeric_decimals'],
                       'decimal_separator' => $data['numeric_decimal_separator'],
                   ),
                   'text' => array(
                       'default_value' => $data['text_default_value'],
                       'regex'         => $data['text_regex'],
                       'regex_error'   => $data['text_regex_error'],
                   ),
                   'radio' => array(
                       'radio_items'       => $data['radio_items'],
                   ),
               )),
               "system_created"   => 0,
        );

        if(empty($id)){
            $this->db->insert($this->db->prefix . "woopricesim_fields", $record);
            return $this->db->insert_id;
        }else{
            $this->db->update($this->db->prefix . "woopricesim_fields", $record,
                            array(
                                'id' => $id
                                )
                        );
        }
    }
    
    public function delete($id){
        $this->db->delete($this->db->prefix . "woopricesim_fields", 
                array(
                       "id" => $id,
                ));
    }
}