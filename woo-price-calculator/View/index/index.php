<?php
    //Controllo della versione PHP
    if(version_compare(phpversion(), '5.0.0', '<=')) {
        die("<div style=\"padding-top:30px\"><b><center>Error: Compatible PHP versions are: >= 5.0.0</center></b></div>");
    }
?>

<div class="wsf-bs wsf-wrap">
    <div class="row">
        <div class="col-xs-12 col">
            <div>
                <a style="" class="pull-left" target="_blank" href="http://woopricecalculator.com?wt_source=woo-price-calculator">
                    <img style="max-width: 150px;" src="<?php echo $this->view['icon']; ?>">
                </a>
    
                <div class="pull-right">
                    <a target="_blank" href="https://woopricecalculator.com/documentation?wt_source=woo-price-calculator" class="btn btn-default">
                        <i class="fa fa-book"></i> <?php echo $this->trans('wpc.documentation'); ?>
                    </a>
                    <a target="_blank" href="https://woopricecalculator.com/forum?wt_source=woo-price-calculator" class="btn btn-default">
                        <i class="fa fa-question"></i> <?php echo $this->trans('wpc.forum'); ?>
                    </a>
                    <a target="_blank" href="https://woopricecalculator.com/donate?wt_source=woo-price-calculator" class="btn btn-default">
                        <i class="fa fa-gift"></i> <?php echo $this->trans('wpc.donate'); ?>
                    </a>
                </div>
            </div>
        </div>
        <div class="col-xs-12 col">
<div class="mr-md">
<?php if($this->getLicense() == 0): ?>
<p style="margin-top: 15px; padding: 15px; background-color: #fff; box-shadow: 0 6px 10px #A9A9A9;">
<?php echo $this->trans('wpc.go_pro'); ?>
</p>
<?php else: ?>
<p style="margin-top: 15px; padding: 15px; background-color: #fff; box-shadow: 0 6px 10px #A9A9A9;">
<?php echo $this->trans('wpc.header.pro'); ?>
</p>
<?php endif; ?>
</div>
</div>
    </div>

    <br/><br/>

    <ul class="nav nav-tabs">
        <li class="<?php echo ($this->view['controller'] == "field" || empty($this->view['controller']))?"active":""; ?>">
            <a href="<?php echo $this->adminUrl(array('controller' => 'field')); ?>"><?php echo $this->trans('Fields'); ?></a>
        </li>
        
        <li class="<?php echo ($this->view['controller'] == "calculator")?"active":""; ?>">
            <a href="<?php echo $this->adminUrl(array('controller' => 'calculator')); ?>"><?php echo $this->trans('Calculator'); ?></a>
        </li>
        
        <?php if($this->getLicense() != 0): ?>
        <li class="<?php echo ($this->view['controller'] == "regex")?"active":""; ?>">
            <a href="<?php echo $this->adminUrl(array('controller' => 'regex')); ?>"><?php echo $this->trans('wpc.regex'); ?></a>
        </li>
        <?php endif; ?>
        
        <li class="<?php echo ($this->view['controller'] == "settings")?"active":""; ?>">
            <a href="<?php echo $this->adminUrl(array('controller' => 'settings')); ?>"><?php echo $this->trans('wpc.settings'); ?></a>
        </li>
        
    </ul>
    
</div>
