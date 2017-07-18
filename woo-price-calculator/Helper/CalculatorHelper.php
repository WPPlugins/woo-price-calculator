<?php

namespace WSF\Helper;

use WSF\WSF;

class CalculatorHelper {
    
    var $wsf;
    
    var $fieldHelper;
    var $wooCommerceHelper;
    
    public function __construct(WSF $wsf) {
        $this->wsf = $wsf;
        
        /* HELPERS */
        $this->fieldHelper = $this->wsf->get('\\WSF\\Helper', 'Helper', 'FieldHelper', array($this->wsf));
        $this->wooCommerceHelper = $this->wsf->get('\\WSF\\Helper', 'Helper', 'WooCommerceHelper', array($this->wsf));
        
        /* MODELS */
        $this->fieldModel = $this->wsf->get('\\WSF\\Model', 'Model', 'FieldModel', array($this->wsf));
        $this->calculatorModel = $this->wsf->get('\\WSF\\Model', 'Model', 'CalculatorModel', array($this->wsf));
    }
    
    /*
     * Calcolo del prezzo utilizzando le formule inserite nel simulatore
     */
    public function calculate_price($product_id, $data, $format_price = true, $simulator_id = null){
        $product        = new \WC_Product($product_id);
        $ret		= 0;
        $eos            = new \jlawrence\eos\Parser();

        if(empty($simulator_id)){
            $simulator = $this->get_simulator_for_product($product_id);
        }else{
            $simulator = $this->calculatorModel->get($simulator_id);
        }

        $simulator_fields_ids   = $this->get_simulator_fields($simulator->id);
        $fields                 = $this->fieldHelper->get_fields_by_ids($simulator_fields_ids);

        $vars                   = array('price' => $product->get_price());
        $formula                = $simulator->formula;
        
        foreach($fields as $field_key => $field_value){
            if(!empty($field_value)){
                $options    = json_decode($field_value->options, true);
                $fieldId    = $this->wsf->getPluginShortCode() . '_' . $field_value->id;
                $value      = $this->wsf->isset_or($data[$fieldId], 0);

                if($field_value->type == "checkbox"){
                    if($value === "on" || $value == 1){
                        $value = $options['checkbox']['check_value'];
                    }else{
                        $value = $options['checkbox']['uncheck_value'];
                    }
                }else if($field_value->type == "numeric"){
                    $value = str_replace($options['numeric']['decimal_separator'], ".", $value);
                    
                    //Se il campo è vuoto definisco 0
                    if(empty($value)){
                        $value  = 0;
                    }
                    
                }else if($field_value->type == "radio"){
                    $itemsData         = json_decode($options['radio']['radio_items'], true);
                    
                    foreach($itemsData as $index => $item){
                        if($item['id'] == $value){
                            $value  = $item['value'];
                            break;
                        }
                    }

                }else if($field_value->type == "picklist"){
                    $itemsData         = json_decode($options['picklist_items'], true);
                    
                    foreach($itemsData as $index => $item){
                        if($item['id'] == $value){
                            $value  = $item['value'];
                            break;
                        }
                    }
                }

                $vars[$this->wsf->getPluginShortCode() . '_' . $field_value->id] = $value;
            }
        }

        
        foreach($vars as $var_key => $var_value){
            if(empty($var_value)){
                $value              = 0;
            }else{
                $value = $var_value;
            }
            $formula = str_replace("\${$var_key}", (float)$value, $formula);
        }

        if($simulator->type == "simple" || empty($simulator->type)){
            $ret = $eos->solveIF($formula, $vars);
        }else if($simulator->type == "excel"){
        }
        
        
        if($format_price == true){
            return $this->wooCommerceHelper->get_price($ret);
        }else{
            return $ret;
        }
    }

