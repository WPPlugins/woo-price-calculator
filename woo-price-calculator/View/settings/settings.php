<div class="wsf-bs wsf-wrap">
    <div class="mt-md">
        <?php if(count($this->view['errors']) != 0): ?>
            <div class="alert alert-danger">
                <?php echo implode("<br/>", $this->view['errors']); ?> 
            </div>
        <?php endif; ?>

        <form id="wpc_field_form" action="<?php echo $this->adminUrl(array('controller' => 'settings', 'action' => 'index')); ?>" method="POST">
            <div class="panel panel-default">
                <div class="panel-heading text-center">
                    <h4>
                        <i class="fa fa-cog"></i> <?php echo $this->trans('wpc.settings'); ?>
                    </h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-12">
                            <div class="form-horizontal wpc-form">

                               <div class="form-group">
                                   <label class="control-label col-sm-4" for="cart_edit_button_class">
                                       <?php $this->renderView('partial/help.php', array('text' => $this->trans('wpc.settings.form.cart_edit_button_class.tooltip'))); ?> <?php echo $this->trans('wpc.settings.form.cart_edit_button_class.label'); ?>
                                   </label>
                                   <div class="col-sm-8">
                                       <input type="text" class="form-control" name="cart_edit_button_class" value="<?php echo htmlspecialchars($this->view['form']['cart_edit_button_class']); ?>" />
                                   </div>
                               </div>
                                
                               <div class="form-group">
                                   <label class="control-label col-sm-4" for="custom_css">
                                       <?php $this->renderView('partial/help.php', array('text' => $this->trans('wpc.settings.form.custom_css.tooltip'))); ?> <?php echo $this->trans('wpc.settings.form.custom_css.label'); ?>
                                   </label>
                                   <div class="col-sm-8">
                                        <textarea class="form-control" name="custom_css" rows="20"><?php echo htmlspecialchars($this->view['form']['custom_css']); ?></textarea>
                                   </div>
                               </div>

                                <div class="form-group">
                                    <div class="col-sm-10 col-sm-offset-3 text-center">
                                        <button id="wpc_field_form_submit" type="button" class="btn btn-primary">
                                            <i class="fa fa-floppy-o"></i> <?php echo $this->trans('wpc.save'); ?>
                                        </button>
                                    </div>
                                </div>

                                <input type="hidden" name="task" value="save" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>

</div>

<?php $this->renderView('app/footer.php'); ?>
