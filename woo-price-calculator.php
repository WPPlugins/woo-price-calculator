<?php
/*
Plugin Name: Woo Price Calculator
Plugin URI:  http://www.woopricecalculator.com
Description: Price Calculator for WooCommerce
Version:     1.2.25
Author:      AltosMail
Author URI:  http://www.altosmail.com
License:     
License URI: 
Domain Path: /lang
Text Domain: PoEdit
*/

/*
 * ATTENZIONE, Se si aggiorna Version, aggiornare anche la variabile $plugin_db_version
 * qui sotto per il database
 */

require_once( 'lib/eos/Stack.php' );
require_once( 'lib/eos/Parser.php' );


require_once('WSF/WSF.php');

        
class Woo_Price_Calculator {

	var $plugin_label           = "Woo Price Calculator";
	var $plugin_code            = "woo-price-calculator";
        var $plugin_dir             = "woo-price-calculator";
        var $plugin_short_code      = "woo_price_calc";
        var $plugin_db_version      = "1.2.25";

        var $view = array();
        
        var $wsf = null;
        var $db;
        
        var $fieldHelper;
        var $calculatorHelper;
        
        var $fieldModel;
        
	public function __construct(){
            
            global $wpdb;

            $this->wpdb = $wpdb;
            
            add_action( 'save_post', array($this, 'save_post'));
            
            add_action('wp_enqueue_scripts', array($this, 'wp_enqueue_scripts'));
            add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
            
            add_action('admin_menu', array( $this, 'register_submenu_page'),99);

            add_action('woocommerce_before_add_to_cart_button', array($this, 'product_meta_end'));

            add_filter('woocommerce_cart_item_price', array($this, 'filter_woocommerce_cart_item_price'), 1, 3);
            add_filter('woocommerce_cart_item_price_html', array($this, 'woocommerce_cart_item_price_html'), 1, 3);
            add_filter('woocommerce_cart_product_subtotal', array($this, 'woocommerce_cart_product_subtotal'), 10, 4 ); 
            
            add_action('woocommerce_before_calculate_totals', array($this, 'add_custom_price'), 10, 1);
            add_action('woocommerce_add_to_cart', array($this, 'add_to_cart_callback'), 10, 6);

            add_action( 'woocommerce_cart_item_removed', array($this, 'action_woocommerce_cart_item_removed'), 10, 2 );

            add_action( 'woocommerce_add_order_item_meta', array($this, 'action_woocommerce_add_order_item_meta'), 1, 3 );
            add_action('woocommerce_checkout_update_order_meta', array($this, 'action_woocommerce_checkout_update_order_meta'), 10, 2);
            add_action( 'woocommerce_checkout_order_processed', array($this, 'action_woocommerce_checkout_order_processed'), 10, 1 );
                    
            add_action( 'add_meta_boxes', array($this, 'order_add_meta_boxes'));
            
            add_action('wp_ajax_woopricesim_ajax_callback', array($this, 'woopricesim_ajax_callback'));
            add_action('wp_ajax_nopriv_woopricesim_ajax_callback', array($this, 'woopricesim_ajax_callback'));
            
            add_action('wp_ajax_woopricesim_upload_ajax_callback', array($this, 'woopricesim_upload_ajax_callback'));
            add_action('wp_ajax_woopricesim_download_ajax_callback', array($this, 'woopricesim_download_ajax_callback'));
            
            add_filter('woocommerce_add_to_cart_validation', array($this, 'filter_woocommerce_add_to_cart_validation'), 10, 3);
            add_filter('woocommerce_add_to_cart_redirect', array($this, 'filter_woocommerce_add_to_cart_redirect'));
            add_filter('woocommerce_get_price_html', array($this, 'filter_woocommerce_get_price_html'));
            add_filter('woocommerce_cart_item_name', array($this, 'filter_woocommerce_cart_item_name'), 20, 3);
            
            add_filter( 'woocommerce_loop_add_to_cart_link', array($this, 'woocommerce_loop_add_to_cart_link'), 10, 2 );
            
            add_action('plugins_loaded', array($this, 'action_plugins_loaded'));
            
            
           // add_filter('admin_footer_text', array($this, 'filter_admin_footer_text'));
            
            $this->wsf = new WSF\WSF($this->plugin_dir);
            $this->db  = $this->wsf->getDB();
            
            $this->calculatorHelper = $this->wsf->get('\\WSF\\Helper', 'Helper', 'CalculatorHelper', array($this->wsf));
            $this->fieldHelper = $this->wsf->get('\\WSF\\Helper', 'Helper', 'FieldHelper', array($this->wsf));
            
            $this->fieldModel       = $this->wsf->get('\\WSF\\Model', 'Model', 'FieldModel', array($this->wsf));
            $this->calculatorModel  = $this->wsf->get('\\WSF\\Model', 'Model', 'CalculatorModel', array($this->wsf));
            $this->settingsModel    = $this->wsf->get('\\WSF\\Model', 'Model', 'SettingsModel', array($this->wsf));
            
            /* Meglio lasciarlo sempre per ultimo affinchè siano istanziati gli oggetti */
            $this->pluginUpgrade();
	}

        /*
         * Eseguita al salvataggio di un post
         */
        function save_post($postId) {
            $post       = get_post($postId);

            if($post->post_type == "product"){
                /* Controllo duplicato Calcolatori, visualizzare errore */
            }
    
        }
               
        /*
         * Cambia la visualizzazione del pulsante Add to cart presente nell'archivio
         */
        function woocommerce_loop_add_to_cart_link($link, $product){
            $calculator  = $this->calculatorHelper->get_simulator_for_product($product->get_id());
            
            if(!empty($calculator)){
                $link = sprintf( '<a href="%s" rel="nofollow" data-product_id="%s" data-product_sku="%s" data-quantity="%s" class="button product_type_%s">%s</a>',
                    esc_url( get_permalink($product->get_id())),
                    esc_attr( $product->get_id()),
                    esc_attr( $product->get_sku()),
                    esc_attr( isset( $quantity ) ? $quantity : 1),
                    esc_attr($product->get_type()),
                    esc_html(__( 'Choose an option', 'woocommerce'))
                );
            }
            
            return $link;
        }

