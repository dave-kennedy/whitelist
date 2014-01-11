(function ($) {
    $.widget('ui.vertabs', {
        'options': {
            'addTab': null,
            'renameTab': null
        },
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
            
            newPanel = $('<div class="ui-vertabs-panel" id="' + newID + '"></div>');
            
            this.links = this.links.add(newLink);
            this.tabs = this.tabs.add(newTab);
            this.panels = this.panels.add(newPanel);
            
            $('#new-category-tab').before(newTab);
            $('#new-category').before(newPanel);
            
            this.activate('#' + newID);
            
            if ($.isFunction(this.options.addTab)) {
                this.options.addTab(title, newID);
            }
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
            
            $('#' + ID).attr('id', newID);
            
            if ($.isFunction(this.options.renameTab)) {
                this.options.renameTab(title, newID);
            }
        }
    });
}(jQuery));