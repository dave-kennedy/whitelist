(function ($) {
    $.widget('ui.vertabs', {
        '_create': function () {
            var self = this;
            
            this.element.addClass('ui-vertabs ui-widget');
            
            this.panels = this.element.children('div')
                .addClass('ui-vertabs-panel');
            
            this.tabContainer = this.element.children('ul')
                .addClass('ui-vertabs-nav');
            
            this.tabs = this.tabContainer.children('li')
                .addClass('ui-corner-left ui-state-default')
                .hover(function () {
                    $(this).addClass('ui-state-hover');
                }, function () {
                    $(this).removeClass('ui-state-hover');
                });
            
            this.links = this.tabs.children('a')
                .click(function () {
                    var panelId = $(this).attr('href');
                    self.activate(panelId);
                });
            
            this.activate();
        },
        'activate': function (panelId) {
            if (!panelId) {
                this.tabs.filter(':first').addClass('ui-state-active');
                this.panels.filter(':not(:first)').hide();
                return;
            }
            
            this.tabs.removeClass('ui-state-active');
            this.panels.hide();
            
            this.tabs.has('a[href=' + panelId + ']').addClass('ui-state-active');
            $(panelId).show();
        },
        'addTab': function (name) {
            var newLink, newPanel, newTab, self = this;
            
            newLink = $('<a href="#' + name + '">' + name + '</a>')
                .click(function () {
                    var panelId = $(this).attr('href');
                    self.activate(panelId);
                });
            
            newTab = $('<li class="ui-corner-left ui-state-default"></li>')
                .append(newLink)
                .hover(function () {
                    $(this).addClass('ui-state-hover');
                }, function () {
                    $(this).removeClass('ui-state-hover');
                });
            
            newPanel = $('<div class="ui-vertabs-panel" id="' + name + '">'
                + '<p><input type="text" value="' + name + '" /></p>'
                + '<p><textarea name="' + name + '"></textarea></p>'
                + '</div>');
            
            this.links = this.links.add(newLink);
            this.tabs = this.tabs.add(newTab);
            this.panels = this.panels.add(newPanel);
            
            $('#new-category-tab').before(newTab);
            $('#new-category').before(newPanel);
            
            newLink.click();
        },
        'renameTab': function (name, newName) {
            this.links.filter('[href=#' + name + ']')
                .attr('href', '#' + newName)
                .text(newName);
            
            $('#' + name).attr('id', newName)
                .find('textarea').attr('name', newName);
        }
    });
}(jQuery));