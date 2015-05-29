(function ($) {

    function Controller() {
        this.init();
    }

    Controller.prototype.init = (function () {
        this.initSliderSettings();
    });

    Controller.prototype.initSliderSettings = (function () {
        var pager = $('input[name="pager[pagerEnabled]"]');
        var navigation = $('select[name="general[navigation]"]');
        var pagerToggle = function(p) {
            if(p.val() == "true" && $('select[name="general[navigation]"] :selected').text() == 'thumbnails') {
                navigation.attr('disabled', 'disabled');
                $('select[name="general[navigation]"] :contains("standart")').attr("selected", "selected");
                $.jGrowl('It\'s impossible to use options of thumbnails navigation and enabled pagination.');
            } else {
                if(navigation.is(':disabled')) {
                    navigation.removeAttr('disabled');
                }
            }
        };
        pagerToggle($('input[name="pager[pagerEnabled]"] :checked'));
        pager.on('click', function() {
            pagerToggle($(this));
        });
    });

    $(document).ready(function () {
        return new Controller();
    });

}(jQuery));