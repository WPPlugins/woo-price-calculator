<div class="wsf-bs">
    <div class="alert alert-success mt-md">
        <h4><?php echo $this->view['recordName']; ?> <?php echo $this->trans('has been'); ?> <?php echo $this->view['mode']; ?>.</h4>
        
        <?php if(!empty($this->view['url'])): ?>
            <?php echo $this->trans('Click'); ?> <a href="<?php echo $this->view['url']; ?>"><?php echo $this->trans('here'); ?></a> <?php echo $this->trans('to go back'); ?>.
        <?php endif; ?>
    </div>
</div>


