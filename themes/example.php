<?php
/*
 * THEME_NAME: Example (No Front End Framework)
 */
?>

<h1>This is a template example</h1>

<div class="wpc-product-form">
    <?php foreach($this->view['data'] as $field): ?>
    
            <b><?php echo $field['field']->label; ?>:</b><br/>
            <?php echo $field['widget']; ?>
            
            <br/><br/>
            
    <?php endforeach; ?>
</div>

