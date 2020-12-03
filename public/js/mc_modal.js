function fitnessCalc() {
    var sum = 0;
    $('#myModal .modal-content').find("input.slider").each(function() {
        var number = Math.round(parseFloat($(this).closest('li').find('.val').html()));
        sum = sum + number;
    });
    $('#myModal .modal-content').find("input.slider").each(function() {
        var number = Math.round(parseFloat($(this).closest('li').find('.val').html()));
        var percent = Math.round((number/sum)*100);
        
        $(this).closest('li').find('.percent').html(percent);
    });
}

$(function() {
    $(document).on('click', '.mc-modal-control', function(e) {
        e.preventDefault();
        $('.modal-content').addClass('hienra');
        $('.wraper-frontend').addClass('display');
        
        var size = $(this).attr('modal-size');
        var url = $(this).attr('href');
        var method = $(this).attr('data-method');
        var load = htmlLoader();
        
        var modal = $('#myModal');
        modal.remove();
        var html = '<div class="mc-modal modal fade" role="dialog" id="myModal">'+
                        '<div class="modal-dialog modal-' + size + ' modal-select">'+
                            '<div class="modal-content">'+
                                '<p>'+load+'</p>'+    
                            '</div>'+
                        '</div>'+
                    '</div>';
        
        $('body').append(html);
        modal = $('#myModal');                
            modal.modal('show');
        
        $.ajax({
            url: url,
            type: method,
            dataType: 'html',
        }).always(function(response) {
            $('#myModal .modal-content').html(response);
            // Select2 ajax
            $('#myModal .modal-content').find(".select2-ajax").each(function() {
                select2Ajax($(this));
            });
            
            customValidate($('#myModal .modal-content').find('form'));
            // slider
            $('#myModal .modal-content').find("input.slider").slider();
            $('#myModal .modal-content').find("input.slider").on("slide", function(slideEvt) {
                $(this).closest('li').find('.val').html(slideEvt.value);
                fitnessCalc();
            });
            fitnessCalc();
            
            // uniform
            $('#myModal .modal-content').find(".styled, .multiselect-container input").uniform({
                radioClass: 'choice'
            });
        });
        return false;
    });
    
    $('.icon-rm').click(function(){
        $('.modal').addClass('andi');
    });
});

function htmlLoader() {
    //return '<div class="loader-outer"><div class="loader"><div class="ball-pulse"><div></div><div></div><div></div></div></div></div>';
    //return '<div class="spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div>';
    var khoi =  '<div class="sk-fading-circle">'+
                '<div class="sk-circle1 sk-circle"></div>'+
                '<div class="sk-circle2 sk-circle"></div>'+
                '<div class="sk-circle3 sk-circle"></div>'+
                '<div class="sk-circle4 sk-circle"></div>'+
                '<div class="sk-circle5 sk-circle"></div>'+
                '<div class="sk-circle6 sk-circle"></div>'+
                '<div class="sk-circle7 sk-circle"></div>'+
                '<div class="sk-circle8 sk-circle"></div>'+
                '<div class="sk-circle9 sk-circle"></div>'+
                '<div class="sk-circle10 sk-circle"></div>'+
                '<div class="sk-circle11 sk-circle"></div>'+
                '<div class="sk-circle12 sk-circle"></div>'+
          '</div>';
    return khoi;
}