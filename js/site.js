$(function () {
    var categories = $('#categories').vertabs({
            'add': function (event, ui) {
                var newButton = $('<span class="delete-category">Delete</span>').button(),
                    newTitle = $('<p><input class="category-title" name="' + ui.newPanel.attr('id') + '[title]" type="text" value="' + ui.newTab.text() + '" /></p>'),
                    newContents = $('<p><textarea class="category-contents" name="' + ui.newPanel.attr('id') + '[contents]"></textarea></p>');
                
                ui.newPanel.append(newTitle.append(newButton)).append(newContents);
                
                $(this).vertabs('option', 'active', ui.newTab.index());
                
                newContents.find('textarea').focus();
            },
            'prefix': 'category-'
        }),
        addCategoryModal = $('#add-category-modal').dialog({ 'autoOpen': false, 'modal': true, 'title': 'Add Category' }),
        syncConfigModal = $('#sync-config-modal').dialog({ 'autoOpen': false, 'modal': true, 'title': 'Sync' }),
        uploadConfigModal = $('#upload-config-modal').dialog({ 'autoOpen': false, 'modal': true, 'title': 'Upload' }),
        viewExceptionsModal = $('#view-exceptions-modal').dialog({ 'autoOpen': false, 'modal': true, 'title': 'Exceptions' }),
        submitted = false;
    
    function addCategory() {
        var title = $('#add-category-modal-title');
        
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
        
        addCategoryModal.dialog('close');
    }
    
    function saveConfig() {
        if (submitted) {
            return;
        }
        
        submitted = true;
        
        $('#action').val('saveConfig');
        $('#form').append($('#view-exceptions-modal-contents')).submit();
    }
    
    function syncConfig() {
        if (submitted) {
            return;
        }
        
        submitted = true;
        
        $('#action').val('syncConfig');
        $('#form').submit();
    }
    
    function uploadConfig() {
        var password = $('#upload-config-modal-password');
        
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
        
        $('#action').val('uploadConfig');
        $('#form').append(password).submit();
    }
    
    $('body').click(function (event) {
        var target = $(event.target),
            index;
        
        if (target.parent().hasClass('delete-category')) {
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
    
    $('#add-category').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            addCategoryModal.dialog('open');
        }
    });
    
    $('#add-category-modal-cancel').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            addCategoryModal.dialog('close');
        }
    });
    
    $('#add-category-modal-ok').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            addCategory();
        }
    });
    
    $('#save-config').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            saveConfig();
        }
    });
    
    $('#sync-config').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            syncConfigModal.dialog('open');
        }
    });
    
    $('#sync-config-modal-cancel').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            syncConfigModal.dialog('close');
        }
    });
    
    $('#sync-config-modal-ok').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            syncConfig();
        }
    });
    
    $('#upload-config').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            uploadConfigModal.dialog('open');
        }
    });
    
    $('#upload-config-modal-cancel').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            uploadConfigModal.dialog('close');
        }
    });
    
    $('#upload-config-modal-ok').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            uploadConfig();
        }
    });
    
    $('#view-exceptions').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            viewExceptionsModal.dialog('open');
        }
    });
    
    $('#view-exceptions-modal-ok').button().on('click keypress', function (event) {
        if (event.keyCode === undefined || event.keyCode === 13) {
            viewExceptionsModal.dialog('close');
        }
    });
    
    $('.delete-category').button();
    
    $('#result').hide().fadeIn().delay(3000).fadeOut();
});