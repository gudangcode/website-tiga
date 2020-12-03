$.fn.inline_edit = function(action, param) {
    $(this).each(function() {
        var box = $(this);
        var type = box.attr('data-type');
        if (typeof(type) === 'undefined') {
            type = 'text';
        }
        box.val = box.find('val');
        box.value = function() {
            return box.val.html().trim();
        };
        
        // type == number
        if (type === 'number') {
            box.val.on('click', function() {
                
            });
        }
    });
};