    /*
     * Ritorna il simulatore utilizzato per un prodotto
     */
    public function get_simulator_for_product($product_id){
        $simulators = $this->calculatorModel->get_list();
        $terms      = get_the_terms($product_id, 'product_cat');
        $terms      = (empty($terms))?array():$terms;
        
        foreach($simulators as $simulator){
            
            $products               = json_decode($simulator->products, true);
            $productCategories      = json_decode($simulator->product_categories, true);
            
            /* Controllo se è stato selezionato quello specifico prodotto */
            if(!empty($products)){
                
                if(in_array($product_id, $products)){
                    return $simulator;
                }
                
        
            }
            
            /* Controllo se è stata selezionata una categoria che contiene il prodotto */
            if(!empty($productCategories)){

                foreach ($terms as $term) {
                    
                    if(in_array($term->term_id, $productCategories)){
                        return $simulator;
                    }

                    /* Controllo tutte le sottocategorie */
                    foreach($productCategories as $productCategoryId){
                        if(term_is_ancestor_of($term->term_id, $productCategoryId, 'product_cat') == true || 
                           term_is_ancestor_of($productCategoryId, $term->term_id, 'product_cat') == true){
                            return $simulator;
                        }
                        
                    }
                }
                
            }
        
        }

        return null;
    }
    
    /*
     * Ritorna i campi utilizzati da un simulatore
     */
    public function get_simulator_fields($simulator_id){
        $simulator = $this->calculatorModel->get($simulator_id);

        if($simulator->type == "simple" || empty($simulator->type)){
            return json_decode($simulator->fields, true);
        }else if($simulator->type == "excel"){
        }
    }
    
    /*
     * Ritorna i prodotti selezionati nel simulatore
     */
    public function get_simulator_products($simulator_id){
        $simulator = $this->calculatorModel->get($simulator_id);

        return json_decode($simulator->products);
    }
    
    /*
     * Ritorna i campi utilizzati da un simulatore
     */
    public function get_loader_simulator_cells($simulator_id){
        $simulator = $this->calculatorModel->get($simulator_id);

        $ret = array();

        $loader_fields = json_decode($simulator->options, true);
        //PMR: to supress a warning.
        if($loader_fields['input'] === NULL){
        	return $ret;
        }
        //
        foreach($loader_fields['input'] as $coordinates => $fieldId){
            $ret[$coordinates]      = $fieldId;
        }
        
        return $ret;
    }
    
    /*
     * Ritorna la lista dei temi nel formato:
     * array['filename'] = NOME TEMA
     */
    public function get_themes(){
        $themes_list = array();
        
        $themes = glob($this->wsf->getUploadPath('themes/*.php'));
        
        foreach($themes as $theme){
            $theme_file = file_get_contents($theme);
            preg_match("/THEME_NAME:(.*)/", $theme_file, $match_name);
            $name = trim($match_name[1]);

            $filename = str_replace($this->wsf->getUploadPath('themes/'), "", $theme);
            
            $themes_list[] = array(
                'path'      => $theme,
                'filename'  => $filename,
                'name'      => $name,
            );
        }
        
        return $themes_list;
    }
    
    /*
     * Permette di scaricare un foglio di calcolo caricato
     */
    public function downloadSpreadsheet($simulatorId){
        if (!current_user_can('manage_options')){
            die("WPC: Access denied!");
        }
        
        $calculator        = $this->calculatorModel->get($simulatorId);
        
        if($calculator->type == 'excel'){
            $calculatorOptions  = json_decode($calculator->options, true);
            $file               = $calculatorOptions['file'];
            $filename           = $calculatorOptions['filename'];
            $filePath           = $this->getSpreadsheetUploadPath($file);
            
            if(file_exists($filePath)){
                header('Content-Description: File Transfer');
                header('Content-Type: application/octet-stream');
                header("Content-Disposition: attachment; filename=\"{$filename}\"");
                header('Expires: 0');
                header('Cache-Control: must-revalidate');
                header('Pragma: public');
                header('Content-Length: ' . filesize($filePath));
                readfile($filePath);
                exit;
            }
        }
        
        die("WPC: Nothing to do");
    }
    
    /*
     * Ritorna il path del foglio di calcolo
     */
    public function getSpreadsheetUploadPath($file){
        return $this->wsf->getUploadPath("docs/{$file}");
    }
    
