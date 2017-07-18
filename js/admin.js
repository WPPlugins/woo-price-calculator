jQuery(document).ready(function($){
    var selected_cell           = null;
    var sortableEditElement     = null;
    
    var sortableItems           = null;
    var sortableItemsData       = null;
    var language                = $('html').attr('lang');
    
    /*
     * Solo se la lingua esiste
    */
    if(language == "it-IT"){
        $('.wsf-bs .data-table').DataTable({
            "language": {
                "url": WPC_HANDLE_SCRIPT.siteurl + "/wp-content/plugins/woo-price-calculator/lib/DataTables-1.10.12/lang/" + language + ".json"
            }
        });
    }else{
        $('.wsf-bs .data-table').DataTable();
    }

    
     $('.wsf-bs-tooltip').tooltip(); 
     
    $(document).mousedown( function(e) {
       mouseX = e.pageX; 
       mouseY = e.pageY;
    });  

    $('.woo-price-calculator-tooltip').tooltipster({
        animation: 'fade',
         contentAsHTML: true,
        multiple: true,
        theme: 'tooltipster-shadow',
        touchDevices: true
    });

    $('.wpc-multiselect').multiSelect({ keepOrder: true });
    
    /* Abilita il sortable nel multiselect */
    $("#field_container div.ms-selection ul.ms-list").sortable({helper: 'clone'});

    $('#calculator_submit').click(function(){

        $('#field_orders').val($('#field_container .ms-elem-selection.ms-selected span').map(function() {
            var value   = $(this).html();
            
            var regExp = /\[([^)]+)\]/;
            var matches = regExp.exec(value);
            
            if(matches != null){
                var fieldId = matches[1].replace('woo_price_calc_', '');
            }
            
            return fieldId;
        }).get().join(','));

        $('#calculator_form').submit();
    });
    
    $('#wpc_load_mapping_button').click(function(){
        $('#cell_next_form').submit();
    });

    $('#field_regex_modal_ok').click(function(){
        $("#text_regex").val($("#field_regex_modal_list").val());
        
        $("#field_regex_modal").modal('hide');
    });
    
    wpcInitCellMapping();
    wpcInitFields();
    wpcInitCalculator();
    
    /* Inizializzazione delle liste ordinabili (Campi Picklist e Radio) */
    wpcSortableElement("#picklist_items_sortable", "#picklist_items", "#items_list_id");
    wpcSortableElement("#radio_items_sortable", "#radio_items", "#items_list_id");
    wpcSortableEvents("#items_list_id");
    
    function wpcUpdateSortableData(selector, dataSelector, idSelector){
        $(dataSelector).val("[]");
        
        $(selector + ' li').each(function(i){
            var id          = $(this).attr('data-id');
            var label       = $(this).attr('data-label');
            var value       = $(this).attr('data-value');

            if(value == '' || value == undefined){
                value = label;
            }

            var data    = JSON.parse($(dataSelector).val());

            data.push({
                "id": id,
                "label": label,
                "value": value,
            });
            
            $(dataSelector).val(JSON.stringify(data));
        });
    }
    
    function wpcSortableEvents(idSelector){
        $('#field_list_modal_ok').click(function(){
            var id          = $("#field_list_modal_id").val();
            var label       = $("#field_list_modal_label").val();
            var value       = $("#field_list_modal_value").val();

            if(value == '' || value == undefined){
                value = label;
            }
            
            if(label == ''){
                alert('Error: Label can\'t be null');
            }else{
            
                if($("#field_list_mode").val() == "add"){
                    var el = document.createElement('li');
                   
                    $(el).attr('data-id', $(idSelector).val());
                    $(el).attr('data-value', value);
                    $(el).attr('data-label', label);

                    el.innerHTML = '<a class="btn btn-danger js-remove">' +
                                        '<i class="fa fa-times"></i>' +
                                    '</a> ' + 
                                    '<a class="btn btn-primary sortable-edit">' +
                                        '<i class="fa fa-pencil"></i>' +
                                    '</a> ' + label + ' <i>[Value: ' + value + ']</i>';

                    $(sortableItems).append(el);
                    
                    $(idSelector).val(parseInt($(idSelector).val())+1);
                    
                }else if($("#field_list_mode").val() == "edit"){
                    
                    $(sortableEditElement).attr('data-id', id);
                    $(sortableEditElement).attr('data-value', value);
                    $(sortableEditElement).attr('data-label', label);

                    $(sortableEditElement).html('<a class="btn btn-danger js-remove">' +
                                                    '<i class="fa fa-times"></i>' +
                                                '</a> ' + 
                                                '<a class="btn btn-primary sortable-edit">' +
                                                    '<i class="fa fa-pencil"></i>' +
                                                '</a> ' + label + ' <i>[Value: ' + value + ']</i>');

                }

                wpcUpdateSortableData(sortableItems, sortableItemsData);

                $('#field_list_modal').modal('hide');
            }
        });
    }
    
    function wpcSortableElement(selector, dataSelector, idSelector){

        if($(selector).get(0) != undefined){
            var sortableList = Sortable.create($(selector).get(0), {
                animation: 150,
                filter: '.js-remove',

                onFilter: function (evt){
                    evt.item.parentNode.removeChild(evt.item);
                    wpcUpdateSortableData(selector, dataSelector, idSelector);
                },

                onAdd: function (evt) {
                    wpcUpdateSortableData(selector, dataSelector, idSelector);
                },

                onEnd: function(evt){
                    wpcUpdateSortableData(selector, dataSelector, idSelector);
                },

            });

            $('.field_list_add').click(function(){

                $("#field_list_mode").val("add");

                sortableItems       = $(this).attr("data-sortable-items");
                sortableItemsData   = $(this).attr("data-sortable-items-data");
                
                $("#field_list_modal_id").val("");
                $("#field_list_modal_label").val("");
                $("#field_list_modal_value").val("");

                $('#field_list_modal').modal('show');
            });

            $(document).on('click', selector + " .sortable-edit", function(){
                $("#field_list_mode").val("edit");
                
                sortableItems       = $(this).attr("data-sortable-items");
                sortableItemsData   = $(this).attr("data-sortable-items-data");
                
                var element         = $(this).parent();
                sortableEditElement = element;

                var id              = $(element).attr('data-id');
                var label           = $(element).attr('data-label');
                var value           = $(element).attr('data-value');

                if(value == '' || value == undefined){
                    value = label;
                }

                $("#field_list_modal_id").val(id);
                $("#field_list_modal_label").val(label);
                $("#field_list_modal_value").val(value);

                $('#field_list_modal').modal('show');
            });
        }

    }
    
    function wpcInitCellMapping(){
        
        $(document).ready(function(){
           if($("[data-type='output']").length){
               $("#cell_type_output_div").hide();
           }
           
           if($("[data-type='price']").length){
               $("#cell_type_price_div").hide();
           }
           
           $("[data-type='input']").each(function(index, element){
               var fieldId                  = $(element).attr("data-field-id");
               var listInputFieldsElement   = $("#cell_type_select option[value='" + fieldId + "']");
           });
           
        });
        
        $('.cell').click(function(){
            
            if($(this).hasClass('disabled') == false){
                $('.cell').removeClass('cell_not_selected');
                $('.cell').removeClass('cell_selected');

                if($(this).hasClass('cell_type_selected')){
                    $("#cell_type_none_div").show();
                }else{
                    $("#cell_type_none_div").hide();
                    $(this).addClass('cell_selected');
                }

                selected_cell = $(this);

                $('#cell_type').css({'top':mouseY - 20,'left':mouseX - $('#cell_type').width()/2});

                /*
                 * Se non è possibile nessuna opzione nascondo il form e mostro
                 * un messaggio; altrimenti mostro il form e nascondo il messaggio
                 */
                if($("#cell_type_none_div").css('display') == 'none' &&
                   $("#cell_type_input_div").css('display') == 'none' &&
                   $("#cell_type_output_div").css('display') == 'none'){

                    $("#cell_type_form").hide();
                    $("#cell_type_no_content").show();

                }else{
                    $("#cell_type_form").show();
                    $("#cell_type_no_content").hide();
                }
                    //alert($('#cell_type').height()/2);

                if(selected_cell.attr('data-type') == "input"){
                    $('#cell_type_select').val(selected_cell.attr('data-field-id'));
                    $("#cell_type_input").prop("checked", true);
                }else if(selected_cell.attr('data-type') == "output"){
                    $("#cell_type_output").prop("checked", true);
                }else if(selected_cell.attr('data-type') == "price"){
                    $("#cell_type_price").prop("checked", true);
                }else{
                    $("#cell_type_none").prop("checked", true);
                }
                
                $('#cell_type').show();
            }
            
         });
    
        $('#cell_type_submit').click(function(){
            $('#cell_type').hide();
            
            selected_mode                = $('input[name=cell_type_mode]:checked', '#cell_type_form').val();
            coordinates                  = $(selected_cell).attr('data-coordinates');

            if(selected_mode == "none"){
                field_id                     = $(selected_cell).attr('data-field-id');
                
                if($(selected_cell).attr('data-type') == "output"){
                    cellResetOutput();
                }else if($(selected_cell).attr('data-type') == "input"){
                    cellResetInput(selected_cell);
                }else if($(selected_cell).attr('data-type') == "price"){
                    cellResetPrice();
                }
                
                /* Cancello i campi data e tolgo la selezione */
                $(selected_cell).attr('data-type', "");
                $(selected_cell).attr('data-field-id', "");
                $(selected_cell).removeClass('cell_type_selected');
                
                /* Cancello anche l'hidden */
                $("#cell_next_form input[value='" + coordinates + "']").remove();
                
            }else if(selected_mode == "input"){
                $(selected_cell).addClass('cell_type_selected');

                field_id                   = $('#cell_type_select').val();
                var listInputFieldsElement = $("#cell_type_select option[value='" + field_id + "']");
                
		/* Cancello campi con stesse coordinate */
                $(".mapping_fields").each(function(index, element){
                    if(coordinates == $(element).val()){
                        $(element).remove();
                    }
                });
                
		/* Aggiungo il campo coordinate */
                $("#cell_next_form").append('<input class="mapping_fields" type="hidden" id="field_' + field_id + '[]" name="field_' + field_id + '[]" value="' + coordinates + '" />');

                if($(selected_cell).attr('data-type') == "output"){
                    cellResetOutput();
                }else if($(selected_cell).attr('data-type') == "price"){
                    cellResetPrice();
                }
                
                /* Memorizzo nella cella l'ID del campo */
                $(selected_cell).attr('data-type', "input");
                $(selected_cell).attr('data-field-id', field_id);

            }else if(selected_mode == "output"){
                $(selected_cell).addClass('cell_type_selected');

                $("#cell_type_output_div").hide();
                $("#cell_next_form").append('<input type="hidden" id="output" name="output" value="' + coordinates + '" />');
                
                //alert($(selected_cell).attr('data-type'));
                
                if($(selected_cell).attr('data-type') == "input"){
                    cellResetInput(selected_cell);
                }else if($(selected_cell).attr('data-type') == "price"){
                    cellResetPrice();
                }
                
                $(selected_cell).attr('data-type', "output");
                
            }else if(selected_mode == "price"){

                $(selected_cell).addClass('cell_type_selected');
                
                $("#cell_type_price_div").hide();
                $("#cell_next_form").append('<input type="hidden" id="price" name="price" value="' + coordinates + '" />');
                
                if($(selected_cell).attr('data-type') == "input"){
                    cellResetInput(selected_cell);
                }else if($(selected_cell).attr('data-type') == "output"){
                    cellResetOutput();
                }
                
                $(selected_cell).attr('data-type', "price");
                
            }

           /* Nascondo la selezione del campo di input se non ci sono più campi
            * da selezionare */
           if($('#cell_type_select option').size() <= 0){
               $('#cell_type_input_div').hide();
           }else{
               $('#cell_type_input_div').show();
           }

           $("#cell_type_input").prop("checked", true);

           //alert($("#cell_next_form").html());
        });
    }
    
    function cellResetInput(selected_cell){
        field_id                     = $(selected_cell).attr('data-field-id');

        $("#cell_type_input_div").show();

        $("#field_" + field_id).remove();
    }
    
    function cellResetOutput(){
        $("#output").remove();
        $("#cell_type_output_div").show();
        
    }
    
    function cellResetPrice(){
        $("#price").remove();
        $("#cell_type_price_div").show();
        
    }
    
    function wpcInitFields(){
        jQuery(".wpc-numeric").numeric({decimal: false});
        
        jQuery(".wpc-numeric-decimals").numeric({
            decimal: ".",
        });

        wpcShowFieldPanel(jQuery('#field_type').val());

        jQuery('#field_type').change(function() {
            wpcShowFieldPanel(jQuery(this).val());

        });
        
        /* Visualizzo il modal per aggiungere le voci */
        
        /*
        $('#wpc_options_radio_add').click(function(){
            window.wpcShowFieldChoiseMode = "add";
            wpcShowFieldChoiseModal("wpc_options_radio_list");
        });
        
        $('#wpc_options_radio_edit').click(function(){
            if($("#wpc_options_radio_list option:selected").length){
                window.wpcShowFieldChoiseMode = "edit";
                wpcShowFieldChoiseModal("wpc_options_radio_list");
            }else{
                alert("Error: Please select an item");
            }

        });
        
        $('#wpc_options_radio_remove').click(function(){
            $("#wpc_options_radio_list option:selected").remove();
        });
        
        $('#field_choise_modal_ok').click(function(){
            var value   = $("#field_choise_modal_value").val();
            var text    = $("#field_choise_modal_text").val();
            
            if(text == ""){
                alert("Error: Text is mandatory");
            }else{
            
                if(window.wpcShowFieldChoiseMode == "add"){
                    $('#wpc_options_radio_list').append($('<option>', {
                        value:  value,
                        text:   text
                    }));

                    $('#field_choise_modal').modal("hide"); 

                }else if(window.wpcShowFieldChoiseMode == "edit"){
                    $("#wpc_options_radio_list option:selected").text(text);
                    $("#wpc_options_radio_list option:selected").val(value);

                    $('#field_choise_modal').modal("hide"); 
                }
            }
        });
        */
       
        $("#wpc_field_form_submit").click(function(){
            //$("#wpc_options_radio_list option").prop('selected', true);
            $("#wpc_field_form").submit();
        });
    
    }
    
    function wpcShowFieldChoiseModal(listSelector){
       if(window.wpcShowFieldChoiseMode == "add"){
           $("#field_choise_modal_text").val("");
           $("#field_choise_modal_value").val("");
       }else if(window.wpcShowFieldChoiseMode == "edit"){
           $("#field_choise_modal_text").val($("#" + listSelector + " option:selected").text());
           $("#field_choise_modal_value").val($("#" + listSelector + " option:selected").val());
       }
   
       $('#field_choise_modal').modal("show"); 
    }
    
    function wpcShowFieldPanel(value){
        jQuery("#checkbox_options").hide();
        jQuery("#picklist_options").hide();
        jQuery("#numeric_options").hide();
        jQuery("#text_options").hide();
        jQuery("#radio_options").hide();
        
        if(value == "checkbox"){
            jQuery("#checkbox_options").show();
        }else if(value == "picklist"){
            jQuery("#picklist_options").show();
        }else if(value == "numeric"){
            jQuery("#numeric_options").show();
        }else if(value == "text"){
            jQuery("#text_options").show();
        }else if(value == "radio"){
            jQuery("#radio_options").show();
        }
    }

    function wpcInitCalculator(){
        
        $('#addFieldFormulaModalAdd').click(function(){
            insertAtCursor("calculatorFormula", $("#addFieldFormulaModalSelect").val());
            
            $('#addFieldFormulaModal').modal('hide');
        });

        $('#addFieldFormula').click(function(){
            
            /* Controllo che si possano aggiungere solo i campi inseriti */
            $("#addFieldFormulaModalSelect option").each(function(){
                var option          = $(this);
                var selectValue     = $(this).val();
                var found           = false;
                
                $('#fields :selected').each(function(i, selected){
                    if(selectValue == "$woo_price_calc_" + $(selected).val()){
                        found = true;
                    }
                });
                
                if(found == true){
                    option.show();
                }else{
                    option.hide();
                }
            
            });


            
            $('#addFieldFormulaModal').modal('show');
        })

    }

    function insertAtCursor(myField, myValue){
        myField = document.getElementById(myField);
        
        //IE support
        if (document.selection) {
            myField.focus();
            sel = document.selection.createRange();
            sel.text = myValue;
        }
        //MOZILLA and others
        else if (myField.selectionStart || myField.selectionStart == '0') {
            var startPos = myField.selectionStart;
            var endPos = myField.selectionEnd;
            myField.value = myField.value.substring(0, startPos)
                + myValue
                + myField.value.substring(endPos, myField.value.length);
        } else {
            myField.value += myValue;
        }
    }



});
