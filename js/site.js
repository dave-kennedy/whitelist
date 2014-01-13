$(function () {
    function save() {
        action.val('save');
        form.submit();
    }
    
    function upload() {
        var password = $('#password');
        
        if (password.val() === '') {
            password.css({
                'background-color': '#fee',
                'border-color': '#c00',
                'color': '#c00'
            }).effect('bounce').focus();
            return;
        }
        
        action.val('upload');
        form.submit();
    }
    
    var action = $('#action'),
        categories = $('#categories').vertabs({
            'addTab': function (title, panelID) {
                var newTitle = $('<p><input class="category-title" name="' + panelID + '[title]" type="text" value="' + title + '" /></p>'),
                    newContents = $('<p><textarea class="category-contents" name="' + panelID + '[contents]"></textarea></p>');
                
                $('#' + panelID).append(newTitle).append(newContents);
                
                newContents.find('textarea').focus();
            }
        }),
        form = $('#form');
    
    $('body').focusout(function (e) {
        var target = $(e.target),
            panelID,
            title;
        
        if (target.hasClass('category-title')) {
            panelID = target.attr('name').slice(0, -7);
            title = target.val().trim();
            categories.vertabs('renameTab', panelID, title);
            return;
        }
        
        if (target.hasClass('new-category-title')) {
            title = target.val().trim();
            categories.vertabs('addTab', title);
            return;
        }
    }).keypress(function (e) {
        var key = (e.keyCode ? e.keyCode : e.which),
            target = $(e.target);
        
        if (key == 13 && target.attr('id') == 'password') {
            upload();
        }
    });
    
    $('#save').click(function (e) {
        e.preventDefault();
        save();
    }).button();
    
    $('#upload').click(function (e) {
        e.preventDefault();
        upload();
    }).button();
    
    $('#result').hide().fadeIn().delay(3000).fadeOut();
});