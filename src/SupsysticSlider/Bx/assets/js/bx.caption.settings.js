(function($, app) {

    function initCaptionsEditor() {
        // Set caption settings to Text In / Text Out dialogs
        function setCaptionSettings() {
            var textAlign = $('select[name="[_veditor_][.bx-caption][text-align]"]').val();
            var textColor = $('input[name="[_veditor_][.bx-caption][color]"]').val();
            var bgColor = $('input[name="[_veditor_][.bx-caption][background-color]"]').val();
            var textSize = $('input[name="[_veditor_][.bx-caption][font-size]"]').val();
            var props = {
                'text-align': textAlign,
                'color': textColor,
                'background-color': bgColor,
                'font-size': textSize
            };

            $('div#tieImg').find('div.bx-caption-preview').css(props);
            $('div#toeImg').find('div.bx-caption-preview').css(props);
        }
        // Initial caption settings
        setCaptionSettings();
        // Init Text in effect dialog
        $('#select-tie').on('click', function () {
            $('#selectTextInEffectDialog').dialog({
                autoOpen: false,
                modal:    true,
                height:   600,
                width:    450,
                buttons:  {
                    Cancel: function () {
                        $(this).dialog('close');
                    }
                }
            });
            var selectedTie = $('input#bx-caption-tie').val();
            if(selectedTie) {
                var tiePreview = $('img#tieImg[data-value="'+selectedTie+'"]');
                if(tiePreview) {
                    tiePreview.parent().removeClass().addClass('selectedPreset');
                }
            }
            $('#selectTextInEffectDialog').dialog('open');
        });
        // Init Text out effect dialog
        $('#select-toe').on('click', function () {
            $('#selectTextOutEffectDialog').dialog({
                autoOpen: false,
                modal:    true,
                height:   600,
                width:    450,
                buttons:  {
                    Cancel: function () {
                        $(this).dialog('close');
                    }
                }
            });
            var selectedToe = $('input#bx-caption-toe').val();
            if(selectedToe) {
                var toePreview = $('img#toeImg[data-value="'+selectedToe+'"]');
                if(toePreview) {
                    toePreview.parent().removeClass().addClass('selectedPreset');
                }
            }
            $('#selectTextOutEffectDialog').dialog('open');
        });
        // Init looping text animation in Text in dialog (on mouseenter)
        $('div#tieImg').each(function() {
            $(this).on('mouseenter', function() {
                if($(this).is('.unselectedPreset')) {
                    $(this).css('border-bottom', '1px solid aqua');
                }
                $(this).find('div.bx-caption-preview').find('span').textillate();
            });
            $(this).on('mouseleave', function() {
                if($(this).is('.unselectedPreset')) {
                    $(this).css('border-bottom', '1px solid gray');
                }
            });
        });
        // Select Text in effect
        $('img#tieImg').on('click', function() {
            $('#bx-caption-tie').attr('value', $(this).data('value'));
            $('div.selectedPreset').removeClass().addClass('unselectedPreset');
            $(this).parent().addClass('selectedPreset');
            $('#selectTextInEffectDialog').dialog('close');
        });
        // Init looping text animation in Text out dialog (on mouseenter)
        $('div#toeImg').each(function() {
            $(this).on('mouseenter', function() {
                if($(this).is('.unselectedPreset')) {
                    $(this).css('border-bottom', '1px solid aqua');
                }
                $(this).find('div.bx-caption-preview').find('span').textillate();
            });
            $(this).on('mouseleave', function() {
                if($(this).is('.unselectedPreset')) {
                    $(this).css('border-bottom', '1px solid gray');
                }
            });
        });
        // Select Text out effect
        $('img#toeImg').on('click', function() {
            $('#bx-caption-toe').attr('value', $(this).data('value'));
            $('div.selectedPreset').removeClass().addClass('unselectedPreset');
            $(this).parent().addClass('selectedPreset');
            $('#selectTextOutEffectDialog').dialog('close');
        });
    }

    app.initBuilderCaptions = initCaptionsEditor;

}(jQuery, window.SupsysticSlider = window.SupsysticSlider || {}));