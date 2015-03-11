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
                $($userMail.parent()).append('<span style="color: red;">* Required</span>');
                $($userText.parent()).append('<span style="color: red;">* Required</span>');
            }
        });
    };

    Controller.prototype.initFaqToggles = function() {
        var self = this;

        this.$faqToggles.on('click', function() {
            self.$faqToggles.find('div.description').hide();
            $(this).find('div.description').show();
        });
    };

    Controller.prototype.init = function() {
        this.initScroll();
        this.checkMail();
        this.initFaqToggles();
    };

    $(document).ready(function() {
        var controller = new Controller();

        controller.init();
    });
})(jQuery);