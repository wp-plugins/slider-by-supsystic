/*global jQuery*/
(function ($, undefined) {

    $.fn.scrollTo = function( target, options, callback ){
        if(typeof options == 'function' && arguments.length == 2){ callback = options; options = target; }
        var settings = $.extend({
            scrollTarget  : target,
            offsetTop     : 50,
            duration      : 500,
            easing        : 'swing'
        }, options);
        return this.each(function(){
            var scrollTarget = (typeof settings.scrollTarget == "number") ? settings.scrollTarget : $(settings.scrollTarget);
            var scrollY = (typeof scrollTarget == "number") ? scrollTarget : scrollTarget.offset().top + $('html, body').scrollTop() + 1 - parseInt(settings.offsetTop);
            $('html, body').animate({scrollTop : scrollY }, parseInt(settings.duration), settings.easing, function(){
                if (typeof callback == 'function') { callback.call(this); }
            });
        });
    };

    /**
     * Tabs constructor.
     *
     * @constructor
     */
    function Tabs() {
        this.$buttons = $('div.navigation > .add-new-h2');

        this.init();
    }

    /**
     * Initialize tabs controller.
     *
     * @type {Function}
     */
    Tabs.prototype.init = (function () {
        this.$buttons.on('click', $.proxy(this.handleClick, this));
    });

    /**
     * Handles click on buttons.
     *
     * @type {Function}
     */
    Tabs.prototype.handleClick = (function (e) {
        $('body').scrollTo($(e.currentTarget).attr('href'));
    });

    $(document).ready(function () {
        return new Tabs();
    });

}(jQuery));