        function admin_enqueue_scripts($hookSuffix){
            
            /*
             * Carico gli stili e script solo nelle pagine del plugin
             * 
             * Il suffix hook da cercare sarebbe "woocommerce_page_woo-price-calculator",
             * ma in alcuni sistemi con altre lingue (es: Ebreo), la prima parte (woocommerce), potrebbe essere diversa
             */
            if(strpos($hookSuffix, '_page_woo-price-calculator') !== false){
                /* Questo bootstrap ha un prefisso per non modificare l'aspetto di altre cose */
                wp_enqueue_script('wsf-bootstrap', plugins_url($this->plugin_dir  . '/lib/wsf-bootstrap-3.3.7/js/bootstrap.js'), array('jquery'), '3.3.7');
                wp_enqueue_style('wsf-bootstrap', plugins_url($this->plugin_dir  . '/lib/wsf-bootstrap-3.3.7/css/wsf-bootstrap.css'));
                wp_enqueue_style('wsf-bootstrap', plugins_url($this->plugin_dir  . '/lib/wsf-bootstrap-3.3.7/css/wsf-bootstrap-theme.css'));

                wp_enqueue_style('tooltipstercss', plugins_url($this->plugin_dir  . '/css/tooltipster.css'));
                wp_enqueue_style('tooltipster-shadow', plugins_url($this->plugin_dir . '/css/tooltipster-shadow.css'));
                wp_enqueue_script('tooltipster', plugins_url($this->plugin_dir . '/js/jquery.tooltipster.min.js'), array('jquery'), '3.2.6');

                wp_enqueue_script($this->plugin_code . '-admin', plugins_url($this->plugin_dir . '/js/admin.js'), array('jquery', 'jquery-ui-tooltip', 'tooltipster'),'1.0.1');
                wp_enqueue_script($this->plugin_code . '-jquery-numeric', plugins_url($this->plugin_dir . '/js/jquery.numeric.min.js'), array('jquery')); 
                wp_enqueue_script($this->plugin_code . '-jquery-tooltipster', plugins_url($this->plugin_dir . '/js/jquery.tooltipster.min.js'), array('jquery')); 

                wp_enqueue_style($this->plugin_code . '-admin-style', plugins_url($this->plugin_dir  . '/css/admin.css'));

                wp_enqueue_script('uploadify-css', plugins_url($this->plugin_dir . '/lib/uploadify/jquery.uploadify.js'), array('jquery'),'1.7.1');
                wp_enqueue_style('uploadify', plugins_url($this->plugin_dir  . '/lib/uploadify/uploadify.css'));

                wp_enqueue_script('lou-multi-select', plugins_url($this->plugin_dir  . '/lib/lou-multi-select-0.9.12/js/jquery.multi-select.js'), array('jquery'), '0.9.12');
                wp_enqueue_style('lou-multi-select', plugins_url($this->plugin_dir  . '/lib/lou-multi-select-0.9.12/css/multi-select.css'));

                wp_enqueue_script('datetimepicker', plugins_url($this->plugin_dir  . '/lib/datetimepicker-2.5.4/jquery.datetimepicker.js'), array('jquery'), '2.5.4');
                wp_enqueue_style('datetimepicker', plugins_url($this->plugin_dir  . '/lib/datetimepicker-2.5.4/jquery.datetimepicker.css'));

                wp_enqueue_style('dataTables-bootstrap', plugins_url($this->plugin_dir  . '/lib/DataTables-1.10.12/media/css/dataTables.bootstrap.min.css'));
                wp_enqueue_script('dataTables', plugins_url($this->plugin_dir  . '/lib/DataTables-1.10.12/media/js/jquery.dataTables.min.js'), array('jquery'), '1.10.12');
                wp_enqueue_script('dataTables-bootstrap', plugins_url($this->plugin_dir  . '/lib/DataTables-1.10.12/media/js/dataTables.bootstrap.min.js'), array('jquery'), '1.10.12');

                wp_enqueue_style('font-awesome', plugins_url($this->plugin_dir  . '/lib/font-awesome-4.6.3/css/font-awesome.min.css'));

                wp_enqueue_script('Sortable', plugins_url($this->plugin_dir  . '/lib/Sortable-1.4.2/Sortable.min.js'), array('jquery'), '1.10.12');

                wp_enqueue_script('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js', array('jquery'), '1.12.4');
            }
            
            /* Eseguito sempre per ultimo */
            wp_localize_script($this->plugin_code . '-admin', 'WPC_HANDLE_SCRIPT', array( 
                'siteurl' => get_option('siteurl'),
            ));
        }
        
        function wp_enqueue_scripts(){

            if(is_product()){
                $simulator = $this->calculatorHelper->get_simulator_for_product(get_the_ID());
            }
            
            wp_enqueue_script($this->plugin_code . '-jquery-numeric', plugins_url($this->plugin_dir . '/js/jquery.numeric.min.js'), array('jquery')); 
            
            wp_enqueue_script($this->plugin_code . '-datetimepicker', plugins_url($this->plugin_dir  . '/lib/datetimepicker-2.5.4/build/jquery.datetimepicker.full.js'), array('jquery'), '2.5.4');
            wp_enqueue_style($this->plugin_code . '-datetimepicker', plugins_url($this->plugin_dir  . '/lib/datetimepicker-2.5.4/jquery.datetimepicker.css'));
                
            wp_enqueue_style('woocommerce-pricesimulator-main', plugins_url($this->plugin_dir . '/css/main.css'));
            
            /*
             * array('jquery', 'woocommerce'): Ma se ricaricato due volte "woocommerce", genera errori su altre librerie
             * Inoltre sembra che in main.js non sia utilizzata nessuna funzione woocommerce. In ogni caso lascio questo
             * commento per eventuali problemi futuri, ma se non si denunciano problemi può essere cancellato il commento
             */
            wp_enqueue_script($this->plugin_code . '-main', plugins_url($this->plugin_dir . '/js/main.js'), array('jquery'));
            
            wp_enqueue_style('remodal', plugins_url($this->plugin_dir . '/lib/remodal-1.0.7/src/remodal.css'));
            wp_enqueue_style('remodal-default-theme', plugins_url($this->plugin_dir . '/lib/remodal-1.0.7/src/remodal-wpc-theme.css'));
            wp_enqueue_script('remodal', plugins_url($this->plugin_dir  . '/lib/remodal-1.0.7/src/remodal.js'), array('jquery'), '2.5.4');
            
            /* Solo se è presente il simulatore */
            if(!empty($simulator)){
                wp_enqueue_script($this->plugin_code . '-jquery-tooltipster', plugins_url($this->plugin_dir . '/js/jquery.tooltipster.min.js'), array('jquery'));

                wp_enqueue_style('wsf-bootstrap', plugins_url($this->plugin_dir  . '/lib/wsf-bootstrap-3.3.7/css/wsf-bootstrap.css'));
                wp_enqueue_style('wsf-bootstrap', plugins_url($this->plugin_dir  . '/lib/wsf-bootstrap-3.3.7/css/wsf-bootstrap-theme.css'));
                
                wp_enqueue_style('wsf-uikit', plugins_url($this->plugin_dir  . '/lib/wsf-uikit-2.27.1/src/less/wsf-uikit.css'));
                
            }
            
            wp_enqueue_style('woocommerce-pricesimulator-custom', $this->wsf->getUploadUrl('style/custom.css'));
                            
            /* Eseguito sempre per ultimo */
            wp_localize_script($this->plugin_code . '-main', 'WPC_HANDLE_SCRIPT', array( 
                'siteurl' => get_option('siteurl'),
                'is_cart' => (is_cart() == true)?1:0, 
            ));
        }
        
