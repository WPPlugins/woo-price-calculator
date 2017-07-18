<?php

namespace WSF\Model;

use WSF\WSF;

class RegexModel {
    
    var $wsf;
    var $db;
    
    public function __construct(WSF $wsf){
        $this->wsf  = $wsf;
        $this->db   = $this->wsf->getDB();
    }
    
    /*
     * Ritorna un simulatore utilizzando l'ID
     */
    public function get($id){

        return $this->db->get_row(
                    $this->db->prepare(
                        "SELECT * FROM {$this->db->prefix}woopricesim_regex WHERE id = %d",
                        $id
                    )
                );
    }
    
    /*
     * Ritorna la lista di tutti i simulatori
     */
    public function get_list(){
        return $this->db->get_results("SELECT * FROM {$this->db->prefix}woopricesim_regex");
    }
    
    public function exchangeArray($object){
        return array(
            "name"              => $object->name,
            "regex"             => $object->regex,
            "user_created"      => $object->user_created,
        );
    }
    
    public function save($data, $id = null){
            $record = array(
               "name"           => $data['name'],
               "regex"          => $data['regex'],
               "user_created"   => 1,
            );
                        
            if(empty($id)){
                $this->db->insert("{$this->db->prefix}woopricesim_regex", $record);
                
                return $this->db->insert_id;
            }else{
                $this->db->update("{$this->db->prefix}woopricesim_regex", $record, array(
                    'id' => $id
                ));
            }
    }
    
    public function delete($id){
        $this->db->delete("{$this->db->prefix}woopricesim_regex", array("id" => $id));
    }
    
}