    /*
     * Controlla gli errori del simulatore
     */
    public function checkErrors($fields, $fieldValues = array()){
        
        $errors     = array();
        
        foreach($fields as $field_key => $field_value){
            $fieldId                            = "{$this->wsf->getPluginShortCode()}_{$field_value->id}";
            $options                            = json_decode($field_value->options, true);
            $value                              = $fieldValues[$fieldId];
            
            /* CONTROLLO DATI */
            if($field_value->type == "text"){
                if(!empty($options['text']['regex'])){
                    preg_match($options['text']['regex'], $value, $matches);
                    if(count($matches) == 0){
                        $errors[$fieldId][]       = $options['text']['regex_error'];
                    }
                }
            }else if($field_value->type == "numeric"){
                if(!empty($options['numeric']['max_value'])){
                    if($value > $options['numeric']['max_value']){
                        $errors[$fieldId][]       = $options['numeric']['max_value_error'];
                    }
                }

                if(!empty($options['numeric']['min_value'])){
                    if($value < $options['numeric']['min_value']){
                        $errors[$fieldId][]       = $options['numeric']['min_value_error'];
                    }
                }
            }
        }
        
        return $errors;
    }
    
    /*
     * Controlla che se il prezzo nei prodotti selezionati è nullo, visualizza
     * un messaggio di avvertimento che dice che per visualizzare il simulatore
     * è necessario inserire un prezzo
     */
    public function checkProductPrices($productIds){
        $warnings   = array();
        
        foreach($productIds as $productId){
            $product            = $this->wooCommerceHelper->get_woocommerce_product_by_id($productId);
            $price              = $product->get_regular_price();
            $title              = $product->get_title();
            
            if($price == ''){
                $warnings[]         = $this->wsf->trans("wpc.calculator.form.price.warning", array(
                    'productTitle'      => $title,
                ));
            }

        }

        return $warnings;
    }
    
    /*
     * Ritorna la lista dei campi ordinata per selezione da parte dell'utente
     */
    public function orderFields($selectedFields){
        $orderedFields      = array();
        $fields             = $this->fieldModel->get_field_list();
        
        foreach($selectedFields as $fieldId){
            $orderedFields[]        = $this->fieldModel->get_field_by_id($fieldId);
        }

        foreach($fields as $field){
            if(!in_array($field, $orderedFields)){
                $orderedFields[]    = $field;
            }
        }
        
        return $orderedFields;
    }
    
    /*
     * Controlla che due o più calcolatori non siano assegnati allo stesso prodotto
     */
    public function checkCalculatorDuplicate($record, $excludeId){
        $errors           = array();
        
        $check_simulators = $this->calculatorModel->get_list();
        
        /* Faccio la lista di tutti i simulatori utilizzati ad esclusione del corrente */
        foreach($check_simulators as $check_simulator){
            if($check_simulator->id != $excludeId){
                
                $productCategories  = json_decode($this->wsf->isset_or($check_simulator->product_categories, "{}"), true);
                $check_sim_products = json_decode($this->wsf->isset_or($check_simulator->products, "{}"), true);

                foreach($productCategories as $productCategoryId){
                    $check_sim_products = array_merge($check_sim_products, $this->wooCommerceHelper->getCategoryProductsByCategoryId($productCategoryId));
                }

                $check_sim_products     = array_unique($check_sim_products);
            }
        }

        /*
         * Controllo se fra i prodotti e le categorie selezionate del simulatore
         * ci sono simulatori utilizzati
         */
        if(!empty($check_sim_products)){

            $productCategories      = $record['product_categories'];
            $checkReqProducts       = $record['products'];

            foreach($productCategories as $productCategoryId){
                $checkReqProducts = array_merge($checkReqProducts, $this->wooCommerceHelper->getCategoryProductsByCategoryId($productCategoryId));
            }
            $checkReqProducts   = array_unique($checkReqProducts);

            if(!empty($checkReqProducts)){
                foreach($checkReqProducts as $check_req_product){
                    if(in_array($check_req_product, $check_sim_products)){
                        $check_product      = $this->wooCommerceHelper->get_woocommerce_product_by_id($check_req_product);
                        $checkProductTitle  = $check_product->get_title();
                        
                        $errors[]           = "- \"{$checkProductTitle}\" {$this->wsf->trans('used by')} \"{$check_simulator->name}\" {$this->wsf->trans("You must select only one simulator for product.")}";
                    }
                }
            }
        }
        
        return $errors;
    }
    
        
}
