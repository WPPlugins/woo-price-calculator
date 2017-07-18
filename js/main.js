jQuery(document).ready(function($){
    var requestAjaxCalculatePrice;
    
    WooPriceCalculator = {
        init: function(){
            
            if($('.wpc-cart-form').length){
                $('.wpc-cart-form').each(function(index, element){
                    $('.wpc-cart-edit', element).click(function(){
                        var productId           = $('.wpc_product_id', element).val();
                        var simulatorId         = $('.wpc_simulator_id', element).val();
                        var cartItemKey         = $(element).attr('data-cart-item-key');
                        var remodalInst         = $('[data-remodal-id="wpc_cart_item_' + cartItemKey + '"]').remodal();
                        var editButtons         = $('[data-remodal-target="wpc_cart_item_' + cartItemKey + '"]');
                        
                        //Evito che i prodotti si aggiungano automaticamente ad ogni calcolo (add-to-cart)
                        var data                = $(element).find(WooPriceCalculator.getFieldSelector(), element).serialize();
                        var quantity            = parseInt($("input[name='cart[" + cartItemKey + "][qty]']").val());
                    
                        $('.cart_item .product-price').html(WooPriceCalculator.htmlLoadingImage());
                        
                        WooPriceCalculator.ajaxEditCartItem(cartItemKey, productId, simulatorId, quantity, data);
                        remodalInst.close();
                    });
                });
            }
            
            /*
             * Inizializzazione dei componenti per data & ora
             */
            $('.wpc-field-widget').each(function(index, element){
                var fieldId             = $(element).attr('id');
                var fieldContainer      = $(".wpc-field", element);
                var options             = JSON.parse($('#' + fieldId + "_options").val());
                
                $(".woo_price_calc_date input", element).datetimepicker({
                    timepicker: false,
                    format: 'Y-m-d',
                    lazyInit: true,
                    validateOnBlur: false,
                    allowBlank: true,
                    scrollInput: false,
                    closeOnDateSelect: true,
                });
            
                $(".woo_price_calc_time input", element).datetimepicker({
                    datepicker: false,
                    format: 'H:i:s',
                    lazyInit: true,
                    validateOnBlur: false,
                    allowBlank: true,
                    scrollInput: false,
                });
                
                $(".woo_price_calc_datetime input", element).datetimepicker({
                    format: 'Y-m-d H:i:s',
                    lazyInit: true,
                    validateOnBlur: false,
                    allowBlank: true,
                    scrollInput: false,
                });
                
                if(fieldContainer.hasClass('woo_price_calc_numeric')){
                    var field               = $('input', fieldContainer);
                    var decimals            = options['numeric']['decimals'];
                    var decimalSeparator    = options['numeric']['decimal_separator'];

                    //decimal: false,
                    $(field).numeric({ 
                        decimalPlaces: decimals,
                        decimal: decimalSeparator,
                    });
                }
                
            });

            WooPriceCalculator.initFieldEvents();
            
            /*
             * Controllo qualsiasi richiesta ajax eseguita nel carrello.
             * In questo modo so se è stato aggiornato
             */
            setTimeout(function() { 
                $('.remodal').remodal(); 
            }, 500);
            
            if(WPC_HANDLE_SCRIPT.is_cart == true){
                $(document).ajaxComplete(function(event, xhr, settings) {
                    if($('.woocommerce .cart_item').length){
                        //Rinizializzo i modal
                        $('.remodal').remodal();  
                    }
                });
            }else{
                WooPriceCalculator.calculatePrice();
            }
            
        },
        
        getPluginDir: function(){
            return 'woo-price-calculator';
        },
        
        hidePrice: function(cartItemKey){
            var priceSelector       = WooPriceCalculator.getPriceSelector();
            
            if(cartItemKey != null){
                var cartModalContainer  = $('[data-cart-item-key="' + cartItemKey + '"]');
                
                $('.wpc-cart-item-price', cartModalContainer).hide();
                $('.wpc-cart-edit', cartModalContainer).prop('disabled', true);
            
            }else{
                $(priceSelector).hide();
            }
        },
        
        showPrice: function(cartItemKey){
            var priceSelector       = WooPriceCalculator.getPriceSelector();
            
            if(cartItemKey != null){
                var cartModalContainer  = $('[data-cart-item-key="' + cartItemKey + '"]');
                
                $('.wpc-cart-item-price', cartModalContainer).show();
                $('.wpc-cart-edit', cartModalContainer).prop('disabled', false);
            }else{
                $(priceSelector).show();
            }

        },
        
        setFieldError: function(element, error){
            $(element).html(error);
        },
        
        getPriceSelector: function(){
            /*
             * Bisogna evitare che il prezzo sia aggiornato dove non si deve
             * all'interno della stessa pagina
             */
            return '.product .summary .price, ' +
                   '.wpc-cart-form .price, ' +
                   '.product .price-box .amount';
        },
        
        getFieldSelector: function(){
            return '.wpc-field input, .wpc-field select';
        },
        
        htmlLoadingImage: function(){
            return "<img src=\"" + WPC_HANDLE_SCRIPT.siteurl + "/wp-content/plugins/" + WooPriceCalculator.getPluginDir() + "/assets/ajax-loader.gif\" />";
        },
        
        ajaxCalculatePrice: function(productId, simulatorId, cartItemKey, data, outputEl){

            WooPriceCalculator.showPrice(cartItemKey);
            $(outputEl).html(WooPriceCalculator.htmlLoadingImage());
            
            $(".wpc-field-error").html("");
            
            if(requestAjaxCalculatePrice && requestAjaxCalculatePrice.readyState != 4){
                requestAjaxCalculatePrice.abort();
            }
            
            requestAjaxCalculatePrice = $.ajax({
                method: "POST",
                url: WPC_HANDLE_SCRIPT.siteurl + "/wp-admin/admin-ajax.php?action=woopricesim_ajax_callback&id=" + productId + "&simulatorid=" + simulatorId,
                dataType: 'json',
                data: data,

                success: function(result, status, xhrRequest) {
                    if(result.errorsCount == 0){
                        $(outputEl).html(result.price);
                        $(outputEl).show();
                    }else{
                        WooPriceCalculator.hidePrice(cartItemKey);

                        $.each(result.errors, function(fieldId, fieldErrors){
                            $.each(fieldErrors, function(index, fieldError){
                                
                                if(cartItemKey != null){
                                    var cartModalContainer  = $('[data-cart-item-key="' + cartItemKey + '"]');
                                    var fieldContainer      = $("#" + fieldId, cartModalContainer);
                                }else{
                                    var fieldContainer      = $("form.cart #" + fieldId);
                                }
                                
                                var error               = $(".wpc-field-error", fieldContainer);

                                $(error).html(fieldError);
                                //console.log(fieldId + ": " + fieldError);
                            });
                        });
                    }
                },
                error: function(xhrRequest, status, errorMessage)  {
                   //alert("Sorry, an error occurred");
                   console.log("Error: " + errorMessage);
                }
           });
        },
        
        ajaxEditCartItem: function(cartItemKey, productId, simulatorId, quantity, data){
            $.ajax({
                method: "POST",
                url: WPC_HANDLE_SCRIPT.siteurl + "/wp-admin/admin-ajax.php?" +
                     "action=woopricesim_ajax_callback&id=" + productId + 
                     "&simulatorid=" + simulatorId + 
                     "&wpc_action=edit_cart_item" +
                     "&cart_item_key=" + cartItemKey + 
                     "&quantity=" + quantity,
             
                data: data,

                success: function(result, status, xhrRequest){
                    location.reload();
                    
                    //console.log(result);
                },
                error: function(xhrRequest, status, errorMessage)  {
                   console.log("Error: " + errorMessage);
                }
           });
        },
                
        wooCommerceUpdateCart: function(){
            $('[name="update_cart"]').trigger('click');
        },
        
        calculatePrice: function(){
            /* Si potrebbe anche fare che sia l'utente ad impostare la classe di cambio del prezzo, nel caso sia utilizzati plugin che modificano la parte del prezzo */
            if(WPC_HANDLE_SCRIPT.is_cart == true){
                if($('.wpc-cart-form').length){
                    WooPriceCalculator.calculateCartPrice();
                }
            }else{
                if($('.wpc-product-form').length){
                    WooPriceCalculator.calculateProductPrice();
                }
            }

        },
                
        calculateCartPrice: function(){
            var element             = window.wpcCurrentCartItem;
            var productId           = $('.wpc_product_id', element).val();
            var simulatorId         = $('.wpc_simulator_id', element).val();
            //Evito che i prodotti si aggiungano automaticamente ad ogni calcolo (add-to-cart)
            var data                = $(element).find(WooPriceCalculator.getFieldSelector(), element).serialize();
            var cartItemKey         = $(element).attr('data-cart-item-key');

            //console.log(data);

            WooPriceCalculator.ajaxCalculatePrice(productId, simulatorId, cartItemKey, data, $('.price', element).first());

        },
        
        calculateProductPrice: function(){
            var productId           = $('form.cart .wpc_product_id').val();
            var simulatorId         = $('form.cart .wpc_simulator_id').val();
            var priceSelector       = WooPriceCalculator.getPriceSelector();

            //Evito che i prodotti si aggiungano automaticamente ad ogni calcolo (add-to-cart)
            var data                = $('form.cart .wpc-product-form').find(WooPriceCalculator.getFieldSelector()).serialize();
            WooPriceCalculator.ajaxCalculatePrice(productId, simulatorId, null, data, $(priceSelector));
        },
        
        initFieldEvents: function(){
            var timeout             = false;
            var writingTimeout      = 250;
            
            if(WPC_HANDLE_SCRIPT.is_cart == true){
                $(document).on('opening', '.remodal', function (){
                    window.wpcCurrentCartItem    = $(this);
                    WooPriceCalculator.calculateCartPrice();
                    
                    //console.log('Cart Item has been opened: ' + $(this).attr('data-cart-item-key'));
                });
            }
            
            $(document).on('keyup', '.woo_price_calc_numeric input', function(){
                if(timeout){ 
                    clearTimeout(timeout); 
                }

                timeout = setTimeout(function () {
                      WooPriceCalculator.calculatePrice();
                }, writingTimeout);
            });
            
            /* Per gli elementi di tipo Range */
            $(document).on('change', '.woo_price_calc_numeric input[type=range]', function(){
                if(timeout){ 
                    clearTimeout(timeout); 
                }

                timeout = setTimeout(function () {
                      WooPriceCalculator.calculatePrice();
                }, writingTimeout);
            });
                        
            $(document).on('keyup', '.woo_price_calc_text input', function(){
                if(timeout){ 
                    clearTimeout(timeout); 
                }

                timeout = setTimeout(function () {
                      WooPriceCalculator.calculatePrice();
                }, writingTimeout);
            });
            
            $(document).on('change', '.woo_price_calc_picklist select', function(){
                WooPriceCalculator.calculatePrice();
            });

            $(document).on('change', '.woo_price_calc_radio input', function(){
                WooPriceCalculator.calculatePrice();
            });

            $(document).on('change', '.woo_price_calc_checkbox input', function(){
                WooPriceCalculator.calculatePrice();
            });

        }
    };
    
    WooPriceCalculator.init();

});