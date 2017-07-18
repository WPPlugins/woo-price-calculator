<div id="radio_options" style="display: none;">
    <div class="form-group">
        <label class="control-label col-sm-4" for="default_status">
                <?php
                    $this->renderView('partial/help.php', 
                            array('text' => $this->trans('wpc.field.radio.tooltip')));
                ?> <?php echo $this->trans('Picklist Items'); ?>
        </label>
        <div class="col-sm-8">
            <div class="row">
                <div class="col-xs-12 text-center">
                    <button data-sortable-items="#radio_items_sortable" data-sortable-items-data="#radio_items" type="button" class="field_list_add btn btn-primary"><?php echo $this->trans('wpc.add'); ?></button>
                </div>
            </div>
            
            <div class="row">
                <div class="col-xs-12">
                    <ul id="radio_items_sortable">
                        <?php foreach($this->view['radio_items_data'] as $index => $radio): ?>
                            <li data-id="<?php echo $radio['id']; ?>" data-value="<?php echo $radio['value']; ?>" data-label="<?php echo $radio['label']; ?>">
                                <a class="btn btn-danger js-remove" data-sortable-items="#radio_items_sortable" data-sortable-items-data="#radio_items">
                                    <i class="fa fa-times"></i>
                                </a> 

                                <a class="btn btn-primary sortable-edit" data-sortable-items="#radio_items_sortable" data-sortable-items-data="#radio_items">
                                    <i class="fa fa-pencil"></i>
                                </a>

                                <?php echo $radio['label']; ?> <i>[Value: <?php echo $radio['value']; ?>]</i>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
            
            <input type="hidden" id="radio_items" name="radio_items" value="<?php echo htmlentities($this->view['form']['radio_items']); ?>" />
            
        </div>
    </div>
</div>
