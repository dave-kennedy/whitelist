$(function () {
    var action = $('#action'),
        categories = $('#categories').vertabs({
            'addTab': function (title, newID) {
                var newTitle = $('<p><input class="category-title" name="' + newID + '[category]" type="text" value="' + title + '" /></p>'),
                    newContents = $('<p><textarea class="category-contents" name="' + newID + '[contents]"></textarea></p>');
                
                $('#' + newID).append(newTitle).append(newContents);
                
                newContents.find('textarea').focus();
            },
            'renameTab': function (title, newID) {
                $('#' + newID).find('.category-title').attr('name', newID + '[category]').addBack()
                    .find('.category-contents').attr('name', newID + '[contents]');
            }
        }),
        form = $('#form'),
        title;
    
    $('body').focusin(function (e) {
        var target = $(e.target);
        
        if (target.hasClass('category-title')) {
            title = target.val().trim();
            return;
        }
    }).focusout(function (e) {
        var target = $(e.target),
            newTitle;
        
        if (target.hasClass('category-title')) {
            newTitle = target.val().trim();
            categories.vertabs('renameTab', title, newTitle);
            return;
        }
        
        if (target.hasClass('new-category-title')) {
            newTitle = target.val().trim();
            categories.vertabs('addTab', newTitle);
            return;
        }
    });
    
    $('#save').click(function (e) {
        e.preventDefault();
        action.val('save');
        form.submit();
    }).button();
    
    $('#upload').click(function (e) {
        var password = $('#password');
        
        e.preventDefault();
        
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
    }).button();
    
    $('#result').hide().fadeIn().delay(3000).fadeOut();
});