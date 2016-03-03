tinymce.PluginManager.add('imagepicker', function(editor, url) {
    // Add a button that opens a window
    editor.addButton('imagepicker', {
        text: 'Image',
        icon: 'image',
        onclick: function() {
            // Open window
			mediaPickerTarget = "selected-media";
			showMediaPickerDialog();
			editor.insertContent('<img src="' + $('#selected-media').val() + '"' + $('#selected-media').val() + '>');
        }
    });

    // Adds a menu item to the tools menu
    editor.addMenuItem('imagepicker', {
        text: 'imagepicker plugin',
        context: 'tools',
        onclick: function() {
            // Open window with a specific url
            editor.windowManager.open({
                title: 'TinyMCE site',
                url: 'http://www.tinymce.com',
                width: 800,
                height: 600,
                buttons: [{
                    text: 'Close',
                    onclick: 'close'
                }]
            });
        }
    });
});