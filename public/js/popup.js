class Popup {
    constructor(url, callback, options) {
        var _this = this;
        this.id = '_' + Math.random().toString(36).substr(2, 9);
        this.options = {};
        this.popup = $('.popup[id='+this.id+']');
        this.loadingHtml = '<div class="popup-loading"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>';
        this.data = {};
        
        // url
        if (typeof(url) !== 'undefined') {
            this.url = url;
        }
        
        // callback
        if (typeof(callback) !== 'undefined') {
            this.callback = callback;
        }
        
        // options
        if (typeof(options) !== 'undefined') {
            this.options = options;

            // data            
            if (typeof(options.data) !== 'undefined') {
                this.data = options.data;
            }
        }
        
        if (!this.popup.length) {
            var popup = $('<div class="popup" id="'+this.id+'">').html('');
            $('body').append(popup);
            
            this.popup = popup;
            this.loading();
        }
        this.popup.css('display', 'none');
        
        //// click outside to close
        //$(".popup").click(function(e){
        //    if(e.target != this) return; // only continue if the target itself has been clicked
        //    // this section only processes if the .nav > li itself is clicked.
        //    Popup.hide();
        //});

        // onclose popup
        if(this.options.onclose != null) {
            this.onclose = this.options.onclose;
        }
    }
    
    show() {
        this.popup.fadeIn();
        $('html').css('overflow', 'hidden');
    }
    
    hide() {
        this.popup.fadeOut();
        $('html').css('overflow', 'auto');

        // onclose
        if (this.onclose != null) {
            this.onclose();
        }
    }
    
    loading() {
        this.popup.prepend(this.loadingHtml);
        this.popup.addClass('popup-is-loading');
    }

    loaded() {
        // apply js for new content
        this.applyJs();
        
        // remove loading effects
        this.popup.find('.popup-loading').remove();        
        this.popup.removeClass('popup-is-loading');
    }
    
    static hide() {
        $('.popup').fadeOut();
        $('html').css('overflow', 'auto');
    }
    
    applyJs() {
        var _this = this;
        
        // init js
        initJs(_this.popup);
        
        // set back button
        // back button
        if (typeof(_this.back) !== 'undefined') {
            _this.popup.find('.back').click( function() {                    
                _this.back();
            });
        } else {
            _this.popup.find('.back').click( function() {                    
                _this.hide();
            });
        }
        
        // click close button
        _this.popup.find(".close").click(function(){
            _this.hide();
        });
    }
    
    load(url, callback) {
        var _this = this;
        
        if (typeof(url) !== 'undefined') {
            this.url = url;
        }
        
        if (typeof(callback) !== 'undefined') {
            this.callback = callback;
        }
        
        this.loading();
        
        this.show();
        
        $.ajax({
            url: _this.url,
            type: 'GET',
            dataType: 'html',
            data: _this.data,
        }).done(function(response) {
            _this.popup.html(response);
            
            if (typeof(_this.callback) !== 'undefined') {
                _this.callback();
            }
            
            // after load
            _this.loaded();

            // // apply js
            // _this.applyJs();
        }).fail(function(jqXHR, textStatus, errorThrown){
            // for debugging
            alert(errorThrown);
            document.write(jqXHR.responseText);
        });
    }
    
    loadHtml(html) {
        var _this = this;
        
        _this.popup.html(html);
        
        // after load
        _this.loaded();

        // // apply js
        // _this.applyJs();
    }
}