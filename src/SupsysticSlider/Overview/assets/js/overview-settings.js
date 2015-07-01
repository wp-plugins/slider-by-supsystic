(function($) {

    var Controller = function () {
        this.$newsContainer = $('.supsystic-overview-news');
        this.$mailButton = $('#send-mail');
        this.$faqToggles = $('.faq-title');
    };

    Controller.prototype.initScroll = function() {

        this.$newsContainer.slimScroll({
            height: '500px',
            railVisible: true,
            alwaysVisible: true,
            allowPageScroll: true
        });
    };

    Controller.prototype.checkMail = function() {
        var $userMail = $('[name="mail[email]"]'),
            $userText = $('[name="mail[message]"]');

        this.$mailButton.on('click', function(e) {
            if(!$userMail.val() || !$userText.val()) {
                e.preventDefault();
                $userMail.closest('tr').find('.required').css('color', 'red');
                $userText.closest('tr').find('.required').css('color', 'red');
                $('.required-notification').show();
            }
        });
    };

    Controller.prototype.initFaqToggles = function() {
        var self = this;

        this.$faqToggles.on('click', function() {
            //self.$faqToggles.find('div.description').hide();
            //$(this).find('div.description').show();
            jQuery(this).find('div.description').toggle();
        });
    };

    Controller.prototype.init = function() {
        this.initScroll();
        //this.checkMail();
        this.initFaqToggles();
    };

    $(document).ready(function() {
        var controller = new Controller();

        controller.init();
    });
})(jQuery);