        /*
         * Modifica il prezzo nella pagina del prodotto e nelle pagine dello shop
         * 
         * Visualizzo il prezzo all'inizio, prendo i valori di default per calcolare il prezzo di partenza
         */
        function filter_woocommerce_get_price_html($price){
            
            $productId	= get_the_ID();
            $product    = new WC_Product($productId);
            $simulator  = $this->calculatorHelper->get_simulator_for_product($productId);

            if(!empty($simulator)){
                $simulatorFieldsIds                     = $this->calculatorHelper->get_simulator_fields($simulator->id);
                $simulatorFields                        = $this->fieldHelper->get_fields_by_ids($simulatorFieldsIds);
                $fieldsData				= array();

                foreach($simulatorFields as $fieldKey => $field){
                    if(!empty($field)){
                        $fieldId    			= $this->wsf->getPluginShortCode() . '_' . $field->id;
                        $defaultValue			= $this->fieldHelper->getFieldDefaultPriceValue($field);

                        $fieldsData[$fieldId]	= $defaultValue;
                    }
                }

                try{
                    $price		= $this->calculatorHelper->calculate_price($productId, $fieldsData, true, $simulator->id);
                } catch (\Exception $ex) {
                    $price              = "{$this->wsf->trans('wpc.calculate_price.error')}: {$ex->getMessage()}";
                }
               
                 return "<span class=\"woocommerce-Price-amount amount\">{$price}</span>";
            }
            return $price;
        }
        
        /*
         * Modifica il nome del prodotto nella pagina del carrello
         */
        function filter_woocommerce_cart_item_name($productTitle, $cartItem, $cartItemKey){
            
            
            return $productTitle;
        }
        
        /*
         * Eseguito in review-order.php per rivedere l'ordine
         */
        
        /*
         * Eseguito dopo l'acquisto, nel dettaglio dell'ordine
         */
        
        /*
         * Ritorna l'elemento per il review dell'ordine
         */
        public function getReviewElement($field, $value){
            if($field->type == "checkbox"){
                $tickImageUrl       = $this->wsf->getImageUrl("tick.png");
                $crossImageUrl      = $this->wsf->getImageUrl("cross.png");
                
                if($value === "on"){
                    return "<img src=\"{$tickImageUrl}\" />";
                }else{
                    return "<img src=\"{$crossImageUrl}\" />";
                }
            }else if($field->type == "picklist"){
                $picklistItems = $this->fieldHelper->get_field_picklist_items($field);
                
                foreach($picklistItems as $index => $item){
                    if($value == $item['id']){
                        return $this->wsf->userTrans($item['label']);
                    }
                }
            }else if($field->type == "radio"){
                $radioItems   = $this->fieldHelper->get_field_radio_items($field);
                
                foreach($radioItems as $index => $item){
                    if($value == $item['id']){
                        return $this->wsf->userTrans($item['label']);
                    }
                }
            }else{
                return $value;
            }
        }
        
        function filter_admin_footer_text () {
            echo "";
        } 

        /*
         * Dopo che è stato aggiunto un prodotto reindirizza direttamente
         * al checkout
         */
        function filter_woocommerce_add_to_cart_redirect() {

            global $woocommerce;
            
            $product_id = $this->wsf->requestValue('add-to-cart');
            if(!empty($product_id)){
                $simulator = $this->calculatorHelper->get_simulator_for_product($product_id);

                if(!empty($simulator)){
                    if($simulator->redirect == 1){
                        return $woocommerce->cart->get_checkout_url();
                    }
                }
            }

        } 

        /*
         * Attivazione dell'internazionalizzazione
         */
        function action_plugins_loaded() {
            load_plugin_textdomain($this->plugin_code, false, dirname( plugin_basename(__FILE__) ) . '/lang' );
        }
        
        /*
         * Installazione: Esecuzione Upgrade, viene sempre eseguita
         */
        function pluginUpgrade() {
                global $wpdb;

                require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
                
                $charset_collate        = $wpdb->get_charset_collate();
                $oldVersion             = get_option('woopricesim_db_version');
                $fullPath               = get_home_path();
                
                /* ATTENZIONE: Per la funzione dbDelta non bisogna utilizzare
                 * gli ALTER TABLE, ma inserire nel CREATE TABLE
                 */
                
                /* woopricesim_fields */
                $sql = "CREATE TABLE " . $wpdb->prefix .  "woopricesim_fields (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `label` text,
                        `short_label` TEXT DEFAULT NULL,
                        `description` TEXT DEFAULT NULL,
                        `type` varchar(100) DEFAULT NULL,
                        `validator` blob,
                        `options` blob,
                        `system_created` TINYINT(1) NOT NULL,
                        PRIMARY KEY (`id`)
                      )" . $charset_collate . ";";
                dbDelta($sql);

