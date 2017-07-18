<div class="wsf-bs wsf-wrap">
    <div class="mt-md">
        <?php if(count($this->view['errors']) != 0): ?>
        <div class="alert alert-danger">
            <?php echo implode("<br/>", $this->view['errors']); ?> 
        </div>
        <?php endif; ?>
        
        <?php if(count($this->view['warnings']) != 0): ?>
        <div class="alert alert-warning">
            <h4><?php echo $this->trans('wpc.warnings'); ?></h4>
            
            <?php echo implode("<br/>", $this->view['warnings']); ?> 
        </div>
        <?php endif; ?>
        
        <div class="panel panel-default">
            <div class="panel-heading text-center">
                <h4>
                    <i class="fa fa-calculator"></i> <?php echo $this->view['title'] . ' ' . $this->trans('Calculator'); ?>
                </h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-xs-6 col-xs-offset-2">
                        <form id="calculator_form" class="form-horizontal wpc-form" action="<?php echo $this->adminUrl(array('controller' => 'calculator', 'action' => $this->view['action'])); ?>" method="POST">

                           <div class="form-group">
                               <label class="control-label col-sm-4 required" for="name">
                                   <?php $this->renderView('partial/help.php', array('text' => $this->trans('Just for remember'))); ?> <?php echo $this->trans('Name'); ?>
                               </label>
                               <div class="col-sm-8">
                                   <input required="required" class="form-control" name="name" type="text" value="<?php echo htmlspecialchars($this->view['form']['name']); ?>" />
                               </div>
                           </div>

                           <div class="form-group">
                               <label class="control-label col-sm-4" for="description">
                                   <?php $this->renderView('partial/help.php', array('text' => $this->trans('Just for remember'))); ?> <?php echo $this->trans('Description'); ?>
                               </label>
                               <div class="col-sm-8">
                                   <textarea class="form-control" style="height: 100px" name="description"><?php echo $this->view['form']['description']; ?></textarea>
                               </div>
                           </div>

                           
                               <div class="form-group">
                                   <label class="control-label col-sm-4" for="fields">
                                       <?php $this->renderView('partial/help.php', array('text' => $this->trans('You must select the fields that you use in the Formula Field'))); ?> <?php echo $this->trans('Fields'); ?>
                                   </label>
                                   <div id="field_container" class="col-sm-8">                                      
                                       <select id="fields" class="form-control wpc-multiselect" name="fields[]" multiple="multiple">
                                           <?php foreach($this->view['orderedFields'] as $field): ?>
                                           <option value="<?php echo $field->id; ?>" <?php if(in_array($field->id, $this->view['form']['fields'])){echo 'selected="selected"';} ?>>
                                                   <?php echo htmlentities($field->label . " [" . $this->getPluginShortCode() . "_" . $field->id . "]"); ?>
                                               </option>
                                           <?php endforeach; ?>
                                       </select>
                                   </div>
                               </div>
                            <?php if($this->view['form']['type'] == 'simple' || empty($this->view['form']['type'])): ?>
                               <div class="form-group">
                                    <label class="control-label col-sm-4" for="formula">
                                        <?php $this->renderView('partial/help.php', array(
                                                    'text' => $this->trans('This is the formula that change the price') . '<br/>' .
                                                    $this->trans('You can get the value of the fields using') . ' <br/>' .
                                                    '<b>$' . $this->getPluginShortCode() . '_n</b>'
                                                )
                                        ); ?> <?php echo $this->trans('Formula'); ?> <br/><br/>
                                        
                                        <b><?php echo $this->trans('wpc.formula.decimal.separator'); ?></b><br/>
                                        <b>$<?php echo $this->getPluginShortCode(); ?>_n</b>: <?php echo $this->trans('Value of the field'); ?><br/>
                                        <b>$price</b>: <?php echo $this->trans('Basic Price'); ?><br/>
                                        <b>a+b</b>: <?php echo $this->trans('Plus'); ?><br/>
                                        <b>a-b</b>: <?php echo $this->trans('Minus'); ?><br/>
                                        <b>a*b</b>: <?php echo $this->trans('Moltiplication'); ?><br/>
                                        <b>a/b</b>: <?php echo $this->trans('Division'); ?><br/>
                                        <b>a^b</b>: <?php echo $this->trans('Power'); ?><br/>
                                        <b>a!</b>: <?php echo $this->trans('Factorial'); ?><br/>
                                        <b>a%b</b>: <?php echo $this->trans('Module'); ?><br/>
                                        <b>sqrt</b>: <?php echo $this->trans('Square'); ?><br/>
                                        <b>cos(a)</b>: <?php echo $this->trans('Cosine'); ?><br/>
                                        <b>sin(a)</b>: <?php echo $this->trans('Sine'); ?><br/>
                                        <b>tan(a)</b>: <?php echo $this->trans('Tangent'); ?><br/>
                                        <b>sec(a)</b>: <?php echo $this->trans('Secant'); ?><br/>
                                        <b>csc(a)</b>: <?php echo $this->trans('Cosecant'); ?><br/>
                                        <b>cot(a)</b>: <?php echo $this->trans('Cotangent'); ?><br/>
                                        <b>abs(a)</b>: <?php echo $this->trans('Absolute Value'); ?><br/>
                                    </label>

                                    <div class="col-sm-8">
                                        <div class="formula-editor">
                                            <textarea id="calculatorFormula" class="form-control" style="height: 360px" name="formula"><?php echo $this->view['form']['formula']; ?></textarea>
                                            <button class="btn btn-default" id="addFieldFormula" type="button">
                                               <i class="fa fa-pencil"></i> <?php echo $this->trans('wpc.calculator.insert_field'); ?>
                                            </button>
                                        </div>
                                    </div>
                               </div>
                           <?php endif; ?>

                           <div class="form-group">
                               <label class="control-label col-sm-4" for="fields">
                                   <?php $this->renderView('partial/help.php', 
                                           array(
                                               'text' => $this->trans('To which products do you want to enable the calculator?') . "<br/>" .
                                                         '<b>' . $this->trans("Note: You can't use different calculators for the same product") . "</b><br/>"
                                           )
                                   ); ?> <?php echo $this->trans('Products'); ?>
                               </label>
                               
                               <div id="products_container" class="col-sm-8">
                                   <small><?php echo $this->trans('wpc.calculator.form.products.choose_products'); ?></small>
                                   <select class="wpc-multiselect" name="products[]" multiple="multiple">

                                        <?php foreach($this->view['products'] as $product): ?>
                                            <option value="<?php echo $product->get_id(); ?>" <?php if(in_array($product->get_id(), $this->view['form']['products'])){echo 'selected="selected"';} ?>>
                                                <?php echo $product->get_title(); ?>
                                            </option>
                                        <?php endforeach; ?>

                                   </select>
                                   
                                   <br/>
                                   
                                   <small><?php echo $this->trans('wpc.calculator.form.products.choose_product_categories'); ?></small>
                                   <select class="wpc-multiselect" name="product_categories[]" multiple="multiple">
                                        <?php foreach($this->view['productCategories'] as $productCategoryKey => $productCategoryName): ?>
                                            <option value="<?php echo $productCategoryKey; ?>" <?php if(in_array($productCategoryKey, $this->view['form']['product_categories'])){echo 'selected="selected"';} ?>>
                                                <?php echo $productCategoryName; ?>
                                            </option>
                                        <?php endforeach; ?>
                                   </select>
                               </div>
                           </div>

                           <div class="form-group">
                               <label class="control-label col-sm-4" for="fields">
                                   <?php $this->renderView('partial/help.php', array('text' => $this->trans('If you want to redirect the user directly to the checkout after user added a product to cart, set Yes'))); ?> <?php echo $this->trans('Redirect to checkout on Add to Cart'); ?>
                               </label>
                               <div class="col-sm-8">
                                   <select class="form-control" name="redirect">
                                       <option value="0" <?php if(empty($this->view['form']['redirect'])){echo 'selected="selected"';} ?>><?php echo $this->trans('No'); ?></option>
                                       <option value="1" <?php if($this->view['form']['redirect'] == 1){echo 'selected="selected"';} ?>><?php echo $this->trans('Yes'); ?></option>
                                   </select>
                               </div>
                           </div>

                           <div class="form-group">
                               <label class="control-label col-sm-4" for="fields">
                                   <?php $this->renderView('partial/help.php', array('text' => $this->trans('wpc.calculator.form.redirect.tooltip'))); ?> <?php echo $this->trans('wpc.calculator.form.redirect.label'); ?>
                               </label>
                               <div class="col-sm-8">
                                   <select class="form-control" name="empty_cart">
                                       <option value="0" <?php if(empty($this->view['form']['empty_cart'])){echo 'selected="selected"';} ?>><?php echo $this->trans('wpc.no'); ?></option>
                                       <option value="1" <?php if($this->view['form']['empty_cart'] == 1){echo 'selected="selected"';} ?>><?php echo $this->trans('wpc.yes'); ?></option>
                                   </select>
                               </div>
                           </div>
                            
                           <div class="form-group">
                               <label class="control-label col-sm-4" for="fields">
                                   <?php $this->renderView('partial/help.php', 
                                               array('text' => $this->trans('wpc.calculator.themes.tooltip'))
                                   ); ?> <?php echo $this->trans('Themes'); ?>
                               </label>
                               <div class="col-sm-8">
                                   <select class="form-control" name="theme">
                                       <option value=""><?php echo $this->trans('wpc.theme.default'); ?></option>
                                       <?php foreach($this->view['themes'] as $theme){ ?>
                                           <option value="<?php echo $theme['filename']; ?>" <?php if($theme['filename'] == $this->view['form']['theme']){echo 'selected="selected"';} ?>>
                                               <?php echo $theme['name']; ?>
                                           </option>
                                       <?php } ?>
                                   </select>
                               </div>
                           </div>

                           <div class="form-group">
                               <div class="col-sm-10 col-sm-offset-3 text-center">
                                   <button id="calculator_submit" type="button" class="btn btn-primary" <?php echo ($this->view['form']['system_created'] == true)?'disabled="disabled"':''; ?>>
                                       <i class="fa fa-floppy-o"></i> <?php echo $this->trans('wpc.save'); ?>
                                   </button>
                               </div>
                           </div>

                           <input type="hidden" name="field_orders" id="field_orders" value="" />
                           <input type="hidden" name="id" value="<?php echo $this->view['id']; ?>" />
                           <input type="hidden" name="type" value="<?php echo $this->view['type']; ?>" />
                           <input type="hidden" name="task" value="calculator" />

                        </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="addFieldFormulaModal" class="modal fade" role="dialog">
            <div class="modal-dialog">

                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title"><?php echo $this->trans('wpc.calculator.add_field_formula.title'); ?></h4>
                    </div>
                    <div class="modal-body">
                        <select id="addFieldFormulaModalSelect" class="form-control">
                            <option value=""><?php echo $this->trans('wpc.select'); ?></option>
                            <?php foreach($this->view['fields'] as $field): ?>
                                <option value="<?php echo "\${$this->getPluginShortCode()}_{$field->id}"; ?>">
                                    <?php echo htmlentities($field->label . " [" . $this->getPluginShortCode() . "_" . $field->id . "]"); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button id="addFieldFormulaModalAdd" type="button" class="btn btn-primary">
                            <?php echo $this->trans('wpc.add'); ?>
                        </button>

                        <button type="button" class="btn btn-default" data-dismiss="modal">
                            <?php echo $this->trans('wpc.close'); ?>
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

<?php $this->renderView('app/footer.php'); ?>