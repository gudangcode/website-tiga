class Dialog {
    constructor(type, options) {
        var _this = this;
        this.dialog = $('.dialog');
        this.options = {};
        this.loadingHtml = '<div class="dialog-loading"><div class="lds-ellipsis"><div></div><div></div><div></div><div></div></div></div>';
        
        if (type == null) {
            this.type = type;
        }
        
        // options
        if (options != null) {
            this.options = options;
        }

        // on ok
        if (this.options.ok != null) {
            this.ok = this.options.ok;
        }

        // on cancel
        if (this.options.cancel != null) {
            this.cancel = this.options.cancel;
        }

        // on close
        if (this.options.close != null) {
            this.close = this.options.close;
        }

        // title
        if (this.options.title != null) {
            this.title = this.options.title;
        }

        // message
        if (this.options.message != null) {
            this.message = this.options.message;
        }
        
        // remove and create new one
        this.dialog.remove();
        var dialog = $('<div class="dialog">').html(this.renderHtml(type));
        $('body').append(dialog);        
        this.dialog = dialog;
        this.dialog.css('display', 'none');
        
        // show dialog
        this.load();
    }
    
    renderHtml(type) {
        switch(type) {
            case "confirm":
                if (this.title == null) {
                    this.title = LANG_ARE_YOU_SURE;
                }
                return `
                    <div class="dialog-container">
                        <div class="dialog-header">
                            <h2 class="title">`+this.title+`</h2>
                            <i class="lnr lnr-cross close"></i>
                        </div>
                        <div class="dialog-body">
                            `+this.message+`
                        </div>
                        <div class="dialog-footer">
                            <button class="btn btn-primary dialog-ok mr-2">`+LANG_OK+`</button>
                            <button class="btn btn-outline-secondary dialog-cancel mr-2">`+LANG_CANCEL+`</button>
                        </div>
                    </div>
                `;
                case "alert":
                    if (this.title == null) {
                        this.title = LANG_ALERT;
                    }
                    return `
                        <div class="dialog-container">
                            <div class="dialog-header">
                                <h2 class="title d-flex align-items-center">
                                    <i class="material-icons-outlined alert-icon mr-2">notifications</i>
                                    <span>`+this.title+`</span>
                                </h2>
                                <i class="lnr lnr-cross close"></i>
                            </div>
                            <div class="dialog-body">
                                `+this.message+`
                            </div>
                            <div class="dialog-footer">
                                <button class="btn btn-primary dialog-ok mr-2">`+LANG_OK+`</button>
                            </div>
                        </div>
                    `;
                case "notification":
                    if (this.title == null) {
                        this.title = LANG_ALERT;
                    }
                    return `
                        <div class="dialog-container dialog-large">
                            <div class="dialog-body p-5">
                                <h2 class="title d-flex align-items-center mb-3">
                                    <span>`+this.title+`</span>
                                </h2>
                                `+this.message+`
                                <div>
                                    <button class="btn btn-info dialog-ok mr-2 mt-4 px-5">`+LANG_OK+`</button>
                                </div>
                            </div>
                        </div>
                    `;
            case y:
              // code block
              break;
            default:
              // code block
        }
    }
    
    show() {
        this.dialog.fadeIn();
        $('html').css('overflow', 'hidden');
    }
    
    hide() {
        this.dialog.fadeOut();
        $('html').css('overflow', 'auto');
    }
    
    loading() {
        this.dialog.prepend(this.loadingHtml);
    }
    
    static hide() {
        $('.dialog').fadeOut();
        $('html').css('overflow', 'auto');
    }
    
    applyJs() {
        var _this = this;
        
        // init js
        initJs(_this.dialog);

        // click close button
        _this.dialog.find(".close").click(function() {
            if (_this.close != null) {
                _this.close(_this);
            }

            _this.hide();
        });
        
        // click close button
        _this.dialog.find(".dialog-cancel").click(function() {
            if (_this.cancel != null) {
                _this.cancel(_this);
            }

            _this.hide();
        });
        
        // click ok button
        _this.dialog.find(".dialog-ok").click(function() {
            if (_this.ok != null) {
                _this.ok(_this);
            }
            
            _this.hide();
        });
    }
    
    load(message) {
        var _this = this;
        
        if (typeof(message) !== 'undefined') {
            _this.message = message;
        }
        _this.show();
        
        _this.dialog.find('.dialog-body').html(message);
        
        // apply js for new content
        _this.applyJs();
    }
    
    loadHtml(html) {
        var _this = this;
        
        _this.dialog.html(html);
        
        _this.applyJs();
    }
}

