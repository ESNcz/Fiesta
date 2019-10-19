+function($, window){ 'use strict';

    var app = {
        name: 'Thanh workspace',
        version: '0.0.2'
    };

    app.defaults = {
        menubar: {
            folded: false,
            theme: 'light',
        },
        navbar: {
            theme: 'primary',
        }
    };

    // Cache DOM
    app.$body = $('body');
    app.$menubar = $('#menubar');
    app.$appMenu = app.$menubar.find('.app-menu').first();
    app.$navbar = $('#app-navbar');
    app.$main = $('#app-main');
    app.defaultLayout = app.$body.hasClass('menubar-left');
    app.settings = app.defaults;
    app.storage = $.localStorage;


    // initialize navbar
    app.$navbar.addClass('in');

    // initialize menubar
    app.$menubar.addClass('in');

    // initialize main
    app.$main.addClass('in');

    window.app = app;
}(jQuery, window);


// menubar MODULE
// =====================

+function($, window){ 'use strict';
    // Cache DOM
    var $body = app.$body,
        $menubar = app.$menubar,
        $appMenu = app.$appMenu,
        $menubarFoldButton = $('#menubar-fold-btn'),
        $menubarToggleButton = $('#menubar-toggle-btn');

    // menubar object
    var menubar = {
        open: false,
        folded: app.settings.menubar.folded,
        scrollInitialized: false,

        init: function() {
            app.defaultLayout && this.folded && this.fold();

            this.listenForEvents();
        },

        listenForEvents: function() {
            var self = this;

            $(window).on('load', function(e) {
                var current = Breakpoints.current();

                // highlight the open page's link
                if (app.topbarLayout && current.name !== 'xs')
                    $(document).on('app-menu.reduce.done', self.highlightOpenLink.bind(self));
                else
                    self.highlightOpenLink();


                self.cloneAppUser() && self.foldAppUser();

                // mobile or tablet
                if (current.name === 'xs') {
                    // push the menubar out
                    self.pushOut();

                    // if the menubar is folded then unfold it
                    self.folded && self.unFold();
                }

                // desktop (small, medium and large) screens
                if (app.topbarLayout &&  current.name !== 'xs') self.reduceAppMenu();

                if (app.defaultLayout && (current.name !== 'xs' && current.name !== 'lg')) self.fold();
            });

            // changing the the layout according to breakpoints
            Breakpoints.on('change', function() {
                if (app.defaultLayout) {
                    if (/sm|md/g.test(this.current.name)){
                        self.folded || self.fold();
                    } else if(/lg/g.test(this.current.name)) {
                        !app.settings.menubar.folded && self.unFold();
                    } else {
                        self.unFold();
                    }
                }
            });

            Breakpoints.on('xs', {
                enter: function() {
                    // push the (menubar) out
                    self.pushOut();
                },
                leave: function() {
                    app.defaultLayout && self.pullIn();
                }
            });

            // folding and unfolding the menubar
            $menubarFoldButton.on('click', function(e){
                !self.folded ? self.fold() : self.unFold();
                e.preventDefault();
            });

            // showing and hiding the menubar
            $menubarToggleButton.on('click', function(e){
                self.open ? self.pushOut() : self.pullIn();
                e.preventDefault();
            });

            // toggling submenus when the menubar is folded
            $(document).on('mouseenter mouseleave', 'body.menubar-fold ul.app-menu > li.has-submenu', function(e){
                $(this).toggleClass('open').siblings('li').removeClass('open');
            });

            // toggling submenus in the (topbar) layout
            $(document).on('mouseenter mouseleave', 'body.menubar-top ul.app-menu li.has-submenu', self.toggleTopbarSubmneuOnHover);

            // toggling submenus on click
            $(document).on('click', 'body.menubar-unfold .app-menu .submenu-toggle, body.menubar-fold .app-menu .submenu .submenu-toggle', self.toggleSubmenuOnClick);

            // readjust the scroll height on resize and orientationchange
            $(window).on('resize orientationchange', self.readjustScroll);
        },

        cloneAppUser: function() {
            var $navbarCollapse = $('.navbar .navbar-collapse');
            if ($navbarCollapse.find('.app-user').length === 0){
                $menubar.find('.app-user').clone().appendTo($navbarCollapse);
            }
            return true;
        },

        foldAppUser: function() {
            $('.app-user .avatar').addClass('dropdown').find('>a').attr('data-toggle', 'dropdown');
            $('.app-user .dropdown-menu').first().clone().appendTo('.app-user .avatar')
            return true;
        },

        reduceAppMenu: function(){
            var $appMenu = $('body.menubar-top .app-menu');
            // if the menu is already customized return true
            if ($appMenu.find('>li.more-items-li').length) return true;

            var $menuItems = $appMenu.find('> li:not(.menu-separator)');
            if ($menuItems.length > 5) {
                var $moreItemsLi = $('<li class="more-items-li has-submenu"></li>'),
                    $moreItemsUl = $('<ul class="submenu"></ul>'),
                    $moreItemsToggle = $('<a href="javascript:void(0)" class="submenu-toggle"></a>');
                $moreItemsToggle.append(['<i class="menu-icon zmdi zmdi-more-vert zmdi-hc-lg"></i>', '<span class="menu-text">More...</span>', '<i class="menu-caret zmdi zmdi-hc-sm zmdi-chevron-right"></i>']);

                $menuItems.each(function(i, item){
                    if (i >= 5) $(item).clone().appendTo($moreItemsUl);
                });

                $moreItemsLi.append([$moreItemsToggle, $moreItemsUl]).insertAfter($appMenu.find('>li:nth-child(5)'));
            }

            $(document).trigger('app-menu.reduce.done');

            return true;
        },

        toggleSubmenuOnClick: function(e) {
            $(this).parent().toggleClass('open').find('> .submenu').slideToggle(500).end().siblings().removeClass('open').find('> .submenu').slideUp(500);
            e.preventDefault();
        },

        toggleTopbarSubmneuOnHover: function(e){
            var $this = $(this), total = $this.offset().left + $this.width();
            var ww = $(window).width();
            if ((ww - total) < 220) {
                $this.find('> .submenu').css({left: 'auto', right: '100%'});
            } else if ((ww - total) >= 220 && !$this.is('.app-menu > li')) {
                $this.find('> .submenu').css({left: '100%', right: 'auto'});
            }
            $(this).toggleClass('open').siblings().removeClass('open');
        },

        fold: function() {
            $body.removeClass('menubar-unfold').addClass('menubar-fold');
            $menubarFoldButton.removeClass('is-active');
            this.toggleScroll() && this.toggleMenuHeading() && (this.folded = true);
            $appMenu.find('li.open').removeClass('open') && $appMenu.find('.submenu').slideUp();
            return true;
        },

        unFold: function() {
            $body.removeClass('menubar-fold').addClass('menubar-unfold');
            $menubarFoldButton.addClass('is-active');
            // initialize the scroll if it's not initialized
            this.toggleScroll() && this.toggleMenuHeading() && (this.folded = false);
            $appMenu.find('li.open').removeClass('open') && $appMenu.find('.submenu').slideUp();
            return true;
        },

        pullIn: function() {
            $body.addClass('menubar-in') && $menubarToggleButton.addClass('is-active') && (this.open = true);
            return true;
        },

        pushOut: function() {
            $body.removeClass('menubar-in') && $menubarToggleButton.removeClass('is-active') && (this.open = false);
            return true;
        },

        readjustScroll: function(e){
            if ($body.hasClass('menubar-top') || this.folded) return;

            var parentHeight = $menubar.height(),
                $targets = $('.menubar-scroll, .menubar-scroll-inner, .slimScrollDiv');
            if (Breakpoints.current().name === 'xs') {
                $targets.height(parentHeight);
            } else {
                $targets.height(parentHeight - 75);
            }
        },

        toggleScroll: function(){
            var $scrollContainer = $('.menubar-scroll-inner');
            if(!$body.hasClass('menubar-unfold')){
                $scrollContainer.css('overflow', 'inherit').parent().css('overflow', 'inherit');
                $scrollContainer.siblings('.slimScrollBar').css('visibility', 'hidden');
            } else{
                $scrollContainer.css('overflow', 'hidden').parent().css('overflow', 'hidden');
                $scrollContainer.siblings('.slimScrollBar').css('visibility', 'visible');
            }
            return true;
        },

        toggleMenuHeading: function() {
            if ($body.hasClass("menubar-fold")) {
                $('.app-menu > li:not(.menu-separator)').each(function(i, item){
                    if (!$(item).hasClass('has-submenu')) {
                        $(item).addClass('has-submenu').append('<ul class="submenu"></ul>');
                    }
                    var href = $(item).find('a:first-child').attr("href");
                    var menuHeading = $(item).find('> a > .menu-text').text();
                    $(item).find('.submenu').first().prepend('<li class="menu-heading"><a href="'+href+'">'+menuHeading+'</a></li>');
                });
            } else {
                $appMenu.find('.menu-heading').remove();
            }

            return true;
        },

        highlightOpenLink: function() {
            var currentPageLink = $appMenu.find('li[class="active"]').first();

            if ($body.hasClass('menubar-left') && !this.folded) {
                currentPageLink.parents('.has-submenu').addClass('open').find('>.submenu').slideDown(500);
            }

            return true;
        },
    };

    window.app.menubar = menubar;
}(jQuery, window);


