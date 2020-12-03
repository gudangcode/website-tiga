$.fn.select_custom = function() {
    $(this).each(function() {
        var box = $(this);
        var url = box.attr('data-url');
        var trigger = box.find('.modal-trigger');
        var content = function() {
            return box.html();
        };
        var selectbox = box.find('select');
        
        var load = htmlLoader();
        var showModal = function() {
            var modal = $('#modalEdit');
            modal.remove();
            
            var html = '<div class="mc-modal modal fade" role="dialog" id="modalEdit">'+
                        '<div class="modal-dialog modal-lg modal-select">'+
                            '<div class="modal-content">'+
                                '<p>'+load+'</p>'+    
                            '</div>'+
                        '</div>'+
                    '</div>';
        
            $('body').append(html);
            modal = $('#modalEdit');                
            modal.modal('show');
            
            // Get all inputs values
            var data = {};
            box.find(':input').each(function() {
                var type = $(this).prop("type");
        
                // checked radios/checkboxes
                if ((type == "checkbox" || type == "radio") && this.checked) { 
                   data[$(this).prop("name")] = $(this).val();
                }
                // all other fields, except buttons
                else if (type != "button" || type != "submit") {
                    data[$(this).prop("name")] = $(this).val();
                }
            })
            
            $.ajax({
                url: url,
                type: 'GET',
                dataType: 'html',
                data: data,
            }).always(function(response) {
                modal.find('.modal-content').html(response);
                
                // Select2
                modal.find('select').select2({
                    minimumResultsForSearch: 101,
                    templateResult: formatSelect2TextOption,
                    templateSelection: formatSelect2TextSelected
                });
                
                // switchery
                modal.find('.styled').uniform({
                    radioClass: 'choice'
                });
                
                // Numberic input
                modal.find(".numeric").numeric();
                
                // trigger modal form submit
                modal.find("form").submit(function( event ) {
                    $.ajax({
                        url: modal.find("form").prop('action'),
                        type: modal.find("form").prop('method'),
                        dataType: 'html',
                        data: modal.find("form").serialize(),
                    }).always(function(response) {
                        box.html(response);
                        
                        box.find('.modal-trigger').on('click', function() {
                            showModal();
                        });
                        
                        // Select2
                        box.find('select').select2({
                            minimumResultsForSearch: 101,
                            templateResult: formatSelect2TextOption,
                            templateSelection: formatSelect2TextSelected
                        });
                        
                        box.find('select').on('change', function() {
                            var value = $(this).val();
                            if (value == 'custom') {
                                showModal();
                            }
                        });
                    });
                    
                    modal.modal('hide');
                    
                    event.preventDefault();
                });
            });
        };
        
        trigger.on('click', function() {
            showModal();
        });
        
        selectbox.on('change', function() {
            var value = $(this).val();
            if (value == 'custom') {
                showModal();
            }
        });
    });
};