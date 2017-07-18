<div class="wsf-bs wsf-wrap">
    <div class="mt-md">
        <?php if(count($this->view['errors']) != 0): ?>
            <div class="alert alert-danger">
                <?php echo implode("<br/>", $this->view['errors']); ?> 
            </div>
        <?php endif; ?>

        <form id="wpc_field_form" action="<?php echo $this->adminUrl(array('controller' => 'field', 'action' => 'form', 'id' => $this->view['id'])); ?>" method="POST">
            <div class="panel panel-default">
                <div class="panel-heading text-center">
                    <h4>
                        <i class="fa fa-pencil"></i> <?php echo $this->view['title'] . ' ' . $this->trans('Calculator Field'); ?>
                    </h4>
                </div>
                <div class="panel-body">
                    <div class="row">
                        <div class="col-xs-6 col-xs-offset-2">
                            <div class="form-horizontal wpc-form">
                               <div class="form-group">
                                   <label class="control-label col-sm-4 required" for="name">
                                       <?php $this->renderView('partial/help.php', array('text' => $this->trans('You can use this name in formula field'))); ?> <?php echo $this->trans('Field Name'); ?>
                                   </label>
                                   <div class="col-sm-8">
                                        <?php 
                                            if(!empty($field->id)){
                                                echo $this->getPluginShortCode() . "_" . $field->id; 
                                            }
                                        ?>
                                   </div>
                               </div>

                               <div class="form-group">
                                   <label class="control-label col-sm-4 required" for="label">
                                       <?php $this->renderView('partial/help.php', array('text' => $this->trans('Just to remember what it does'))); ?> <?php echo $this->trans('Field Label'); ?>
                                   </label>
                                   <div class="col-sm-8">
                                       <input class="form-control" name="label" type="text" value="<?php echo htmlspecialchars($this->view['form']['label']); ?>" />
                                   </div>
                               </div>

                               <div class="form-group">
                                   <label class="control-label col-sm-4" for="short_label">
                                       <?php $this->renderView('partial/help.php', array('text' => $this->trans('wpc.field.form.short_label.help'))); ?> <?php echo $this->trans('wpc.field.form.short_label'); ?>
                                   </label>
                                   <div class="col-sm-8">
                                       <input class="form-control" name="short_label" type="text" value="<?php echo htmlspecialchars($this->view['form']['short_label']); ?>" />
                                   </div>
                               </div>
                                
                               <div class="form-group">
                                   <label class="control-label col-sm-4" for="description">
                                       <?php $this->renderView('partial/help.php', array('text' => $this->trans('Just to remember what it does'))); ?> <?php echo $this->trans('wpc.field.form.description'); ?>
                                   </label>
                                   <div class="col-sm-8">
                                        <textarea class="form-control" name="description"><?php echo htmlspecialchars($this->view['form']['description']); ?></textarea>
                                   </div>
                               </div>
                                
                               <div class="form-group">
                                   <label class="control-label col-sm-4 required" for="label">
                                        <?php
                                        $this->renderView('partial/help.php', array(
                                            'text' => $this->trans('Type of field') . ":" . "<br/>" .
                                                "- <b>" . $this->trans('Checkbox') . "</b>: " . $this->trans('Accepts only two states') . "<br/>" .
                                                "- <b>" . $this->trans('Numeric') . "</b>: " . $this->trans('Accepts only numbers') . "<br/>" .
                                                "- <b>" . $this->trans('Picklist') . "</b>: " . $this->trans('List of items') . "<br/>" .
                                                "- <b>" . $this->trans('Text') . "</b>: " . $this->trans('Accepts whatever you want') . "<br/>" .
                                                "- <b>" . $this->trans('wpc.date') . "</b>: " . $this->trans('wpc.date.description') . "<br/>" .
                                                "- <b>" . $this->trans('wpc.time') . "</b>: " . $this->trans('wpc.time.description') . "<br/>" .
                                                "- <b>" . $this->trans('wpc.datetime') . "</b>: " . $this->trans('wpc.datetime.description') . "<br/>" .
                                                "- <b>" . $this->trans('wpc.radio') . "</b>: " . $this->trans('wpc.radio.description') . "<br/>"
                                            )
                                        );
                                        ?> <?php echo $this->trans('Field Type'); ?>
                                   </label>
                                   <div class="col-sm-8">
                                        <select class="form-control" id="field_type" name="type">
                                                <option value="">--<?php echo $this->trans('Select'); ?>--</option> 
                                                <option value="checkbox" <?php if($this->view['form']['type'] == "checkbox"){echo 'selected="selected"';} ?>><?php echo $this->trans('Checkbox'); ?></option>
                                                <option value="numeric" <?php if($this->view['form']['type'] == "numeric"){echo 'selected="selected"';} ?>><?php echo $this->trans('Numeric'); ?></option>
                                                <option value="picklist" <?php if($this->view['form']['type'] == "picklist"){echo 'selected="selected"';} ?>><?php echo $this->trans('Picklist'); ?></option>
                                                <option value="text" <?php if($this->view['form']['type'] == "text"){echo 'selected="selected"';} ?>><?php echo $this->trans('Text'); ?></option>
                                                <option value="date" <?php if($this->view['form']['type'] == "date"){echo 'selected="selected"';} ?>><?php echo $this->trans('wpc.date'); ?></option>
                                                <option value="time" <?php if($this->view['form']['type'] == "time"){echo 'selected="selected"';} ?>><?php echo $this->trans('wpc.time'); ?></option>
                                                <option value="datetime" <?php if($this->view['form']['type'] == "datetime"){echo 'selected="selected"';} ?>><?php echo $this->trans('wpc.datetime'); ?></option>
                                                <option value="radio" <?php if($this->view['form']['type'] == "radio"){echo 'selected="selected"';} ?>><?php echo $this->trans('wpc.radio'); ?></option>

                                        </select>
                                   </div>
                               </div>

                                <?php
                                   $this->renderView('fields/checkbox_options.php', array(
                                        'form'      => $this->view['form']
                                    ));
                                ?>

                                <?php
                                    $this->renderView('fields/picklist_options.php', array(
                                        'form'      => $this->view['form']
                                    ));
                                ?>

                                <?php
                                    $this->renderView('fields/numeric_options.php', array(
                                        'form'      => $this->view['form']
                                    ));
                                ?>

                                <?php
                                    $this->renderView('fields/text_options.php', array(
                                        'form'      => $this->view['form']
                                    ));
                                ?>

                                <?php
                                    $this->renderView('fields/radio_options.php', array(
                                        'form'      => $this->view['form']
                                    ));
                                ?>

                                <div class="form-group">
                                    <div class="col-sm-10 col-sm-offset-3 text-center">
                                        <button id="wpc_field_form_submit" type="button" class="btn btn-primary" <?php echo ($this->view['form']['system_created'] == true)?'disabled="disabled"':''; ?>>
                                            <i class="fa fa-floppy-o"></i> <?php echo $this->trans('wpc.save'); ?>
                                        </button>
                                    </div>
                                </div>

                                <input type="hidden" name="task" value="field_form" />
                                <input type="hidden" id="items_list_id" name="items_list_id" value="<?php echo $this->view['form']['items_list_id']; ?>" />
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </form>
    </div>
    
    <!-- Modal -->
    <div id="field_choise_modal" class="modal fade" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><?php echo $this->trans('wpc.field.choise.modal.header'); ?></h4>
          </div>
          <div class="modal-body">
                <div class="form-horizontal">
                 <div class="form-group">
                    <label class="control-label col-sm-4">
                        <?php echo $this->trans('wpc.field.choise.modal.text'); ?>
                    </label>
                    <div class="col-sm-8">
                        <input id="field_choise_modal_text" type="text" class="form-control" />
                    </div>
                 </div>

                 <div class="form-group">
                    <label class="control-label col-sm-4">
                        <?php echo $this->trans('wpc.field.choise.modal.value'); ?>
                    </label>
                    <div class="col-sm-8">
                        <input id="field_choise_modal_value" type="text" class="form-control" />
                    </div>
                 </div>

               </div>
          </div>
          <div class="modal-footer">
            <button id="field_choise_modal_ok" type="button" class="btn btn-primary"><?php echo $this->trans('wpc.ok'); ?></button>
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->trans('wpc.close'); ?></button>
          </div>
        </div>

      </div>
    </div>
    
    <!-- Modal: Regex -->
    <div id="field_regex_modal" class="modal fade" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><?php echo $this->trans('wpc.field.regex.modal.header'); ?></h4>
          </div>
          <div class="modal-body">
                <select id="field_regex_modal_list" class="form-control">
                    <?php foreach($this->view['regex_list'] as $regex): ?>
                    <option value="<?php echo htmlentities($regex->regex); ?>"><?php echo $regex->name; ?></option>
                    <?php endforeach; ?>
                </select>
          </div>
          <div class="modal-footer">
            <button id="field_regex_modal_ok" type="button" class="btn btn-primary"><?php echo $this->trans('wpc.ok'); ?></button>
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->trans('wpc.close'); ?></button>
          </div>
        </div>

      </div>
    </div>
    
    <!-- Modal: Add/Edit List Element -->
    <div id="field_list_modal" class="modal fade" role="dialog">
      <div class="modal-dialog">

        <!-- Modal content-->
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal">&times;</button>
            <h4 class="modal-title"><?php echo $this->trans('wpc.field.list_add.modal.header'); ?></h4>
          </div>
          <div class="modal-body">
                <div class="form-horizontal">
                    <div class="form-group">
                        <label class="control-label col-sm-3 required" for="field_list_modal_label">
                            <?php echo $this->trans('wpc.field.list.add.label'); ?>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control required" id="field_list_modal_label">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="control-label col-sm-3" for="field_list_modal_value">
                            <?php echo $this->trans('wpc.field.list.add.value'); ?>
                        </label>
                        <div class="col-sm-9">
                            <input type="text" class="form-control wpc-numeric-decimals" id="field_list_modal_value">
                        </div>
                    </div>
               </div>
          </div>
          <div class="modal-footer">
            <button id="field_list_modal_ok" type="button" class="btn btn-primary"><?php echo $this->trans('wpc.ok'); ?></button>
            <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $this->trans('wpc.close'); ?></button>
          </div>
        </div>

      </div>
        
        <input type="hidden" id="field_list_mode" value="" />
    </div>
</div>

<?php $this->renderView('app/footer.php'); ?>