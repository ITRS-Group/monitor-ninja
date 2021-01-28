(function () {
	"use strict";
	$(function () {
		$('.nj-form-field-listview-query').each(function () {

			var field = $(this);
			var textarea = field.find('textarea').get(0);
			var validator_to = null;

			var editor = CodeMirror.fromTextArea(textarea, {
				lineNumber: true,
				mode: "text/x-lsfilter",
				hintOptions: {
					structure: orm_structure,
					completeSingle: false
				}
			});

			editor.on('keyup', function (e, s) {

				var mode = CodeMirror.resolveMode("text/x-lsfilter");
				var operator_matcher = mode.operatorChars;

				if (s.key.match(/^[\d\w\{\}\[\]\.\_]$/) || s.key.match(operator_matcher))	{
					CodeMirror.commands.autocomplete(editor);
				}

			});

			editor.on('change', function () {

				clearTimeout(validator_to);
				validator_to = setTimeout(function () {

					var query = editor.getValue();

					var visitor = new LSFilterMetadataVisitor();
					var parser = new LSFilterParser(visitor);
					var preprocessor = new LSFilterPreprocessor(parser);
					var lexer = new LSFilterLexer(query, preprocessor);

					try {
						var result = parser.parse(lexer);
						field.find('.CodeMirror').addClass('success');
						field.find('.CodeMirror').removeClass('error');
					} catch (error) {
						field.find('.CodeMirror').removeClass('success');
						field.find('.CodeMirror').addClass('error');
					}

				}, 150);

			});

		});


	});
}());
