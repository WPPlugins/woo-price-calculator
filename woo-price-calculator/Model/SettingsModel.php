<?php

namespace WSF\Model;

use WSF\WSF;

class SettingsModel {
    var $wsf;
    var $db;
    
    public function __construct(WSF $wsf){
        $this->wsf = $wsf;
        $this->db = $this->wsf->getDB();
    }

    /*
     * Ritorna un campo utilizzando la chiave
     */
    public function getValues(){
        $ret    = array();
        $rows   = $this->db->get_results("SELECT * FROM " . 
                        $this->db->prefix . "woopricesim_settings "
                );

        if(empty($rows)){
            $rows       = array();
        }
        
        foreach($rows as $row){
            $ret[$row->s_key]     = $row->s_value;
        }
        
        return $ret;
    }
    
    /*
     * Ritorna un campo utilizzando la chiave
     */
    public function getValue($key){

        $ret    = $this->db->get_row(
                    $this->db->prepare(
                        "SELECT * FROM " . 
                        $this->db->prefix . "woopricesim_settings "
                        . "WHERE s_key = %s",
                        $key
                    )
                );
        
        return $ret->s_value;
    }
    
    /*
     * Salva un valore nella tabella delle impostazioni
     */
    public function setValue($key, $value){
        $record = array(
            's_key'         => $key,
            's_value'       => $value,
        );
        
        if($this->getValue($key) == null){
            $this->db->insert($this->db->prefix . "woopricesim_settings", $record);
            return $this->db->insert_id;
        }else{
            $this->db->update($this->db->prefix . "woopricesim_settings", $record,
                array(
                    's_key' => $key
                )
            );
        }
    }
    
    /* Controlla se esiste una chiave */
    public function isValue($key){
        $rows   = $this->db->get_results(
                $this->db->prepare(
                        "SELECT * FROM " . 
                        $this->db->prefix . "woopricesim_settings "
                        . "WHERE s_key = %s",
                        $key
                    ));

        if(count($rows) == 0 || empty($rows)){
            return false;
        }
        
        return true;
    }
   
    
}