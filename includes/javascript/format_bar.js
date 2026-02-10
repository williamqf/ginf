

function render_format_bar(id = 'html_content', height = 400, bar_type = 'basic') {
    /**
     * ClassName : 'sun-eidtor'
     */
    // ID or DOM object

    let advanced = [
        'undo', 'redo',
        'font', 'fontSize', 'formatBlock',
        'paragraphStyle', 'blockquote',
        'bold', 'underline', 'italic', 'strike', 'subscript', 'superscript',
        'fontColor', 'hiliteColor', 'textStyle',
        'removeFormat',
        'outdent', 'indent',
        'align', 'horizontalRule', 'list', 'lineHeight',
        'table', 'link', 'image', /** 'video', 'audio',  'math', */ // You must add the 'katex' library at options to use the 'math' plugin.
        /** 'imageGallery', */ // You must add the "imageGalleryUrl".
        'fullScreen', 'showBlocks', 'codeView',
        'preview', 'print', //'save', // 'template',
        /** 'dir', 'dir_ltr', 'dir_rtl' */ // "dir": Toggle text direction, "dir_ltr": Right to Left, "dir_rtl": Left to Right
    ];

    let operator = [
        'font', 'fontSize', 'formatBlock',
        'blockquote',
        'bold', 'underline', 'italic', 'strike',
        'fontColor', 'hiliteColor',
        'removeFormat',
        'outdent', 'indent',
        'align', 'horizontalRule', 'list', 'lineHeight',
        'table', 
    ];

    let basic = [
        'font', 'fontSize', 'formatBlock',
        'blockquote',
        'bold', 'underline', 'italic', 'strike',
        'fontColor', 'hiliteColor', 'removeFormat'
    ];


    switch (bar_type) {
        case 'advanced':
            bar_type = advanced;
            break;
        case 'operator':
            bar_type = operator;
            break;
        default:
            bar_type = basic;
    }


    const editor = SUNEDITOR.create((document.getElementById(id) || id), {
        // All of the plugins are loaded in the "window.SUNEDITOR" object in dist/suneditor.min.js file
        // Insert options
        toolbarContainer : '#toolbar_container',
        "lang": SUNEDITOR_LANG.pt_br,
        height: height,
        width: "100%",
        buttonList: [
            bar_type
        ]
    })

    return editor;
}


function displayContent(id = 'display_description') {


    const editorDisplay = SUNEDITOR.create((document.getElementById(id) || id), {
        toolbarContainer : '#toolbar_container',
        height: "auto",
        width: "100%",
        resizingBar: false
    })

    editorDisplay.toolbar.hide();
    editorDisplay.disable();

    return editorDisplay;
}
