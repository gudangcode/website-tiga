class List {
    constructor(selector, options) {
        this.page = 0;
        this.per_page = 10;
        this.data = function() { return {}; };
        this.list = selector;
        this.loadingHtml = '<div class="list-loading"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>';
        
        if (options !== null) {
            this.options = options;
        }
        
        // url
        if (this.options.url !== null) {
            this.url = this.options.url;
        }
        
        // page
        if (this.options.page !== null) {
            this.page = this.options.page;
        }
        
        // per_page
        if (this.options.per_page !== null) {
            this.per_page = this.options.per_page;
        }
        
        // data        
        if (this.options.data !== null && typeof(this.options.data) != 'undefined') {
            this.data = this.options.data;
        }
    }
    
    loading() {
        if (!this.list.find('.list-loading').length) {
            this.list.prepend(this.loadingHtml);
        }
        
        this.list.addClass('list-is-loading');
    }
    
    loaded() {
        var _this = this;
        
        // apply js for new content
        initJs(_this.list);
        
        // remove loading effects
        _this.list.find('.list-loading').remove();        
        _this.list.removeClass('list-is-loading');
        
        // page link
        _this.list.find('.page-link').click(function() {
            var page = $(this).attr('data-page');					
            _this.page = page;
            _this.load();
        });
    }

    load(url) {
        var _this = this;

        if (url != null) {
            _this.url = url;
        }
        
        this.loading();
        
        // post data
        var data = {
            _token: CSRF_TOKEN,
            page: _this.page,
            per_page: _this.per_page,
        };
        
        $.extend( data, _this.data() );
        
        if(_this.xhr && _this.xhr.readyState != 4) {
            _this.xhr.abort();
            this.loading();
        }
        _this.xhr = $.ajax({
            url: _this.url,
            type: 'POST',
            dataType: 'html',
            data: data,
        }).always(function(response) {
            _this.list.html(response);
            
            // scroll top
            _this.list.animate({scrollTop: 0});
            
            // done
            _this.loaded();
        });
    }
}