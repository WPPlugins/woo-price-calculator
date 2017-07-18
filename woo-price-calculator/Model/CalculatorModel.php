<?php

namespace WSF\Model;

use WSF\WSF;

class CalculatorModel {
    
    var $wsf;
    var $db;
    
    public function __construct(WSF $wsf){
        $this->wsf = $wsf;
        $this->db = $this->wsf->getDB();
    }
    
    /*
     * Ritorna un simulatore utilizzando l'ID
     */
    public function get($id){

        return $this->db->get_row(
                    $this->db->prepare(
                        "SELECT * FROM " . 
                        $this->db->prefix . "woopricesim_simulators "
                        . "WHERE id = %d",
                        $id
                    )
                );
    }
    
    /*
     * Ritorna la lista di tutti i simulatori
     */
    public function get_list(){
        return $this->db->get_results("SELECT * FROM " . 
                        $this->db->prefix . "woopricesim_simulators");
    }
    
    public function exchangeArray($object){
        return array(
            "name"                  => $object->name,
            "description"           => $object->description,
            "fields"                => json_decode($object->fields, true),
            "products"              => json_decode($object->products, true),
            "product_categories"    => json_decode($object->product_categories, true),
            "options"               => json_decode($object->options, true),
            "formula"               => $object->formula,
            "redirect"              => $object->redirect,
            "empty_cart"            => $object->empty_cart,
            "type"                  => $object->type,
            "theme"                 => $object->theme,
            "system_created"        => $object->system_created,
        );
    }
    
    public function save($data, $id = null){
            $record = array(
               "name"                   => $data['name'],
               "description"            => $data['description'],
               "fields"                 => json_encode($data['fields']),
               "products"               => json_encode($data['products']),
               "product_categories"     => json_encode($data['product_categories']),
               "options"                => json_encode($data['options']),
               "formula"                => $data['formula'],
               "redirect"               => $data['redirect'],
               "empty_cart"             => $data['empty_cart'],
               "type"                   => $data['type'],
               "theme"                  => $data['theme'],
               "system_created"         => 0,
            );
                        
            if(empty($id)){
                $this->db->insert($this->db->prefix . "woopricesim_simulators", $record);
                return $this->db->insert_id;
            }else{
                $this->db->update($this->db->prefix . "woopricesim_simulators", $record,
                                array(
                                    'id' => $id
                                )
                );
                return $id;
            }
            
            return null;
    }
    
    public function delete($id){
        $this->db->delete($this->db->prefix . "woopricesim_simulators", array("id" => $id));
    }
    
}
