tinymce.PluginManager.add('linkpicker', function(editor, url) {
    // Add a button that opens a window
    editor.addButton('linkpicker', {
        text: 'Content Link',
        icon: false,
        onclick: function() {
            // Open window
			mediaPickerTarget = "selected-media";
			showMediaPickerDialog();
			editor.insertContent('<a href="' + $('#selected-media').val() + '">' + $('#selected-media').val() + '</a>');
        /*    editor.windowManager.open({
                title: 'linkpicker plugin',
                body: [
                    {type: 'textbox', name: 'title', label: 'Title'}
                ],
                onsubmit: function(e) {
                    // Insert content when the window form is submitted
                    editor.insertContent('Title: ' + e.data.title);
                }
            });  */
        }
    });

    // Adds a menu item to the tools menu
    editor.addMenuItem('linkpicker', {
        text: 'linkpicker plugin',
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