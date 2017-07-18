<table class="wpc-product-form" id="<?php echo $this->getPluginShortCode(); ?>_product_table">
        <?php foreach($this->view['data'] as $key => $data): ?>
            <tr>
                <td id="<?php echo $data['labelId']; ?>">
                    <?php echo $this->userTrans($data['field']->label); ?>
                </td>
                
                <td id="<?php echo $data['inputId']; ?>">
                    <?php echo $data['widget']; ?>
                </td>
            </tr>
        <?php endforeach; ?>
            
</table>
