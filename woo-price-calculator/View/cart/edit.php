<div id="wpc-cart-container">
    <?php if($this->getLicense() == 1): ?>
    <?php else: ?>
        <?php echo $this->view['price']; ?>
    <?php endif; ?>
</div>

