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
                    var panelID = $(this).attr('href');
                    self.activate(panelID);
                });
            
            this.activate();
        },
        'activate': function (panelID) {
            if (!panelID) {
                this.tabs.filter(':first').addClass('ui-state-active');
                this.panels.filter(':not(:first)').hide();
                return;
            }
            
            this.tabs.removeClass('ui-state-active');
            this.panels.hide();
            
            this.tabs.has('a[href=' + panelID + ']').addClass('ui-state-active');
            $(panelID).show();
        },
        'addTab': function (title) {
            var newID = this.makeID(title),
                self = this,
                newLink,
                newPanel,
                newTab;
            
            newLink = $('<a href="#' + newID + '">' + title + '</a>')
                .click(function () {
                    var panelID = $(this).attr('href');
                    self.activate(panelID);
                });
            
            newTab = $('<li class="ui-corner-left ui-state-default"></li>')
                .append(newLink)
                .hover(function () {
                    $(this).addClass('ui-state-hover');
                }, function () {
                    $(this).removeClass('ui-state-hover');
                });
            
            newPanel = $('<div class="ui-vertabs-panel" id="' + newID + '">'
                + '<p><input class="category-title" name="' + newID + '[category]" type="text" value="' + title + '" /></p>'
                + '<p><textarea class="category-contents" name="' + newID + '[contents]"></textarea></p>'
                + '</div>');
            
            this.links = this.links.add(newLink);
            this.tabs = this.tabs.add(newTab);
            this.panels = this.panels.add(newPanel);
            
            $('#new-category-tab').before(newTab);
            $('#new-category').before(newPanel);
            
            newLink.click();
        },
        'makeID': function (string) {
            return string.replace(/ /g, '-').replace(/[^\w-]/g, '').toLowerCase();
        },
        'renameTab': function (title, newTitle) {
            var ID = this.makeID(title),
                newID = this.makeID(newTitle);
            
            this.links.filter('[href=#' + ID + ']')
                .attr('href', '#' + newID)
                .text(newTitle);
            
            $('#' + ID).attr('id', newID)
                .find('.category-title').attr('name', newID + '[category]').addBack()
                .find('.category-contents').attr('name', newID + '[contents]');
        }
    });
}(jQuery));