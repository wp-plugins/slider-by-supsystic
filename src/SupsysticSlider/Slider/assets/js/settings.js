(function ($, WordPress) {

    /**
     * Page Controller.
     *
     * @constructor
     */
    function Controller() {
        this.$submit = $('button#save');
        this.$form = $('form#settings');
        this.$pluginBtn = $('button#change');
        this.$pluginWindow = $('#changePluginWindow');
        this.$deleteBtn = $('button#delete');
        this.$randomCheckbox = $('#generalRandomStart');
        this.$addPage = $('.add-page');
        this.$addPost = $('.add-post');

        this.init();
    }

    /**
     *
     * toggle random slide start checkbox value
     *
     * @type {Function}
     */
    Controller.prototype.randomToggle = (function() {
        this.$randomCheckbox.on('click', function() {
            if(parseInt($(this).val(), 10)) {
                $(this).attr('value', '0');
            } else {
                $(this).attr('value', '1');
            }
        });
    });

    Controller.prototype.formNavigation = (function() {
        var $buttons = $('form .add-new-h2');

        $buttons.on('click', function() {
            var $container = $(this).closest('tr');
            $container.find('.add-new-h2').removeClass('active');
            $buttons.css('background-color', 'white');
            $(this).addClass('active');
        });
    });

    Controller.prototype.checkWidth = (function() {
        var $widthType = $('[name="properties[widthType]"]'),
            $height = $('[name="properties[height]"]');

        $widthType.on('change', function() {
            if($(this).val() == '%') {

                $height.attr('disabled', 'disabled');

                var notification = noty({
                    layout: 'topRight',
                    type: 'warning',
                    text : '<h3>Warning</h3>Max width in percents is equal to 100',
                    timeout: 2000,
                    animation: {
                        open: 'animated flipInX',
                        close: 'animated flipOutX',
                        easing: 'swing',
                        speed: '800'
                    }
                });
            } else {
                $height.attr('disabled', false);
            }
        });
    });

    Controller.prototype.addPages = function() {
        var sliderId = parseInt($('#sliderID').attr('value'));

        this.$addPage.on('click', function() {
            var postId = $('#post-feed-selectPages').val();

            $.post(WordPress.ajax.settings.url,
                {
                    id: postId,
                    slider : sliderId,
                    type: 'page',
                    action: 'supsystic-slider',
                    route: {
                        module: 'slider',
                        action: 'addPost'
                    }
                })
                .success(function (response) {
                    $.jGrowl(response.message);
                });
        });
    };

    Controller.prototype.addPosts = function() {
        var sliderId = parseInt($('#sliderID').attr('value'));

        this.$addPost.on('click', function() {
            var postId = $('#post-feed-selectPosts').val();

            $.post(WordPress.ajax.settings.url,
                {
                    id: postId,
                    slider : sliderId,
                    type: 'post',
                    action: 'supsystic-slider',
                    route: {
                        module: 'slider',
                        action: 'addPost'
                    }
                })
                .success(function (response) {
                    $.jGrowl(response.message);
                });
        });
    };

    Controller.prototype.initPostsTable = function() {

        jQuery("#posts-table").jqGrid({
            datatype: "local",
            autowidth: true,
            shrinkToFit: true,
            colNames:['Id', 'Image','Title'],
            colModel:[
                {name:'id', index:'id', sortable: false, width: 20, align: 'center'},
                {name:'image',index:'image', sortable: false, width: 60, align: 'center'},
                {name:'title', sortable: false, index:'title', align: 'center'}
            ],
            height: 'auto'
        });
    };

    Controller.prototype.fillPostsTable = function() {
        var posts = [],
            sliderId = parseInt($('#sliderID').attr('value'));

        $.post(WordPress.ajax.settings.url,
            {
                slider : sliderId,
                type: 'post',
                action: 'supsystic-slider',
                route: {
                    module: 'slider',
                    action: 'getPosts'
                }
            })
            .success(function (response) {
                console.log(response);

                $.each(response.elements, function(index, value) {
                    var data = {
                        'id': value.id,
                        'image': '<img src=' + value.image + ' width = 60 height = 60>',
                        'title': value.title
                    };

                    jQuery("#posts-table").jqGrid('addRowData', index, data);
                });
                //$.jGrowl(response.message);
            });
    };

    /**
     * Init controller.
     *
     * @type {Function}
     */
    Controller.prototype.init = (function () {
        this.intiPluginSelectWindow();

        this.$submit.on('click', $.proxy(this.submit, this));
        this.$pluginBtn.on('click', $.proxy(this.openPluginSelectWindow, this));
        this.$deleteBtn.on('click', $.proxy(this.deleteSlider, this));
        this.$pluginWindow.find('form').submit(function (e) {
            e.preventDefault();
        });

        this.randomToggle();
        this.formNavigation();
        this.checkWidth();
        this.addPages();
        this.addPosts();
        this.initPostsTable();
        this.fillPostsTable();
    });

    /**
     * Removes the slider.
     * @type {Function}
     */
    Controller.prototype.deleteSlider = (function (e) {
        var id = this.$deleteBtn.data('id'),
            redirectUri = this.$deleteBtn.data('redirect-uri'),
            confirmMsg = this.$deleteBtn.data('confirm');

        if (!confirm(confirmMsg)) {
            e.preventDefault();

            return;
        }

        $.post(WordPress.ajax.settings.url, { id: id, action: 'supsystic-slider', route: { module: 'slider', action: 'delete' } })
            .success(function (response) {
                if (!response.error) {
                    window.location.href = redirectUri;
                }

                $.jGrowl(response.message);
        });
    });

    /**
     * Submit form.
     *
     * @type {Function}
     */
    Controller.prototype.submit = (function () {
        this.$form.submit();
    });

    Controller.prototype.intiPluginSelectWindow = (function () {
        this.$pluginWindow.dialog({
            modal:    true,
            width:    400,
            autoOpen: false,
            buttons:  {
                Change:   function () {
                    $.post(
                        WordPress.ajax.settings.url,
                        $('form#changePlugin').serialize()
                    ).success(
                            $.proxy(
                                function(response) {
                                    if (!response.error) {
                                        window.location.reload(true);
                                    } else {
                                        $.jGrowl(response.message);
                                    }
                                },
                                this
                            )
                        );
                },
                Cancel: function () {
                    $(this).dialog('close');
                }
            }
        });
    });

    Controller.prototype.openPluginSelectWindow = (function () {
        this.$pluginWindow.dialog('open');
    });

    $(document).ready(function () {
        return new Controller();
    });

}(jQuery, window.wp = window.wp || {}));