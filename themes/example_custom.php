<?php
/*
 * THEME_NAME: Example Custom Theme
 */
?>

<div class="wpc-product-form">
	<!--Field start-->
	<div id="<?php echo $this->view['data']['woo_price_calc_1']['elementId']; ?>" class="form-group wpc-field-widget">
		<div class="wpc-field <?php echo $this->view['data']['woo_price_calc_1']['class']; ?>">
		<?php echo $this->view['fields']['woo_price_calc_1']['html']; ?>
		<label for="<?php echo $this->view['data']['woo_price_calc_1']['elementId']; ?>_field">
			<?php echo $this->view['fields']['woo_price_calc_1']['label_name']; ?>
		</label>
		</div>
		<div class="wpc-field-error"></div>
	</div>
	<!--Field end-->
	<!--Field start-->
	<div id="<?php echo $this->view['data']['woo_price_calc_2']['elementId']; ?>" class="form-group wpc-field-widget">
		<div class="wpc-field <?php echo $this->view['data']['woo_price_calc_2']['class']; ?>">
		<?php echo $this->view['fields']['woo_price_calc_2']['html']; ?>
		<label for="<?php echo $this->view['data']['woo_price_calc_2']['elementId']; ?>_field">
			<?php echo $this->view['fields']['woo_price_calc_2']['label_name']; ?>
		</label>
		</div>
		<div class="wpc-field-error"></div>
	</div>
	<!--Field end-->
	<!--Field start-->
	<div id="<?php echo $this->view['data']['woo_price_calc_3']['elementId']; ?>" class="form-group wpc-field-widget">
		<div class="wpc-field <?php echo $this->view['data']['woo_price_calc_3']['class']; ?>">
		<?php echo $this->view['fields']['woo_price_calc_3']['html']; ?>
		<label for="<?php echo $this->view['data']['woo_price_calc_3']['elementId']; ?>_field">
			<?php echo $this->view['fields']['woo_price_calc_3']['label_name']; ?>
		</label>
		</div>
		<div class="wpc-field-error"></div>
	</div>
	<!--Field end-->
</div>