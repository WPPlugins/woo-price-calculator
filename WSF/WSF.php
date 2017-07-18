<?php
/*
 * Sefin Framework for Wordpress
 */
namespace WSF;

use WSF\Controller;

class WSF {
        var $plugin_label;
        var $plugin_code;
        var $plugin_short_code;
        var $plugin_dir;
        
        var $view;
        
        var $controllerName;
        var $actionName;
        
        var $controller;
        
        var $executions;
        
        public function __construct($plugin_dir){
            $this->plugin_dir = $plugin_dir;
            
            $config = $this->get('\\WSF\\Config', 'Config', 'Config');
            
            $config_params = $config->initialize();
            
            foreach($config_params as $config_key => $config_value){
                $this->{$config_key} = $config_value;
            }
  
        }
        
        public function execute($pcontroller = null, $paction = null){
            if(empty($pcontroller)){
                $controllerRequest     = $this->requestValue('controller');
            }else{
                $controllerRequest     = $pcontroller;
            }

            if(empty($paction)){
                /*
                 * Forzo da richiesta da GET, in modo tale che altri componenti, 
                 * possano usare il GET, con all'interno delle variabili nascoste di tipo
                 * action
                 */
                
                $actionRequest         = $this->requestValue('action', 'GET');
            }else{
                $actionRequest         = $paction;
            }

            if(empty($actionRequest)){
                $actionRequest = "index";
            }

            $this->actionName = $actionRequest;
            
            $actionRequest      .= 'Action';
            
            $controllerName = $this->getControllerName($controllerRequest);
            $controllerClass = '\\WSF\\Controller\\' . $controllerName;
            $controllerPath = $this->getPluginPath("{$this->plugin_dir}/Controller/{$controllerName}.php");
            
            $this->controllerName = $controllerName;
                        
            require_once($controllerPath);
            
            $controller = new $controllerClass($this);
            $this->controller = $controller;

            if(empty($this->executions)){
                $this->executions = array();
            }
            
            /* Evita i loop nell'esecuzione delle azioni */
            if($this->checkLoop($controllerName, $actionRequest, $this->executions) == true){
                return;
            }
            
            $this->executions[] = array(
                'controller'    => $controllerName,
                'action'        => $actionRequest,
            );
            
            /* DEBUG */
            if(count($this->executions) >= 1){
                //print_r($this->executions);
               // exit(-1);
            }
            
            $controller->{$actionRequest}();
        }
        
        public function getFirstExecution(){
            return $this->executions[0];
        }
        
        private function checkLoop($controller, $action, $executions){
            foreach($executions as $execution){
                if($execution['controller'] == $controller &&
                   $execution['action']     == $action){
                    return true;
                }
            }
            
            return false;
        }
        
        public function requestValue($name = null, $type = "REQUEST", $default = null){
            if(empty($name)){
                if(empty($type) || $type == "REQUEST"){
                    return $_REQUEST;
                }else if($type == "GET"){
                    return $_GET;
                }else if($type == "POST"){
                    return $_POST;
                }
            }
            
            if(empty($type) || $type == "REQUEST"){
                if(isset($_REQUEST[$name])){
                    return is_string($_REQUEST[$name])?stripslashes($_REQUEST[$name]):$_REQUEST[$name];
                }
            }else if($type == "GET"){
                if(isset($_GET[$name])){
                    return is_string($_GET[$name])?stripslashes($_GET[$name]):$_GET[$name];
                }
            }else if($type == "POST"){
                if(isset($_POST[$name])){
                    return is_string($_POST[$name])?stripslashes($_POST[$name]):$_POST[$name];
                }
            }

            return $default;
        }
        
        function getPluginPath($relpath){
            return plugin_dir_path( __DIR__ ) . $relpath;
        }
        
        function getUploadUrl($relPath){
            $uploadDirArray     = wp_upload_dir();
            
            return "{$uploadDirArray['baseurl']}/woo-price-calculator/{$relPath}";
        }
        
        function getUploadPath($relPath){
            $uploadDirArray     = wp_upload_dir();
            
            return "{$uploadDirArray['basedir']}/woo-price-calculator/{$relPath}";
        }
        
        function getPluginUrl($relpath = ''){
            return site_url() . '/wp-content/plugins/' . $this->plugin_dir . '/' . $relpath;
        }
        
        function getControllerName($controllerName = null){
            if(empty($controllerName)){
                $retControllerName = 'Index';
            }else{
                $retControllerName = $controllerName;
                $retControllerName = ucfirst($retControllerName);
            }

            $retControllerName .= 'Controller';
            
            return $retControllerName;
        }
        
        function renderView($view, $params = array(), $absolutePath = false){
            
            foreach($params as $param_name => $param_value){
                $this->view[$param_name] = $param_value;
            }

            if($absolutePath == false){
                require($this->getPluginPath($this->plugin_dir . '/View/' . $view));
            }else{
                require($view);
            }
        }
        