// NAVBAR MODULE
// =====================

+function($, window){ 'use strict';

    // Cache DOM
    var $body = app.$body;

    var navbar = {

        init: function() {
            this.listenForEvents();
        },

        listenForEvents: function() {
            $(document).on("click", '[data-toggle="collapse"]', function(e) {
                var $trigger = $(e.target);
                $trigger.is('[data-toggle="collapse"]') || ($trigger = $trigger.parents('[data-toggle="collapse"]'));
                var $target = $($trigger.attr('data-target'));
                if ($target.attr('id') === 'app-navbar-collapse') {
                    $body.toggleClass('navbar-collapse-in', !$trigger.hasClass('collapsed'));
                }
                e.preventDefault();
            });
        }
    };
    window.app.navbar = navbar;
}(jQuery, window);

// initialize app
+function($, window) { 'use strict';
    window.app.menubar.init();
    window.app.navbar.init();
}(jQuery, window);

// other
+function($, window) { 'use strict';

    $(window).on('load resize orientationchange', function(){
        // readjust panels on load, resize and orientationchange
        readjustActionPanel();

        // activate bootstrap tooltips
        $('[data-toggle="tooltip"]').tooltip();
    });

    function readjustActionPanel(){
        var $actionPanel = $('.app-action-panel');
        if (!$actionPanel.length > 0) return;
        var $actionList = $actionPanel.children('.app-actions-list').first();
        $actionList.height($actionPanel.height() - $actionList.position().top);
    }

}(jQuery, window);

+function(){ 'use strict';

    var toggle = '[data-toggle="class"]';

    var ToggleClass = function() {};

    ToggleClass.prototype.toggle = function(e) {
        var $this = $(this);

        if ($this.is('.disabled, :disabled')) return;

        var target = $this.attr('data-target');
        var className = $this.attr('data-class');
        var isActive = $(target).hasClass(className);

        if (!isActive) {
            $(target).addClass(className);
            $this.attr('data-active', true);
        } else{
            $(target).removeClass(className);
            $this.attr('data-active', false);
        }

        if ($this.attr('self-toggle')) {
            var className = $this.attr('self-toggle');
            $this.toggleClass(className);
        }
    }

    $(document).on('click.app.toggleclass', toggle, ToggleClass.prototype.toggle);
}(jQuery);