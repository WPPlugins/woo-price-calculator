<!--WPC-FREE-->
<?php
	 echo base64_decode("PHNtYWxsPjxici8+PGJyLz48YSBocmVmPSJodHRwOi8vd29vcHJpY2VjYWxjdWxhdG9yLmNvbSIgdGFyZ2V0PSJfYmxhbmsiPlBvd2VyZWQgYnkgV29vUHJpY2UgQ2FsY3VsYXRvcjwvYT48YnIvPjxici8+PC9zbWFsbD4="); 
 ?>
<!--/WPC-FREE-->

<input type="hidden" class="wpc_product_id" value="<?php echo $this->view['product']->get_id(); ?>" />
<input type="hidden" class="wpc_simulator_id" value="<?php echo $this->view['simulator']->id; ?>" />

<?php foreach($this->view['data'] as $key => $data): ?>
<input type="hidden" id="<?php echo $data['optionId']; ?>" value="<?php echo htmlspecialchars($data['options']); ?>" />
<?php endforeach; ?>

<?php //echo printf($this->get_price_format(), 2, 1); ?>