        function getView($view, $params = array()){
            foreach($params as $param_name => $param_value){
                $this->view[$param_name] = $param_value;
            }

            ob_start();
            require($this->getPluginPath($this->plugin_dir . '/View/' . $view));
            $view   = ob_get_contents();
            ob_end_clean();
            
            return $view;
        }
        
        function get($namespace, $path, $class, $params = array()){
            require_once ($this->getPluginPath($this->plugin_dir . '/' . $path . '/' . $class . '.php'));

            $className = $namespace . '\\' . $class;
            
            $reflection = new \ReflectionClass($className); 
            return $reflection->newInstanceArgs($params); 
        }
        
        function getPluginLabel(){
            return $this->plugin_label;
        }
        
        function getPluginCode(){
            return $this->plugin_code;
        }
        
        function getPluginShortCode(){
            return $this->plugin_short_code;
        }
        
        function getPluginDir(){
            return $this->plugin_dir;
        }
        
        function adminUrl($params = null){
            $url = "admin.php?page=" . $this->plugin_code;
            
            foreach($params as $key => $value){
                $url .= '&' . $key . '=' . $value;
            }
            return admin_url($url);
        }
        
        function getCurrentControllerName(){
            return $this->controllerName;
        }
        
        function getCurrentActionName(){
            return $this->actionName;
        }
        
        /*
         * Effettua la traduzione utilizzando i file lingua dell'utente
         */
        function userTrans($string, $tokens = array()){
            $defaultLocale      = "en_US";
            $locale             = get_locale();
            $langFilePath       = $this->getUploadPath("translations/{$locale}.php");
            
            if(empty($locale) || file_exists($langFilePath) == false){
                $locale         = $defaultLocale;
                $langFilePath   = $this->getUploadPath("translations/{$locale}.php");
                
                if(file_exists($langFilePath) == false){
                    return $string;
                }
            }
            
            $translations   = include $langFilePath;
            
            if(!isset($translations[$string])){
                return $string;
            }
            
            $translation    = $translations[$string];
            
            foreach($tokens as $key => $value){
                $translation     = str_replace("%{$key}%", $value, $translation);
            }
            
            if(empty($translation)){
                return $string;
            }
            
            return $translation;
        }
        
        function trans($string, $tokens = array()){
            $defaultLocale      = "en_US";
            $locale             = get_locale();
            $langFilePath       = $this->getPluginPath("lang/{$locale}.php");
            
            if(empty($locale) || file_exists($langFilePath) == false){
                $locale         = $defaultLocale;
                $langFilePath   = $this->getPluginPath("lang/{$locale}.php");
            }
            
            $translations   = include $langFilePath;
            
            if(isset($translations[$string])){
                $translation    = $translations[$string];

                foreach($tokens as $key => $value){
                    $translation     = str_replace("%{$key}%", $value, $translation);
                }
            }else{
                return $string;
            }
            
            if(empty($translation)){
                return $string;
            }
            
            return $translation;
        }
        
        public function requestForm($formClass, $setValues = array()){
            $ret = array();
            
            $fields = $formClass->getForm();
            
            foreach($fields as $field){
                $default = $this->isset_or($field['default']);
                
                $ret[$field['name']] = $this->requestValue($field['name'], null, $default);
            }
            
            $ret = array_merge($ret, $setValues);
            
            return $ret;
        }
        
        public function setFormField($formClass, $field_name, $field_value){
            $fields = $formClass->getForm();
            
            $fields[$field_name] = $field_value;
            
            $formClass->setForm($fields);
        }
        
        public function getDB(){
            global $wpdb;
            
            return $wpdb;
        }
        
        public function isPost(){
            if($_SERVER['REQUEST_METHOD'] == 'POST'){
                return true;
            }
            
            return false;
        }
        
        public function redirect($location = null){
            if(is_array($location)){
                $location = $this->adminUrl($location);
            }
            
            wp_redirect($location);
            exit(-1);
        }

        function isset_or(&$check, $alternate = NULL){
            return (isset($check)) ? $check : $alternate;
        } 
        
        /*
         * Effettua la decodifica di codice JSON inserito nel database
         */
        public function decode($string){
            $ret = $string;
            
            $ret = str_replace("\\\"", '"', $ret);
            $ret = str_replace("\\'", "'", $ret);
            
            return $ret;
        }
        
        public function getLicense(){
            $license   = file_get_contents($this->getPluginPath("data/license.bin"));
            
            return $license;
        }
        
        public function getImageUrl($imagePath){
            $siteUrl        = get_site_url();
            $pluginDir      = $this->getPluginDir();
            
            return "{$siteUrl}/wp-content/plugins/{$pluginDir}/assets/{$imagePath}";
        }
        
}