$(function () {
    var categories = $('#categories').vertabs({
            'add': function (event, ui) {
                var newButton = $('<span class="remove-category">Delete</span>').button(),
                    newTitle = $('<p><input class="category-title" name="' + ui.newPanel.attr('id') + '[title]" type="text" value="' + ui.newTab.text() + '" /></p>'),
                    newContents = $('<p><textarea class="category-contents" name="' + ui.newPanel.attr('id') + '[contents]"></textarea></p>');
                
                ui.newPanel.append(newTitle.append(newButton)).append(newContents);
                
                $(this).vertabs('option', 'active', ui.newTab.index());
                
                newContents.find('textarea').focus();
            },
            'prefix': 'category-'
        }),
        newCategory = $('#new-category'),
        password = $('#password'),
        submitted = false;
    
    function addCategory() {
        if (newCategory.val() === '') {
            newCategory.css({
                'background-color': '#fee',
                'border-color': '#c00',
                'color': '#c00'
            }).effect('bounce').focus();
            return;
        }
        
        categories.vertabs('add', newCategory.val());
        newCategory.val('');
    }
    
    function refreshConfig() {
        if (submitted) {
            return;
        }
        
        submitted = true;
        
        $('#action').val('refresh');
        $('#form').submit();
    }
    
    function saveConfig() {
        if (submitted) {
            return;
        }
        
        submitted = true;
        
        $('#action').val('save');
        $('#form').submit();
    }
    
    function uploadConfig() {
        if (password.val() === '') {
            password.css({
                'background-color': '#fee',
                'border-color': '#c00',
                'color': '#c00'
            }).effect('bounce').focus();
            return;
        }
        
        if (submitted) {
            return;
        }
        
        submitted = true;
        
        $('#action').val('upload');
        $('#form').submit();
    }
    
    $('body').click(function (event) {
        var target = $(event.target);
        
        if (target.parent().hasClass('remove-category')) {
            index = target.closest('.ui-vertabs-panel').index() - 1;
            
            categories.vertabs('remove', index);
            categories.vertabs('option', 'active', 0);
        }
    }).focusout(function (event) {
        var target = $(event.target),
            index,
            title;
        
        if (target.hasClass('category-title')) {
            index = target.closest('.ui-vertabs-panel').index() - 1;
            title = target.val();
            
            categories.vertabs('rename', index, title);
        }
    }).keypress(function (event) {
        var keyCode = event.keyCode,
            target = $(event.target);
        
        if (keyCode === 9 && target.hasClass('category-contents')) {
            event.preventDefault();
            newCategory.focus();
        }
    });
    
    newCategory.keypress(function (event) {
        if (event.keyCode === 13) {
            addCategory();
        }
    });
    
    password.keypress(function (event) {
        if (event.keyCode === 13) {
            uploadConfig();
        }
    });
    
    $('#add').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            addCategory();
        }
    });
    
    $('#refresh').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            refreshConfig();
        }
    });
    
    $('#save').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            saveConfig();
        }
    });
    
    $('#upload').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            uploadConfig();
        }
    });
    
    $('#result').hide().fadeIn().delay(3000).fadeOut();
    
    $('.category-title').after($('<span class="remove-category">Delete</span>').button());
});