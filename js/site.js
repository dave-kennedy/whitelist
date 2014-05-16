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
        addModal = $('#add-modal').dialog({ 'autoOpen': false, 'modal': true, 'title': 'New Category' }),
        exceptionsModal = $('#exceptions-modal').dialog({ 'autoOpen': false, 'modal': true, 'title': 'Exceptions' }),
        syncModal = $('#sync-modal').dialog({ 'autoOpen': false, 'modal': true, 'title': 'Sync' }),
        uploadModal = $('#upload-modal').dialog({ 'autoOpen': false, 'modal': true, 'title': 'Upload' }),
        submitted = false;
    
    function addCategory() {
        var title = $('#add-modal-title');
        
        if (title.val() === '') {
            title.css({
                'background-color': '#fee',
                'border-color': '#c00',
                'color': '#c00'
            }).effect('bounce').focus();
            return;
        }
        
        categories.vertabs('add', title.val());
        title.val('');
        
        addModal.dialog('close');
    }
    
    function saveConfig() {
        if (submitted) {
            return;
        }
        
        submitted = true;
        
        $('#action').val('save');
        $('#form').append($('#exceptions-modal-contents')).submit();
    }
    
    function syncConfig() {
        if (submitted) {
            return;
        }
        
        submitted = true;
        
        $('#action').val('sync');
        $('#form').submit();
    }
    
    function uploadConfig() {
        var password = $('#upload-modal-password');
        
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
        $('#form').append(password).submit();
    }
    
    $('body').click(function (event) {
        var target = $(event.target);
        
        if (target.parent().hasClass('remove-category')) {
            index = target.closest('.ui-vertabs-panel').index() - 1;
            
            categories.vertabs('remove', index);
            categories.vertabs('option', 'active', 0);
            return;
        }
        
        if (target.hasClass('ui-widget-overlay')) {
            $('.ui-dialog-content').dialog('close');
            return;
        }
    }).focusout(function (event) {
        var target = $(event.target),
            index,
            title;
        
        if (target.hasClass('category-title')) {
            index = target.closest('.ui-vertabs-panel').index() - 1;
            title = target.val();
            
            categories.vertabs('rename', index, title);
            return;
        }
    });
    
    $('#add').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            addModal.dialog('open');
        }
    });
    
    $('#add-modal-cancel').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            addModal.dialog('close');
        }
    });
    
    $('#add-modal-ok').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            addCategory();
        }
    });
    
    $('#exceptions').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            exceptionsModal.dialog('open');
        }
    });
    
    $('#exceptions-modal-ok').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            exceptionsModal.dialog('close');
        }
    });
    
    $('#save').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            saveConfig();
        }
    });
    
    $('#sync').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            syncModal.dialog('open');
        }
    });
    
    $('#sync-modal-cancel').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            syncModal.dialog('close');
        }
    });
    
    $('#sync-modal-ok').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            syncConfig();
        }
    });
    
    $('#upload').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            uploadModal.dialog('open');
        }
    });
    
    $('#upload-modal-cancel').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            uploadModal.dialog('close');
        }
    });
    
    $('#upload-modal-ok').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            uploadConfig();
        }
    });
    
    $('.remove-category').button();
    
    $('#result').hide().fadeIn().delay(3000).fadeOut();
});