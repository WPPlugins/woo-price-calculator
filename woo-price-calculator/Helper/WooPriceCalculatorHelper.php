<?php

namespace WSF\Helper;

use WSF\WSF;

class WooPriceCalculatorHelper {
    
    var $wsf;
    
    public function __construct(WSF $wsf) {
        $this->wsf = $wsf;
    }
    
    function getCreditsUrl(){
        return 'http://www.altosmail.com?wt_source=woo-price-calculator';
    }

    function logo(){
        return plugins_url($this->wsf->getPluginDir()  . '/assets/Altosmail-logo.png');
    }
    
    function icon(){
        return plugins_url($this->wsf->getPluginDir() . '/assets/app-icon.png');
    }
    
    function help($text, $size = "13"){
        ?>

        <?php
    }
 
}