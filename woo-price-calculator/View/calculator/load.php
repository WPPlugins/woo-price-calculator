<?php $timestamp = time();?>
<div class="wsf-bs wsf-wrap">
    
    <div class="row">
        <div class="col-xs-12">
            <div class="alert alert-info">
                <i class="fa fa-question-circle"></i> <?php echo $this->trans('wpc.calculator.calculator_speed'); ?>
            </div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xs-12 text-center">
            <h2><?php echo $this->trans('wpc.load_calculator'); ?></h2>
            <strong><?php echo $this->trans('Please select xlsx, xls, ods files'); ?>:</strong>
        </div>
    </div>
    
    <div class="row">
        <div class="col-xs-12 text-center ma-sm">
            <center>
                <form method="post" action="<?php echo get_site_url(); ?>/wp-admin/admin-ajax.php?action=woopricesim_upload_ajax_callback" enctype="multipart/form-data">
                        <div id="queue"></div>
                        <input id="file_upload" name="file_upload" type="file" />

                        <input class="btn btn-primary ma-sm" type="submit" value="<?php echo $this->trans('wpc.next') ?>" />

                        <input type="hidden" name="timestamp" value="<?php echo $timestamp;?>" />
                        <input type="hidden" name="token" value="<?php echo md5('unique_salt' . $timestamp);?>" />
                </form>
            </center>
        </div>
    </div>
</div>

<?php $this->renderView('app/footer.php'); ?>