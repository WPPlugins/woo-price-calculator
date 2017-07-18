<div class="wsf-bs wsf-wrap">
    <div class="ma-md">
        <div class="row">
            <div class="col-xs-12 text-center">
                <a href="<?php echo $this->adminUrl(array('controller' => 'calculator', 'action' => 'add')); ?>" class="btn btn-primary">
                    <i class="fa fa-calculator"></i> <?php echo $this->trans('wpc.calculator.new'); ?>
                </a>
                
                <?php if($this->getLicense() != 0): ?>
                <a href="<?php echo $this->adminUrl(array('controller' => 'calculator', 'action' => 'loader')); ?>" class="btn btn-primary">
                    <i class="fa fa-file-excel-o"></i> <?php echo $this->trans('wpc.calculator.load'); ?>
                </a>
                <?php endif; ?>
            </div>
        </div>
        
        <table class="table table-striped table-bordered data-table" width="100%">
            <thead>
                <tr>
                    <?php foreach ($this->view['list_header'] as $headerKey => $headerLabel): ?>
                        <th class="text-center"><?php echo $headerLabel; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($this->view['list_rows'] as $row): ?>
                    <tr>
                        <td><?php echo $row->name; ?></td>
                        <td><?php echo $row->description; ?></td>
                        <td><?php echo $this->trans("wpc.calculator.type.{$row->type}"); ?></td>
                        <td class="col-xs-3">
                            <a href="<?php echo $this->adminUrl(array('controller' => 'calculator', 'action' => 'edit','id' => $row->id)); ?>" class="btn btn-primary"><?php echo $this->trans('wpc.edit'); ?></a>
                            
                            <?php if($row->type == 'excel'): ?>
                            <a href="<?php echo $this->adminUrl(array('controller' => 'calculator', 'action' => 'loadermapping','calculator_id' => $row->id)); ?>" class="btn btn-primary"><?php echo $this->trans('wpc.calculator.edit_mapping'); ?></a>
                            <?php endif; ?>

                            <?php if(empty($row->system_created)): ?>
                            <a onclick="return confirm('<?php echo $this->trans('wpc.delete.warning'); ?>');" href="<?php echo $this->adminUrl(array('controller' => 'calculator', 'action' => 'delete', 'id' => $row->id)); ?>" class="btn btn-danger">
                                <?php echo $this->trans('wpc.delete'); ?>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
    </div>
    
</div>

<?php $this->renderView('app/footer.php'); ?>
