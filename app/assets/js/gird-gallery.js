(function ($) {
    $.fn.refresh = function () {
        return $(this.selector);
    };
}(jQuery));

(function (app, $) {

    function Loader() {
        this.$overlay = $('.gg-modal-loading-overlay');
        this.$content = $('.gg-modal-loading-object');
        this.$loadingText = this.$content.find('span#loading-text');

        this.defaultText = this.$loadingText.text();
    }

    Loader.prototype.clearText = function () {
        this.$loadingText.text(this.defaultText);
    };

    Loader.prototype.show = function (text) {
        this.$overlay.slideDown($.proxy(function () {
            if (typeof text !== 'undefined') {
                this.$loadingText.text(text);
            }

            this.$content.show();
        }, this));
    };

    Loader.prototype.hide = function () {
        // Chrome bug ?
        setTimeout($.proxy(function () {
            this.$content.hide($.proxy(function () {
                this.$overlay.slideUp();
                this.clearText();
            }, this));
        }, this), 1500)
    };

    $(document).ready(function () {
        app.Loader = new Loader;
    });

}(window.SupsysticGallery = window.SupsysticGallery || {}, jQuery));

(function (app, $) {

    function Forms() {}

    Forms.prototype.preventSubmit = function (submitEvent) {
        submitEvent.preventDefault();
        return false;
    };

    $(document).ready(function () {
        app.Forms = new Forms;

        $('[data-prevent-submit]').submit(app.Forms.preventSubmit);
    });

}(window.SupsysticGallery = window.SupsysticGallery || {}, jQuery));

(function($) {

    $(document).ready(function() {

        /*var ggActiveTab = jQuery('nav.supsystic-navigation li.active a').attr('href').split('/');
        ggActiveTab = ggActiveTab[ggActiveTab.length - 1];
        if(typeof(ggActiveTab) != 'undefined' && ggActiveTab != 'admin.php?page=supsystic-slider') {
            var subMenus = jQuery('#toplevel_page_supsystic-slider').find('.wp-submenu li');
            subMenus.removeClass('current').each(function(){
                if(jQuery(this).find('a[href*="'+ ggActiveTab + '"]').size()) {
                    jQuery(this).addClass('current');
                }
            });
        }*/

        // SupsysticGallery.Loader.show();

        /* Tooltipster */
        $('.supsystic-tooltip').tooltipster({
            contentAsHTML: true,
            animation: 'swing',
            theme: 'tooltipster-shadow',
            position: 'top-left',
            maxWidth: 400
        });

        /* Lazy loading */
        $('.ready-lazy').lazyload({
            effect: 'fadeIn',
            load: function() {
                setContainerHeight();
            }
        });

        setContainerHeight();
        changeUiButtonToWp();
        closeOnOutside();
    });

    $(window).on('resize', function() {
        setContainerHeight();
    });

    function setContainerHeight() {
        var container = $('.supsystic-container'),
            content = $('.supsystic-content'),
            navigation = $('.supsystic-navigation ul');

        navigation.css({'height': 'auto' });
        container.css({'height': 'auto' });
        content.css({'height': 'auto' });

        if (content.outerHeight() > navigation.outerHeight() || container.outerHeight > navigation.outerHeight()) {
            navigation.css({'height': content.outerHeight() + 'px'});
        } else {
            container.css({'height': navigation.outerHeight() + 'px'});
            content.css({'height': navigation.outerHeight() + 'px'});
        }
    }

    function changeUiButtonToWp() {
        $(document).on('dialogopen', function(event, ui) {
            var $button = $('.ui-button');

            $button.each(function() {
                if (!$(this).hasClass('ui-dialog-titlebar-close')) {
                    $(this).removeAttr('class').addClass('button button-primary');
                }
            });
        });
    }

    function closeOnOutside() {
        $(document).on('click', function () {
            var $overlay = $('.ui-widget-overlay');
            var $container = $('body').find('.ui-dialog-content');

            $overlay.on('click', function () {
                $container.dialog('close');
            });
        });
    }

})(jQuery);