                /* woopricesim_simulations */
                $sql = "CREATE TABLE " . $wpdb->prefix . "woopricesim_simulations (
                  `id` int(11) NOT NULL AUTO_INCREMENT,
                  `order_id` int(11) DEFAULT NULL,
                  `simulation_data` blob,
                  `simulators` blob,
                  PRIMARY KEY (`id`)
                )" . $charset_collate . ";";
                dbDelta($sql);
    
                /* woopricesim_simulators */
                $sql = "CREATE TABLE " . $wpdb->prefix . "woopricesim_simulators (
                        `id` int(11) NOT NULL AUTO_INCREMENT,
                        `name` varchar(255) DEFAULT NULL,
                        `description` text,
                        `fields` blob,
                        `products` blob,
                        `product_categories` blob,
                        `options` blob,
                        `formula` text,
			`redirect` TINYINT(1),
                        `empty_cart` TINYINT(1) NULL DEFAULT 0,
                        `type` VARCHAR(50) NULL DEFAULT 'simple',
                        `theme` VARCHAR(255) NULL,
                        `system_created` TINYINT(1) NOT NULL,
                        PRIMARY KEY (`id`)
                      )" . $charset_collate . ";";
                dbDelta($sql);
                
                /* woopricesim_regex */
                $sql = "CREATE TABLE {$wpdb->prefix}woopricesim_regex (
                        `id` INT NOT NULL AUTO_INCREMENT,
                        `name` VARCHAR(255) NOT NULL,
                        `regex` MEDIUMTEXT NOT NULL,
                        `user_created` TINYINT(1) NOT NULL,
                        PRIMARY KEY (`id`)
                        ){$charset_collate};";
                dbDelta($sql);

                /* woopricesim_settings */
                $sql = "CREATE TABLE {$wpdb->prefix}woopricesim_settings (
                        `s_key` VARCHAR(100) NOT NULL,
                        `s_value` MEDIUMTEXT NOT NULL,
                        PRIMARY KEY (`s_key`)
                        ){$charset_collate};";
                dbDelta($sql);
                
                if($this->plugin_db_version == "1.1"){
                    $wpdb->query("INSERT INTO {$wpdb->prefix}woopricesim_regex SET "
                                . "{$wpdb->prefix}woopricesim_regex.id = 1, "
                                . "{$wpdb->prefix}woopricesim_regex.name = 'Email Check', "
                                . "{$wpdb->prefix}woopricesim_regex.regex = '/^(([^<>()\\\\[\\\\]\\\\\\\\.,;:\\\\s@\\\"]+(\\\\.[^<>()\\\\[\\\\]\\\\\\\\.,;:\\\\s@\\\"]+)*)|(\\\".+\\\"))@((\\\\[[0-9]{1,3}\\\\.[0-9]{1,3}\\\\.[0-9]{1,3}\\\\.[0-9]{1,3}])|(([a-zA-Z\\\\-0-9]+\\\\.)+[a-zA-Z]{2,}))$/', "
                                . "{$wpdb->prefix}woopricesim_regex.user_created = 0 "
                                . "ON DUPLICATE KEY UPDATE "
                                . "{$wpdb->prefix}woopricesim_regex.name = 'Email Check', "
                                . "{$wpdb->prefix}woopricesim_regex.regex = '/^(([^<>()\\\\[\\\\]\\\\\\\\.,;:\\\\s@\\\"]+(\\\\.[^<>()\\\\[\\\\]\\\\\\\\.,;:\\\\s@\\\"]+)*)|(\\\".+\\\"))@((\\\\[[0-9]{1,3}\\\\.[0-9]{1,3}\\\\.[0-9]{1,3}\\\\.[0-9]{1,3}])|(([a-zA-Z\\\\-0-9]+\\\\.)+[a-zA-Z]{2,}))$/', "
                                . "{$wpdb->prefix}woopricesim_regex.user_created = 0 "
                                );
                }else if($oldVersion == "1.0" || 
                         $oldVersion == "1.1" || 
                         (version_compare($oldVersion, "1.1.0", ">=") == true && version_compare($oldVersion, "1.2.8", "<") == true)){
                    /*
                     * Migrazione dei dati dei menù a tendina/Radio
                     */
                    $rows   = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}woopricesim_fields "
                    . "WHERE {$wpdb->prefix}woopricesim_fields.type = 'radio' || {$wpdb->prefix}woopricesim_fields.type = 'picklist'", ARRAY_A);
                    
                    foreach($rows as $row){
                        $options                = json_decode($row['options'], true);
                        
                        if($row['type'] == 'radio'){
                            $items                  = str_replace("\r", "", $options['radio']['radio_items']);
                        }else if($row['type'] == 'picklist'){
                            $items                  = str_replace("\r", "", $options['picklist_items']);
                        }
                        

                        if(substr($items, 0, strlen("\"[{")) === "\"[{" || substr($items, 0, strlen("[{")) === "[{"){
                            //Non faccio nulla
                        }else{
                            $explodedItems          = explode("\n", $items);
                            $id                     = 1;
                            $arrayItems             = array();
                            
                            foreach($explodedItems as $explodedItem){
                                $explodedItemValues     = explode("#$#", $explodedItem);

                                if(count($explodedItemValues) == 2){
                                    $arrayItems[]           = array(
                                        'id'        => $id++,
                                        'label'     => $explodedItemValues[1],
                                        'value'     => $explodedItemValues[0],
                                    );
                                }
                            }
                            
                            if($row['type'] == "radio"){
                                $options['radio']['radio_items']        = json_encode($arrayItems);
                            }else if($row['type'] == "picklist"){
                                $options['picklist_items']              = json_encode($arrayItems);
                            }
                            
                            $row['options']                         = json_encode($options);
                            
                            $wpdb->update("{$wpdb->prefix}woopricesim_fields", $row,
                                array('id' => $row['id'])
                            );

                        }
                    }
                    
                    
                    $wpdb->query("UPDATE {$wpdb->prefix}woopricesim_simulators SET "
                                . "{$wpdb->prefix}woopricesim_simulators.options = {$wpdb->prefix}woopricesim_simulators.fields "
                                . "WHERE {$wpdb->prefix}woopricesim_simulators.type = 'excel';");
                                
                    $wpdb->query("UPDATE {$wpdb->prefix}woopricesim_simulators SET "
                                . "{$wpdb->prefix}woopricesim_simulators.fields = NULL "
                                . "WHERE {$wpdb->prefix}woopricesim_simulators.type = 'excel';");
                }

                /*
                 * Creazione delle cartelle per Upload
                 */

                wp_mkdir_p("{$fullPath}wp-content/uploads/woo-price-calculator/docs");
                wp_mkdir_p("{$fullPath}wp-content/uploads/woo-price-calculator/themes");
                wp_mkdir_p("{$fullPath}wp-content/uploads/woo-price-calculator/translations");
                wp_mkdir_p("{$fullPath}wp-content/uploads/woo-price-calculator/style");
                
                $customCssPath  = "{$fullPath}wp-content/uploads/woo-price-calculator/style/custom.css";
                
                if(!file_exists($customCssPath)){
                    file_put_contents($customCssPath, "/* YOUR CUSTOM CSS */");
                }
                
                /* Chiavi di configurazione */
                if($this->settingsModel->isValue("cart_edit_button_class") == false){
                    $this->settingsModel->setValue("cart_edit_button_class", "button");
                }
                    
                update_option('woopricesim_db_version', $this->plugin_db_version);

        }

        /*
         * Validazione dei campi del simulatore all'aggiunta del prodotto
         * nel carrello
         */
        function filter_woocommerce_add_to_cart_validation( $bool, $product_id, $quantity) {
            global $woocommerce;
            
            $product = new \WC_Product($product_id);
            $simulator = $this->calculatorHelper->get_simulator_for_product($product_id);

            if(!empty($simulator)){
                $simulator_fields_ids = $this->calculatorHelper->get_simulator_fields($simulator->id);

                $fields         = $this->fieldHelper->get_fields_by_ids($simulator_fields_ids);
                $fieldsData     = array();

                foreach($fields as $field_key => $field_value){
                    $fieldRequestKey                    = $this->plugin_short_code . '_' . $field_value->id;
                    $options                            = json_decode($field_value->options, true);
                    $value                              = $this->wsf->requestValue($fieldRequestKey);
                    
                    /* AGGIUSTO I VALORI */
                    $fieldsData[$fieldRequestKey] = $value;
                }

                $errors     = $this->calculatorHelper->checkErrors($fields, $fieldsData);
                
                if(count($errors) != 0){
                    foreach($errors as $fieldId => $fieldErrors){
                        foreach($fieldErrors as $errorMessage){
                            wc_add_notice($errorMessage, "error");
                        } 
                    }
                    
                    return false;
                }

                /*
                 * Svuota il carello prima di ogni aggiunta di prodotto (Se l'opzione è attiva)
                 */
                if($simulator->empty_cart == 1){
                    WC()->cart->empty_cart();
                }
                
                /*
                 * Aggiungo il prodotto. Il prodotto inserito di default da WC
                 * sarà cancellato nella funzione add_to_cart_callback
                 */
                WC()->cart->add_to_cart($product_id, $quantity, 0, array(), array(
                    'simulator_id'              => $simulator->id,
                    'simulator_fields_data'     => $fieldsData,
                ));
            }
            
            return true;
        }
        

        /*
         * Aggiungo ulteriori informazioni nell'ordine che mi saranno utili in futuro.
         * 
         * Potrei anche utilizzare direttamente questo metodo per salvare i dati
         * della tabella "woopricesim_simulations" nell'ordine.
         */
        function action_woocommerce_add_order_item_meta($item_id, $values, $cart_item_key){
        	
        	
            if(isset($values['simulator_id'])) {
            	wc_add_order_item_meta($item_id, "_wpc_cart_item_key", $cart_item_key);
            }
        }
        
        
        /*
         * Eseguito prima di effettuare il checkout
         * 
         * E' possibile prendere le informazioni inserite dall'utente in fase 
         * di checkout
         */
        function action_woocommerce_checkout_update_order_meta($order_id, $values){
        }
        
        /*
         * Salvataggio della simulazione nel database, quando l'utente proccede
         * all'ordine
         */
        function action_woocommerce_checkout_order_processed($order_id){
            $orderData                  = array();
            $simulatorsDataBackup       = array();
            $foundSimulators            = false;
            foreach (WC()->cart->get_cart() as $cart_item_key => $values){
                if(isset($values['simulator_id'])){
                    $foundSimulators                = true;
                    $simulatorId                    = $values['simulator_id'];
                    
                    $orderData[$cart_item_key]      = $values;
                    
                    if(!array_key_exists($simulatorId, $simulatorsDataBackup)){
                        $simulatorsDataBackup[$simulatorId]     = $this->calculatorModel->get($simulatorId);
                    }
                }
            }

            if($foundSimulators === true){
                $this->wpdb->insert($this->wpdb->prefix . "woopricesim_simulations", array(
                   "order_id"           => $order_id,
                   "simulation_data"    => json_encode($orderData),
                   "simulators"         => json_encode($simulatorsDataBackup),
                ));
            }
        }
    
        /*
         * Aggiunta di un blocco negli Ordini
         */
        public function order_add_meta_boxes(){
            add_meta_box( 
                'woocommerce-order-my-custom', 
                $this->wsf->trans('Price Simulator: Order Simulation'), 
                array($this,'order_simulation'), 
                'shop_order', 
                'normal', 
                'default' 
            );
        }
        
        public function getSimulationByOrderId($orderId){
            return $this->wpdb->get_row(
                $this->wpdb->prepare(
                    "SELECT * FROM " . 
                    $this->wpdb->prefix . "woopricesim_simulations "
                    . "WHERE order_id = %d",
                    $orderId
                )
            );
        }
        
        /*
         * Visualizzazione di tutte le simulazioni per quell'ordine
         */
        public function order_simulation($order){
                $simulation         = $this->getSimulationByOrderId($order->ID);
                
                if(count($simulation) == 0){
                    echo "No simulations";
                }else{
                    $simulation_data = json_decode($simulation->simulation_data, true);
                    $simulators      = json_decode($simulation->simulators, true);

                    foreach($simulation_data as $cart_item_key => $orderItem){
                        
                        $product_id         = $orderItem['product_id'];
                        $simulatorId        = $orderItem['simulator_id'];
                        $calculator         = $this->calculatorModel->get($simulatorId);
                        $calculatorType     = $this->wsf->isset_or($calculator->type, "simple");
                        $calculatorFields   = json_decode($this->wsf->isset_or($calculator->fields, "{}"), true);
                        $product_simulator  = $simulators[$simulatorId];
                        $obj_product        = new \WC_Product($product_id);
                        $productTitle       = $obj_product->get_title();
                        $simpleFormula      = $product_simulator['formula'];
                        $quantity           = $orderItem['quantity'];
                        
                        echo "<b>{$quantity} x {$productTitle}:</b><br/>";
                        
                        if($calculatorType == 'simple'){
                            echo "<b>{$this->wsf->trans('Formula')}: {$simpleFormula}</b><br/>";
                        }else{
                            $calculatorOptions          = json_decode($this->wsf->isset_or($calculator->options, array()), true);
                            $downloadSpreadsheetUrl     = admin_url("admin-ajax.php?action=woopricesim_download_ajax_callback&simulator_id={$simulatorId}");
                            
                            echo "<b>{$this->wsf->trans('wpc.order.spreadsheet')}: "
                                    . "<a target=\"_blank\" href=\"{$downloadSpreadsheetUrl}\">"
                                        . "{$calculatorOptions['filename']}"
                                    . "</a>"
                            . "</b><br/>";
                        }
                        

                        foreach($orderItem['simulator_fields_data'] as $field_key => $field_value){

                            $field_id = str_replace($this->plugin_short_code . "_", "", $field_key);
                            $field = $this->fieldModel->get_field_by_id($field_id);

                            if(!empty($field_value)){
                                if(empty($field->label)){
                                    $label = "[FIELD DELETED]";
                                }else{
                                    $label = $field->label;
                                }
                                
                                $htmlElement      = $this->getReviewElement($field, $field_value);

                                echo "&emsp;&emsp;&emsp;&emsp;{$label} [{$field_key}]: {$htmlElement}<br/>";
                            }
                        }
                        
                        
                        echo "<br/>";
                        

                    }
                }

        }
        
        /*
         * Eseguito alla rimozione di un prodotto dal carrello
         */
        function action_woocommerce_cart_item_removed($cart_item_key, $instance){

        }

        /*
         * Eseguito per gli elementi nel carrello
         */
        function filter_woocommerce_cart_item_price($product_name, $values, $cart_item_key){
            global $woocommerce;
            $product = new WC_Product($values['product_id']);

            $cartItem   = $woocommerce->cart->get_cart_item($cart_item_key);
            
            if(isset($cartItem['simulator_id'])){
                $calculatorId   = $cartItem['simulator_id'];
                $fieldsData     = $cartItem['simulator_fields_data'];

                
                    $price                  = $this->calculatorHelper->calculate_price($values['product_id'], $fieldsData);
                    $calculator             = $this->calculatorModel->get($calculatorId);
                    $simulatorFieldsIds     = $this->calculatorHelper->get_simulator_fields($calculator->id);
                    $simulatorFields        = $this->fieldHelper->get_fields_by_ids($simulatorFieldsIds);
                    
                    /* Non faccio vedere il tasto modifica nel carrello dropdown */
                    if(is_cart() == true){
                        return $this->wsf->getView('cart/edit.php', array(
                            'product'               => $product,
                            'cartItemKey'           => $cart_item_key,
                            'price'                 => $price,
                            'cartEditButtonClass'   => $this->settingsModel->getValue("cart_edit_button_class"),
                            'modal'             =>  $this->wsf->getView('product/product.php', array(
                                'product'               => $product,
                                'simulator'             => $calculator,
                                'data'                  => $this->getDefaultThemeData($simulatorFields, $fieldsData),
                            )) . $this->wsf->getView("product/footer_data.php", array(
                                'product'               => $product,
                                'simulator'             => $calculator,
                                'data'                  => $this->getDefaultThemeData($simulatorFields, $fieldsData),
                            )),
                        ));
                    }else{
                        return $price;
                    }

            }
            
            return $product_name;
        }

        /*
         * Eseguito per gli elementi nel carrello (Versione HTML)
         * 
         * Questo prezzo viene anche visualizzato nel carrello dropdown
         */
        function woocommerce_cart_item_price_html($cart_price, $values, $cart_item_key){
            global $woocommerce;
            $product = new WC_Product($values['product_id']);

            $cartItem   = $woocommerce->cart->get_cart_item($cart_item_key);
            
            if(isset($cartItem['simulator_id'])){
                $calculatorId   		= $cartItem['simulator_id'];
                $fieldsData     		= $cartItem['simulator_fields_data'];
                $price                  = $this->calculatorHelper->calculate_price($values['product_id'], $fieldsData);
                $calculator             = $this->calculatorModel->get($calculatorId);
                $simulatorFieldsIds     = $this->calculatorHelper->get_simulator_fields($calculator->id);
                $simulatorFields        = $this->fieldHelper->get_fields_by_ids($simulatorFieldsIds);
                    
                return $price;

            }
            
            return $cart_price;
        }
        
        /*
         * Eseguito nella visualizzazione del sotto totale del prodotto nel carrello
         */
        function woocommerce_cart_product_subtotal($product_subtotal, $product, $quantity, $cart_object){
            
            $this->updateCartByCartObject($cart_object);
            
            return $product_subtotal; 
        }
        
        /*
         * Eseguito all'aggiunta di un prodotto nel carrello
         * 
         * |Imposto la quantità sul carrello|
         * $woocommerce->cart->set_quantity($cart_item_key, 100, true);
         * 
         * |Ricalcola i totali del carrello|
         * $woocommerce->cart->calculate_totals();
         */
	public function add_to_cart_callback($cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data){
                global $woocommerce;

                $simulator = $this->calculatorHelper->get_simulator_for_product($product_id);
                
                if(!empty($simulator)){
                    
                    /*
                     * Rimuovo il prodotto inserito da WC. Con questo trucco,
                     * riesco ad inserire prodotti con dati diversi nel carrello,
                     * riuscendo a separarli per ogni riga.
                     */
                    foreach(WC()->cart->get_cart() as $cart_item_key => $values ){
                        $cartProductId  = $values['product_id'];
                        $cartSimulator  = $this->calculatorHelper->get_simulator_for_product($cartProductId);
                        
                        /* Controllo che il prodotto nel carrello sia associato ad un simulatore */
                        if(empty($values['simulator_id']) && !empty($cartSimulator)){
                            $woocommerce->cart->remove_cart_item($cart_item_key);
                        }
                    }
                    
                    $simulator_fields_ids = $this->calculatorHelper->get_simulator_fields($simulator->id);
                    $fields = $this->fieldHelper->get_fields_by_ids($simulator_fields_ids);

                }
	}
	
        /*
         * Eseguito prima di effettuare il calcolo del totale in cart/checkout
         * Permette di calcolare il totale per ogni prodotto
         */
	public function add_custom_price($cart_object){
            $this->updateCartByCartObject($cart_object);
	}
        
        
        /*
         * Calcola il prezzo del prodotto e lo aggiorna nel carrello
         */
        public function updateCartByCartObject($cart_object){
            global $woocommerce;
     
            foreach ($cart_object->cart_contents as $key => $value){
                if(isset($value['simulator_id'])){
                    $simulatorId    = $value['simulator_id'];
                    $product_price  = 0;
                    $fieldsData     = $value['simulator_fields_data'];
                    $calculator     = $this->calculatorModel->get($simulatorId);
                    
                    if(empty($calculator)){
                        /* Probabilmente il calcolatore è stato cancellato lato admin */
                        $woocommerce->cart->remove_cart_item($key);
                    }else{
                    	
                        
                            //echo $this->calculatorHelper->calculate_price($value['product_id'], $variant, false) . "|";
                            $product_price += $this->calculatorHelper->calculate_price($value['product_id'], $fieldsData, false);

                            $value['data']->set_price($product_price);
                       
                    }

                    $woocommerce->cart->persistent_cart_update();
                }
            }
        }

        
        /*
         * Funzione richiamata via Ajax per il calcolo in real-time del prezzo
         */
	public function woopricesim_ajax_callback(){
            global $woocommerce;

            $wpcAction          = $this->wsf->requestValue('wpc_action');
            $productId          = $this->wsf->requestValue('id');
            $simulatorId        = $this->wsf->requestValue('simulatorid');

            if(!empty($productId) && !empty($simulatorId)){
                if($wpcAction == 'edit_cart_item'){
                    $cartItemKey    = $this->wsf->requestValue('cart_item_key');
                    $quantity       = $this->wsf->requestValue('quantity');

                    $woocommerce->cart->remove_cart_item($cartItemKey);
                    $woocommerce->cart->add_to_cart($productId, $quantity, 0, array(), array(
                        'simulator_id'              => $simulatorId,
                        'simulator_fields_data'     => $_POST,
                    ));
                }else{
                    $simulatorFieldsIds     = $this->calculatorHelper->get_simulator_fields($simulatorId);
                    $fields                 = $this->fieldHelper->get_fields_by_ids($simulatorFieldsIds);
                    $errors                 = $this->calculatorHelper->checkErrors($fields, $_POST);
                    $price                  = null;
                    
                    if(count($errors) == 0){
                        $price              = $this->calculatorHelper->calculate_price($productId, $_POST, true, $simulatorId);
                    }
                    
                    die(json_encode(array(
                        'errorsCount' => count($errors),
                        'errors'      => $errors,
                        'price'       => $price,
                    )));
                }
            }
            
            exit(-1);
	}
        
        public function woopricesim_download_ajax_callback(){
            $simulatorId          = $this->wsf->requestValue('simulator_id');
            
            $this->calculatorHelper->downloadSpreadsheet($simulatorId);
        }
        
	public function woopricesim_upload_ajax_callback(){
                $targetPath     = $this->wsf->getUploadPath('docs');
                $verifyToken    = md5('unique_salt' . $this->wsf->requestValue('timestamp'));
                $token          = $this->wsf->requestValue('token');
                $siteUrl        = get_site_url();
                
                if (!empty($_FILES) && $token == $verifyToken) {
                    $tempFile   = $_FILES['file_upload']['tmp_name'];
                    $filename   = $_FILES['file_upload']['name'];
                    
                    // Validate the file type
                    $fileTypes = array('xls','xlsx', 'ods'); // File extensions
                    $fileParts = pathinfo($filename);
                    
                    $targetFile = rtrim($targetPath,'/') . '/' . $token;
                    
                    if (in_array($fileParts['extension'],$fileTypes)) {
                            move_uploaded_file($tempFile, $targetFile);
                            echo $token;
                    } else {
                            echo 'Invalid file type.';
                    }
                }
                
                $urlParams  = array(
                    'page=woo-price-calculator',
                    'controller=calculator',
                    'action=loadersheet',
                    'file=' . $token,
                    'filename=' . urlencode($filename),
                );

                header("location: {$siteUrl}/wp-admin/admin.php?" . implode("&", $urlParams));
                exit(-1);
	}
        
        /*
         * Visualizzazione del simulatore nella scheda prodotto
         */
	public function product_meta_end(){
            $product    = new WC_Product(get_the_ID());
            $simulator  = $this->calculatorHelper->get_simulator_for_product(get_the_ID());

            if(!empty($simulator)){
                $simulator_fields_ids = $this->calculatorHelper->get_simulator_fields($simulator->id);
                $simulator_fields = $this->fieldHelper->get_fields_by_ids($simulator_fields_ids);

                $fields = array();
                foreach($simulator_fields as $key => $v){
                    $options = json_decode($v->options, true);

                    $v_id = $this->wsf->getPluginShortCode() . '_' . $v->id;

                    $fields[$v_id] = array(
                        'id'            => $v_id,
                        'label_id'      => $this->wsf->getPluginShortCode() . '_label_' . $v->id,
                        'label_name'    => $this->wsf->userTrans($v->label),
                        'field_id'      => $this->wsf->getPluginShortCode() . '_input_' . $v->id,
                        'type'          => $v->type,
                        'class'         => $this->wsf->getPluginShortCode() . "_" . $v->type
                    );

                    if($v->type == "checkbox"){
                            $value = $this->wsf->requestValue($v_id);
                            if($value === "on"){
                                $checked = "checked";
                            }else{
                                $checked = "";
                                if($options['checkbox']['default_status'] == 1){
                                    $checked = "checked";
                                }
                            }

                            $fields[$v_id]['value']     = $checked;
                            $fields[$v_id]['html']      = '<input id="' . $v_id . '" name="' . $v_id . '" class="' . $fields[$v_id]['class'] . '" type="checkbox" ' . $checked . '/>';
                    }else if($v->type == "numeric"){
                            $value = $this->wsf->requestValue($v_id);
                            if(empty($value)){
                                $value = $options['numeric']['default_value'];
                            }
                            $fields[$v_id]['value']     = $value;
                            $fields[$v_id]['html']      = '<input id="' . $v_id . '" name="' . $v_id . '" class="' . $fields[$v_id]['class'] . '" type="text" value="' . $value . '" />';

                    }else if($v->type == "picklist"){

                            $current_value = $this->wsf->requestValue($v_id);
                            $picklist_items = $this->fieldHelper->get_field_picklist_items($v);

                            $fields[$v_id]['html']      = '<select id="' . $v_id . '" name="' . $v_id . '" class="' . $fields[$v_id]['class'] . '">';

                            foreach($picklist_items as $index => $item){
                                $selected = '';
                                if($current_value == $item['id']){
                                    $selected = 'selected="selected"';
                                }

                                $fields[$v_id]['html'] .= '<option value="' . $item['id'] . '" ' . $selected . '>' . $item['label'] . '</option>';
                            }

                            $fields[$v_id]['value'] = $item['value'];
                            $fields[$v_id]['html'] .= '</select>';
                    }else if($v->type == "text"){
                            $value = $this->wsf->requestValue($v_id);
                            if(empty($value)){
                                $value = htmlspecialchars($this->wsf->decode($options['text']['default_value']));
                            }

                            $fields[$v_id]['value']     = $value;
                            $fields[$v_id]['html']      = '<input id="' . $v_id . '" name="' . $v_id . '" class="' . $fields[$v_id]['class'] . '" type="text" value="' . 
                                    $value . 
                                    '" />';
                    }
                }

                $defaultProductView             = $this->wsf->getView('product/product.php', array(
                    'product'               => $product,
                    'simulator'             => $simulator,
                    'data'                  => $this->getDefaultThemeData($simulator_fields),
                ));
                
                if(empty($simulator->theme)){
                    echo $defaultProductView;
                }else{
                    $this->wsf->renderView($this->wsf->getUploadPath("themes/{$simulator->theme}"), array(
                        'simulator'             => $simulator,
                        'simulator_fields'      => $simulator_fields,
                        'fields'                => $fields,
                        'data'                  => $this->getDefaultThemeData($simulator_fields),
                        'defaultView'           => $defaultProductView,
                    ), true);
                }

                $this->wsf->renderView("product/footer_data.php", array(
                    'product'               => $product,
                    'simulator'             => $simulator,
                    'simulator_fields'      => $simulator_fields,
                    'fields'                => $fields,
                    'data'                  => $this->getDefaultThemeData($simulator_fields),
                ));

            }
	}

        /*
         * Aggiunge una voce al menù di WooCommerce
         */
	public function register_submenu_page() {
    		add_submenu_page('woocommerce', 
                        $this->plugin_label, 
                        $this->plugin_label, 
                        'manage_woocommerce', 
                        $this->plugin_code, 
                        array($this, 'submenu_callback')
                        ); 
	}

        /*
         * Visualizza il backend del plugin
         */
	public function submenu_callback() {
                echo $this->wsf->execute();
	}

        public function session_start(){
            if(!isset($_SESSION)) { 
                session_start(); 
            } 
        }
        
        public function getDefaultThemeData($simulatorFields, $values = array()){
            $data       = array();

            foreach($simulatorFields as $key => $v){
                $options    = json_decode($v->options, true);
                $elementId  = "{$this->wsf->getPluginShortCode()}_{$v->id}";
                $optionId   = "{$this->wsf->getPluginShortCode()}_{$v->id}_options";
                $class      = "{$this->wsf->getPluginShortCode()}_{$v->type}";
                
                if(count($values) == 0){ //I valori sono nella richiesta
                    $value      = $this->wsf->requestValue($elementId);
                }else{
                    if(isset($values[$elementId])){
                        $value      = $values[$elementId];
                    }else{
                        $value      = 0;
                    }
                    
                }
                
                if($v->type == "checkbox"){
                        if($value === "on"){
                            $checked = "checked";
                        }else{
                            $checked = "";
                            if($options['checkbox']['default_status'] == 1){
                                $checked = "checked";
                            }
                        }
                        
                        $element = "<input name=\"{$elementId}\" type=\"checkbox\" {$checked}/>";
                        
                }else if($v->type == "numeric"){
                        if(empty($value)){
                            $value = $options['numeric']['default_value'];
                        }

                        $element = "<input name=\"{$elementId}\" type=\"text\" value=\"{$value}\" />";
                        
                }else if($v->type == "picklist"){
                        $element = "<select name=\"{$elementId}\">";
                            $picklist_items = $this->fieldHelper->get_field_picklist_items($v);
                            
                            foreach($picklist_items as $index => $item){
                                $selected   = '';
                                $label      = $this->wsf->userTrans($item['label']);
                                
                                if($value == $item['id']){
                                    $selected = 'selected="selected"';
                                }

                                $element .= "<option value=\"{$item['id']}\" {$selected}>{$label}</option>";
                            }
                        $element .= '</select>';
                }else if($v->type == "text"){
                    if(empty($value)){
                        $value = htmlspecialchars($this->wsf->decode($options['text']['default_value']));
                    }

                    $element = "<input name=\"{$elementId}\" type=\"text\" value=\"{$value}\" />";

                }else if($v->type == "date" || $v->type == "time" || $v->type == "datetime"){
                    if(empty($value)){
                        if(isset($options[$v->type])){
                            $value = htmlspecialchars($this->wsf->decode($options[$v->type]['default_value']));
                        }
                    }

                    $element = "<input name=\"{$elementId}\" value=\"{$value}\" type=\"text\" />";
                }else if($v->type == "radio"){
                    $radio_items    = $this->fieldHelper->get_field_radio_items($v);

                    $radioIndex    = 0;
                    $element       = "";
                    foreach($radio_items as $index => $item){
                        $selected   = '';
                        $label      = $this->wsf->userTrans($item['label']);
                        
                        if($value == $item['id']){
                            $selected = 'checked="checked"';
                        }
                        $element .= "<input value=\"{$item['id']}\" {$selected} name=\"{$elementId}\" type=\"radio\" /> {$label} <br/>";

                        $radioIndex++;
                    }

                }
                               
                $viewParams             = array(
                            'elementId'     => $elementId,
                            'field'         => $v,
                            'labelId'       => "{$this->wsf->getPluginShortCode()}_label_{$v->id}",
                            'inputId'       => "{$this->wsf->getPluginShortCode()}_input_{$v->id}",
                            'optionId'      => $optionId,
                            'value'         => $value,
                            'element'       => $element,
                            'options'       => json_encode($options),
                            'class'         => $class,      
                );
                    
                $viewParams['widget']   = $this->wsf->getView('product/widget.php', $viewParams);
                $data[$elementId]       = $viewParams;
                
            }
            
            return $data;
        }

}

/*
 * Controllo che WooCommerce sia attivato
 */
if (in_array( 'woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    $woo_price_calculator = new Woo_Price_Calculator();
}
