$(function () {
    var categories = $('#categories').vertabs({
            'add': function (event, ui) {
                var newTitle = $('<p><input class="category-title" type="text" value="' + ui.newTab.text() + '" /></p>'),
                    newButton = $('<span class="delete-category">Delete</span>').button(),
                    newContents = $('<p><textarea class="category-contents" name="categories[' + ui.newTab.text() + ']"></textarea></p>');
                
                ui.newPanel.append(newTitle.append(newButton)).append(newContents);
                
                $(this).vertabs('option', 'active', ui.newTab.index());
                
                newContents.find('textarea').focus();
            },
            'rename': function (event, ui) {
                ui.panel.find('textarea').attr('name', 'categories[' + ui.tab.text() + ']');
            },
            'prefix': 'category-'
        }),
        actionResult = $('#action-result'),
        addCategoryModal = $('#add-category-modal').dialog({ 'autoOpen': false, 'dialogClass': 'no-close', 'modal': true, 'title': 'Add Category' }),
        uploadConfigModal = $('#upload-config-modal').dialog({ 'autoOpen': false, 'dialogClass': 'no-close', 'modal': true, 'title': 'Upload' }),
        changed = false,
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
        $('#form').append(password.clone()).submit();
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
    }).change(function () {
        changed = true;
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
        if (event.which === undefined || event.which === 1 || event.which === 13) {
            addCategoryModal.dialog('open');
        }
    });
    
    $('#add-category-modal-cancel').button().on('click keypress', function (event) {
        if (event.which === undefined || event.which === 1 || event.which === 13) {
            addCategoryModal.dialog('close');
        }
    });
    
    $('#add-category-modal-ok').button().on('click keypress', function (event) {
        if (event.which === undefined || event.which === 1 || event.which === 13) {
            addCategory();
        }
    });
    
    $('#upload-config').button().on('click keypress', function (event) {
        if (event.which === undefined || event.which === 1 || event.which === 13) {
            uploadConfigModal.dialog('open');
        }
    });
    
    $('#upload-config-modal-cancel').button().on('click keypress', function (event) {
        if (event.which === undefined || event.which === 1 || event.which === 13) {
            uploadConfigModal.dialog('close');
        }
    });
    
    $('#upload-config-modal-ok').button().on('click keypress', function (event) {
        if (event.which === undefined || event.which === 1 || event.which === 13) {
            uploadConfig();
        }
    });
    
    $('.delete-category').button();
    
    actionResult.hide().show('blind').delay(5000).hide('blind');
    
    $('#action-result-dismiss').click(function () {
        actionResult.dequeue();
    });
    
    $('.ui-dialog').keypress(function (event) {
        if (event.which === 13) {
            $(this).find('.ui-button:visible').eq(0).trigger('click');
            return;
        }
    });
    
    window.onbeforeunload = function () {
        if (changed && !submitted || $('#action-result').hasClass('error') && !submitted) {
            return 'Are you sure you want to navigate away?';
        }
    };
});