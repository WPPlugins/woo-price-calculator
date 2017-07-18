<?php
namespace WSF\Controller;

use WSF\WSF;
use WSF\Helper\WooPriceCalculatorHelper;

class IndexController {
    private $wsf;
    
    public function __construct(WSF $wsf){
        $this->wsf = $wsf;
        
        $this->wooPriceCalculatorHelper = $this->wsf->get('\\WSF\\Helper', 'Helper', 'WooPriceCalculatorHelper', array($wsf));
    }
    
    public function indexAction(){
        $logo       = $this->wooPriceCalculatorHelper->logo();
        $icon       = $this->wooPriceCalculatorHelper->icon();
        $credits    = $this->wooPriceCalculatorHelper->getCreditsUrl();

        $this->wsf->renderView('index/index.php', array(
            'logo'          => $logo,
            'icon'          => $icon,
            'credits'       => $credits,
            //'controller'    => $this->wsf->getCurrentControllerName(),
            'controller'    => $this->wsf->requestValue("controller")
        ));
        
        $firstExecution = $this->wsf->getFirstExecution();

        if($firstExecution['controller'] == 'IndexController' &&
           $firstExecution['action']     == 'indexAction'){

            $this->wsf->execute('field', 'index');
        }
    }
    
    public function footerAction(){
        $this->wsf->renderView('app/footer.php');
    }

}