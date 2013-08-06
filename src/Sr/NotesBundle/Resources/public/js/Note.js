/**
 * Utile pour la page d'édition des notes.
 * @returns {undefined}
 */
var Note = function($container, options) {
    var that = this;

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
        var data = {
            id: options.id,
            titre:$('#titre').val(),
            note:$('#description').val(),
            parentId: $('#parentId').val(),
            prochaine:$('#prochaine').attr('checked') == 'checked',
            enAttente:$('#enAttente').attr('checked') == 'checked',
            dateLimite:$('#dateLimite').val(),
            pcAvancement:$('#pcAvancement').val(),
            tags:$('#tags').val(),
            type: ($('#typeTache').attr('checked') == 'checked' ? 1
                : ($('#typeReference').attr('checked') == 'checked' ? 2
                : ($('#typeProjet').attr('checked') == 'checked' ? 3 : 0)))
        };

        console.log(data); return;

        $('#btnEnregistrer').val("En cours d'enregistrement...");

        $.ajax("{{ path('sr_notes_note_save') }}", {
            type: "POST",
            data: data,
            success: function(data) {
                $('#btnEnregistrer').val("Enregistré.");
                window.setTimeout(function(){
                    $('#btnEnregistrer').val("Enregistrer");
                }, 3000);
            }
        });
    };

    construct();
};

