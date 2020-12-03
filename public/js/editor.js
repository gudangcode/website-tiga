$(document).ready(function() {
    tinymce.init({
        selector: '.full-editor',
        height: 500,
        convert_urls: false,
        remove_script_host: false,
        forced_root_block: "",
        skin: "oxide-dark",
        valid_elements : '*[*],meta[*]',
        valid_children: '+h1[div],+h2[div],+h3[div],+h4[div],+h5[div],+h6[div],+a[div]',
        extended_valid_elements : "meta[*]",
        valid_children : "+body[meta],+div[h2|span|meta|object],+object[param|embed]",
        plugins: [
          'table advlist autolink lists link image charmap print preview anchor directionality',
          'searchreplace visualblocks code fullscreen textcolor',
          'insertdatetime media contextmenu paste code'
        ],
        toolbar: 'insertfile undo redo | fontselect | fontsizeselect | styleselect | bold italic | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | ltr rtl',
        content_css: [
          APP_URL.replace('/index.php','')+'/css/all.css',
          APP_URL.replace('/index.php','')+'/css/page.css',
        ],

        external_filemanager_path:APP_URL.replace('/index.php','')+"/filemanager2/",
        filemanager_title:"Responsive Filemanager" ,
        external_plugins: { "filemanager" : APP_URL.replace('/index.php','')+"/filemanager2/plugin.min.js"}
    });

    tinymce.init({
        selector: '.email-editor',
        height: 500,
        convert_urls: false,
        remove_script_host: false,
        forced_root_block: "",
        skin: "oxide-dark",
        valid_elements : '*[*],meta[*]',
        valid_children: '+h1[div],+h2[div],+h3[div],+h4[div],+h5[div],+h6[div],+a[div]',
        extended_valid_elements : "meta[*]",
        valid_children : "+body[meta],+div[h2|span|meta|object],+object[param|embed]",
        plugins: [
          'table advlist autolink lists link image charmap print preview anchor fullpage',
          'searchreplace visualblocks code fullscreen textcolor',
          'insertdatetime media contextmenu paste code'
        ],
        toolbar: 'insertfile undo redo | fontselect | fontsizeselect | styleselect | bold italic | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | ltr rtl',
        content_css: [
          APP_URL.replace('/index.php','')+'/css/email.css',
        ],

        external_filemanager_path:APP_URL.replace('/index.php','')+"/filemanager2/",
        filemanager_title:"Responsive Filemanager" ,
        external_plugins: { "filemanager" : APP_URL.replace('/index.php','')+"/filemanager2/plugin.min.js"}
    });

    tinymce.init({
        selector: '.clean-editor',
        height: 500,
        convert_urls: false,
        remove_script_host: false,
        forced_root_block: "",
        skin: "oxide-dark",
        plugins: [
          'fullpage table advlist autolink lists link image charmap print preview anchor directionality',
          'searchreplace visualblocks code fullscreen',
          'insertdatetime media contextmenu paste code textcolor'
        ],
        toolbar: 'insertfile undo redo | fontselect | fontsizeselect | styleselect | bold italic | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | ltr rtl',
        valid_elements : '*[*],meta[*]',
        valid_children: '+h1[div],+h2[div],+h3[div],+h4[div],+h5[div],+h6[div],+a[div]',
        extended_valid_elements : "meta[*]",
        valid_children : "+body[style],+body[meta],+div[h2|span|meta|object],+object[param|embed]",
        external_filemanager_path:APP_URL.replace('/index.php','')+"/filemanager2/",
        filemanager_title:"Responsive Filemanager" ,
        external_plugins: { "filemanager" : APP_URL.replace('/index.php','')+"/filemanager2/plugin.min.js"}
    });

    tinymce.init({
        selector: '.builder-editor',
        height: 300,
        convert_urls: false,
        remove_script_host: false,
        forced_root_block: "",
        skin: "oxide-dark",
        plugins: [
          'table advlist autolink lists link image charmap print preview anchor directionality',
          'searchreplace visualblocks code fullscreen',
          'insertdatetime media contextmenu paste code textcolor'
        ],
        toolbar: 'insertfile undo redo | fontselect | fontsizeselect | styleselect | bold italic | forecolor backcolor | alignleft aligncenter alignright alignjustify | bullist numlist outdent indent | link image media | ltr rtl',
        valid_elements : '*[*],meta[*]',
        valid_children: '+h1[div],+h2[div],+h3[div],+h4[div],+h5[div],+h6[div],+a[div]',
        extended_valid_elements : "meta[*]",
        valid_children : "+body[style],+body[meta],+div[h2|span|meta|object],+object[param|embed]",
        content_css: [
          APP_URL.replace('/index.php','')+'/css/res_email.css',
          APP_URL.replace('/index.php','')+'/css/editor.css',
        ],
        external_filemanager_path:APP_URL.replace('/index.php','')+"/filemanager2/",
        filemanager_title:"Responsive Filemanager" ,
        external_plugins: { "filemanager" : APP_URL.replace('/index.php','')+"/filemanager2/plugin.min.js"},
    });
    
    tinymce.init({
        selector: '.setting-editor',
        height: 300,
        convert_urls: false,
        remove_script_host: false,
        forced_root_block: "",
        skin: "oxide",
        menubar: false,
        branding: false,
        elementpath: false,
        plugins: 'link textcolor lists autolink',
            //toolbar: 'undo redo | bold italic underline | fontselect fontsizeselect | forecolor backcolor | alignleft aligncenter alignright alignfull | numlist bullist outdent indent',
        toolbar: [
            'bold italic underline forecolor link menuDateButton',
            // 'forecolor backcolor | alignleft aligncenter alignright alignfull | numlist bullist outdent indent'
        ],
        valid_elements : '*[*],meta[*]',
        valid_children: '+h1[div],+h2[div],+h3[div],+h4[div],+h5[div],+h6[div],+a[div]',
        extended_valid_elements : "meta[*]",
        valid_children : "+body[style],+body[meta],+div[h2|span|meta|object],+object[param|embed]",
        content_css: [
          APP_URL.replace('/index.php','')+'/css/res_email.css',
          APP_URL.replace('/index.php','')+'/css/editor.css',
        ],
        external_filemanager_path:APP_URL.replace('/index.php','')+"/filemanager2/",
        filemanager_title:"Responsive Filemanager" ,
        external_plugins: { "filemanager" : APP_URL.replace('/index.php','')+"/filemanager2/plugin.min.js"},
    });
});
