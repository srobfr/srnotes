/**
 * Utile pour la page d'édition des notes.
 * @returns {undefined}
 */
var Note = function($container, options) {
    var that = this;

    var btnSaveLabel = $('.btnEnregistrer').val();
    var btnSaveBgColor = $('.btnEnregistrer').css('background-color');
    var currentEditorType = 'simple';
    var aceEditor = null;

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

        $('li.simpleEditor a', 'ul.editorsChoice').click(function(e) { $('.saisieNoteContainer').data('note').toggleEditor('simple'); return false ;});
        $('li.aceEditor a', 'ul.editorsChoice').click(function(e) { $('.saisieNoteContainer').data('note').toggleEditor('ace'); return false ;});
        $('li.markdownEditor a', 'ul.editorsChoice').click(function(e) { $('.saisieNoteContainer').data('note').toggleEditor('markdown'); return false ;});

        that.toggleEditor('simple');
    };

    /**
     * Switche d'un éditeur à un autre
     * @param string editor ('simple', 'ace', 'markdown')
     * @returns void
     */
    that.toggleEditor = function(editor) {
        var descriptionContent = that.getDescriptionContent();
        var $editorContainer = $('.editorContainer');
        $editorContainer.empty();
        $editorContainer.css({ height: '' });

        $('.editorsChoice li').removeClass('active');

        var editorsInit = {

            /**
             * Simple textarea.
             */
            simple: function() {
                $('.editorsChoice li.simpleEditor').addClass('active');
                var $editor = $('<textarea placeholder="Description" id="description" class="inputDescription" onkeydown="insertTab(event)"></textarea>');
                $editor.val(descriptionContent);
                $editorContainer.append($editor);
                $editor.autosize();
            },

            /**
             * Editeur de code.
             */
            ace: function() {
                $('.editorsChoice li.aceEditor').addClass('active');
                var $editor = $('<div id="aceEditor">');

                $editorContainer.append($editor);
                $editor.text(descriptionContent);
                aceEditor = ace.edit("aceEditor");
                aceEditor.setTheme("ace/theme/crimson_editor");

                var heightUpdateFunction = function() {
                    var newHeight =
                            aceEditor.getSession().getScreenLength()
                            * aceEditor.renderer.lineHeight
                            + aceEditor.renderer.scrollBar.getWidth();

                    newHeight = Math.max(newHeight, 100);

                    $editorContainer.height(newHeight.toString() + "px");
                    $editor.height(newHeight.toString() + "px");
                    aceEditor.resize();
                };

                heightUpdateFunction();
                aceEditor.getSession().on('change', heightUpdateFunction);

                var modes = {
                    'php': new RegExp('(<\?php|lang:PHP)'),
                    'javascript': new RegExp('lang:JS'),
                    'markdown': new RegExp('lang:MKDOWN')
                };

                var mode = null;
                for(var k in modes) {
                    if(descriptionContent.match(modes[k])) {
                        mode = k;
                    }
                }

                if(mode !== null) {
                    aceEditor.getSession().setMode("ace/mode/" + mode);
                }

                aceEditor.focus();
            },

            /**
             * Editeur markdown.
             */
            markdown: function() {
                $('.editorsChoice li.markdownEditor').addClass('active');

                var $row = $('<div class="row"></div>');
                $editorContainer.append($row);

                var $editor = $('<div id="aceEditor" class="col-lg-6">');
                $editor.text(descriptionContent);
                $row.append($editor);
                
                var $previewMarkdown = $('<div id="previewMarkdown" style="overflow: auto; border: 1px solid #dadada;"></div>');
                $row.append($('<div class="col-lg-6" style="float:right;">').append($previewMarkdown));
                
                if(aceEditor !== null) {
                    aceEditor.getSession().off('change');
                    aceEditor.destroy();
                }

                aceEditor = ace.edit("aceEditor");
                aceEditor.setTheme("ace/theme/crimson_editor");
                aceEditor.getSession().setMode("ace/mode/markdown");

                var descriptionChangeHandler = function(event) {
                    if(event) event.preventDefault();
                    var converter = new Showdown.converter();
                    $previewMarkdown.html(converter.makeHtml(that.getDescriptionContent()));
                };

                aceEditor.getSession().on('change', descriptionChangeHandler);
                setTimeout(descriptionChangeHandler(), 0);

                var heightUpdateFunction = function() {
                    var newHeight =
                            aceEditor.getSession().getScreenLength()
                            * aceEditor.renderer.lineHeight
                            + aceEditor.renderer.scrollBar.getWidth();

                    newHeight = Math.max(newHeight, 100);
                    $previewMarkdown.css('height', (newHeight + 2).toString() + "px");

                    $editorContainer.height(newHeight.toString() + "px");
                    $editor.height(newHeight.toString() + "px");
                    aceEditor.resize();
                };

                heightUpdateFunction();
                aceEditor.getSession().on('change', heightUpdateFunction);
                aceEditor.focus();
            }
        }

        currentEditorType = editor;
        editorsInit[editor]();
    };

    /**
     * Retourne le contenu actuel de l'éditeur.
     * @returns string
     */
    that.getDescriptionContent = function() {
        var strategies = {
            simple: function() { return $('#description').val(); },
            ace: function() { return aceEditor.getSession().getValue(); },
            markdown: function() { return aceEditor.getSession().getValue(); }
        };

        return strategies[currentEditorType]();
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
            note: that.getDescriptionContent(),
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

