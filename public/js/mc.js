function applyJs(container) {
    // Select2 ajax
    container.find('.modal-content').find(".select2-ajax").each(function() {
        select2Ajax($(this));
    });
    
    // Select2
    container.find('.select').select2({
        minimumResultsForSearch: 101,
        templateResult: formatSelect2TextOption,
        templateSelection: formatSelect2TextSelected
    });
    
    // checkbox
    container.find(".styled").uniform({
        radioClass: 'choice'
    });
    
    // autofill
    container.find('.autofill').autofill();
    
    // inline edit
    container.find('.select-custom').select_custom();
    
    // unlimited check trigger
    container.find('.unlimited-check input').trigger("change");
    
    // radio box
    container.find(".control-radio .radio_box .main-control input:checked").parents('.main-control').trigger('click');
}