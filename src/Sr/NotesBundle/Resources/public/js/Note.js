/**
 * Utile pour la page d'édition des notes.
 * @returns {undefined}
 */
var Note = function($container, options) {
    var that = this;

    var btnSaveLabel = $('.btnEnregistrer').val();
    var btnSaveBgColor = $('.btnEnregistrer').css('background-color');

    /**
     * Constructeur.
     */
    var construct = function() {
        $('#tags').tagsInput({
            //'autocomplete_url': url_to_autocomplete_api,
            //'autocomplete': {option: value, option: value},
            'height': '24px',
            'width': '100%',
            'interactive': true,
            'defaultText': 'Tags',
            //'onAddTag': callback_function,
//            'onRemoveTag': callback_function,
//            'onChange': callback_function,
            'removeWithBackspace': true,
            //'minChars': 0,
            //'maxChars': 0, //if not provided there is no limit,
            'placeholderColor' : '#666666'
        });

        // Capture de Ctrl+S
        $(document).keydown(function(e) {
            if(e.ctrlKey && e.keyCode == 83/*S*/) {
                that.save();
                return false;
            }
            return true;
        });

        // Bouton enregistrer
        $('.btnEnregistrer').click(function() {
            that.save();
            return false;
        });

        // Type de la note
        $('.noteType li').click(function() {
            $('.noteType li').removeClass('active');
            $(this).addClass('active');
        });

        // Upload de fichiers
        new PunkAveFileUploader({
            'uploadUrl': options.uploadUrl,
            'viewUrl': options.uploadViewUrl,
            'el': '.file-uploader',
            'existingFiles': options.existingFiles,
            'errorCallback': function( info ) { alert("Error : " + info.error); }
        });

        
    };

    /**
     * Sauvegarde la note.
     * @returns {undefined}
     */
    that.save = function() {
        $('.btnEnregistrer').addClass('disabled');
        $('.btnEnregistrer').val($('.btnEnregistrer').attr('data-loading-text'));
        
        var data = {
            id: options.id,
            titre: $('#titre').val(),
            note: $('#description').val(),
            parentId: $('#parents').val(),
            prochaine: $('.prochaine input').is(':checked'),
            enAttente: $('.attente input').is(':checked'),
            dateLimite: $('input[name="dateLimite"]').val(),
            pcAvancement: ($('#chkBxTermine').is(':checked') ? 100 : 0),
            tags:$('#tags').val(),
            type: $('ul.noteType li.active').attr('data-type')
        };

        $.ajax(options.saveNoteUrl, {
            type: "POST",
            data: data,
            success: function(data) {
                $('.btnEnregistrer')
                    .val(btnSaveLabel)
                    .removeClass('disabled')
                    .css({ 'background-color': 'greenyellow' })
                    .animate({ 'background-color' : btnSaveBgColor }, 2000);
            },
            error: function(data) {
                $('.btnEnregistrer')
                    .val(btnSaveLabel)
                    .removeClass('disabled')
                    .css({ 'background-color': 'red' })
                    .animate({ 'background-color' : btnSaveBgColor }, 2000);
            },
        });
    };

    construct();
};

