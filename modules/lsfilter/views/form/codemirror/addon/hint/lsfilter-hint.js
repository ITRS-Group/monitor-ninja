// CodeMirror, copyright (c) by Marijn Haverbeke and others
// Distributed under an MIT license: http://codemirror.net/LICENSE

(function(mod) {
  if (typeof exports == "object" && typeof module == "object") // CommonJS
    mod(require("../../lib/codemirror"), require("../../mode/lsfilter/lsfilter"));
  else if (typeof define == "function" && define.amd) // AMD
    define(["../../lib/codemirror", "../../mode/lsfitler/lsfilter"], mod);
  else // Plain browser env
    mod(CodeMirror);
})(function(CodeMirror) {
  "use strict";

	var LSFilterHint = {

		_structure: {},
		_table: '',
		_fields: [],
		_tables: [],

		set_structure: function (structure) {
			LSFilterHint._structure = structure;
			LSFilterHint._tables = Object.keys(LSFilterHint._structure);
		},

		set_table: function (table) {
			LSFilterHint._table = table;
			LSFilterHint._fields = Object.keys(LSFilterHint._structure[table]);
		},

		is_object: function (field) {
			if (LSFilterHint._table && LSFilterHint._structure[LSFilterHint._table][field]) {
				return (LSFilterHint._structure[LSFilterHint._table][field][0] === 'object');
			} else {
				return false;
			}
		},

		get_field_object_type: function (field) {
			if (LSFilterHint.is_object(field)) {
				return LSFilterHint._structure[LSFilterHint._table][field][1];
			} else {
				return false;
			}
		},

		get_operator_match: function (search, field, formatter) {

			var operators = [];
			if (!LSFilterHint._table || !LSFilterHint._structure[LSFilterHint._table][field]) {

				operators = Object.keys(lsfilter_graphics_visitor.operators).reduce(function (ops, type) {

					var type_ops = Object.keys(lsfilter_graphics_visitor.operators[type]).map(function (operator_name) {
						return lsfilter_dom_to_query.operators[operator_name];
					});

					type_ops.forEach(function (type_op) {
						if (ops.indexOf(type_op) < 0) {
							ops.push({
								operator: type_op,
								lhs_type: type
							});
						}
					});

					return ops;

				}, []);

			} else {

				var type = LSFilterHint._structure[LSFilterHint._table][field][0];
				operators = Object.keys(lsfilter_graphics_visitor.operators[type]).map(function (operator_name) {
					return {
						operator: lsfilter_dom_to_query.operators[operator_name],
						lhs_type: type
					};
				});

				if (type==="string") {
					formatter = function (value) { return value + ' ""'}
				}

			}

			return operators.reduce(function (matches, op) {
				if (op.operator.toUpperCase().indexOf(search.toUpperCase()) >= 0)
					matches.push({
						operator: formatter ? formatter(op.operator) : op.operator,
						lhs_type: op.lhs_type
					});
				return matches;
			}, []).sort(function (a, b) {
				if (a.length > b.length) return 1;
				else if (a.length < b.length) return -1;
				else return 0;
			});

		},

		get_table_match: function (search, formatter) {

			return LSFilterHint._tables.reduce(function (matches, table) {
				var matcher = new RegExp(search, 'i')
				if (table.toUpperCase().indexOf(search.toUpperCase()) >= 0)
					matches.push(formatter ? formatter(table) : table);
				return matches;
			}, []).sort(function (a, b) {
				if (a.length > b.length) return 1;
				else if (a.length < b.length) return -1;
				else return 0;
			});

		},

		get_field_match: function (search, formatter) {

			return LSFilterHint._fields.reduce(function (matches, field) {
				if (field.toUpperCase().indexOf(search.toUpperCase()) >= 0)
					matches.push(formatter ? formatter(field) : field);
				return matches;
			}, []).sort(function (a, b) {
				if (a.length > b.length) return 1;
				else if (a.length < b.length) return -1;
				else return 0;
			});
		},

		get_foreign_field_match: function (search, field, formatter) {

			if (!LSFilterHint.is_object(field)) {
				return [];
			}

			search = search.substring(1);
			var foreign_table = LSFilterHint.get_field_object_type(field);
			var fields = Object.keys(LSFilterHint._structure[foreign_table]);

			return fields.reduce(function (matches, field) {
				if (field.toUpperCase().indexOf(search.toUpperCase()) >= 0)
					matches.push(formatter ? formatter(field) : field);
				return matches;
			}, []).sort(function (a, b) {
				if (a.length > b.length) return 1;
				else if (a.length < b.length) return -1;
				else return 0;
			});
		}


	};

  CodeMirror.registerHelper("hint", "lsfilter", function(editor, options) {

		LSFilterHint.set_structure(options.structure);
		LSFilterHint.cursor = editor.getCursor();

    var result = [];
    var cursor = editor.getCursor();
    var token = editor.getTokenAt(cursor);
		var search;

		var mode = CodeMirror.resolveMode("text/x-lsfilter");
		var keywords = Object.keys(mode.keywords);
		var operator_matcher = mode.operatorChars;

		var select_table = false;

		if (token.string[0] == '[' && token.start == 0) {

			if (token.string[token.string.length - 1] === ']') {
				search = token.string.substring(1, token.string.length - 1);
			} else search = token.string.substring(1);

			select_table = true;
			result = LSFilterHint.get_table_match(search, function (value) {
				return "[" + value + "]";
			});

		} else if (token.string.match(operator_matcher)) {

			search = token.string;
			var last_token = editor.getTokenAt(CodeMirror.Pos(cursor.line, token.start - 2));
			var operators = LSFilterHint.get_operator_match(search, last_token.string);

			result = operators.map(function (op) {
				return op.operator;
			});

			var token_id = token.type.split(' ')[1];
			var viewport = editor.getViewport();
			console.log(viewport, token_id);

		} else {

			search = token.string;

			if (search.charAt(0) == ".") { /* Dot member syntax */
				var last_token = editor.getTokenAt(CodeMirror.Pos(cursor.line, token.start - 2));
				result = LSFilterHint.get_foreign_field_match(search, last_token.string, function (value) {
					return "." + value;
				});
			} else {
				result = LSFilterHint.get_field_match(search).concat(
					keywords.reduce(function (words, word) {
						if (word.toUpperCase().indexOf(search.toUpperCase()) >= 0)
							words.push(word);
						return words;
					}, [])
				);
			}
		}

		var completion = {
			list: result,
			from: CodeMirror.Pos(cursor.line, token.start),
			to: CodeMirror.Pos(cursor.line, token.end)
		};

		CodeMirror.on(completion, 'pick', function (value) {

			if (select_table) {
				LSFilterHint.set_table(value.substring(1, value.length - 1));
			}

			if (operators) {
				var operator = operators.reduce(function (found, op) {
					if (op.operator === value)
						return op;
					return found;
				}, null);
				if (operator && operator.lhs_type === 'string') {
					editor.setCursor(
						cursor.line,
						cursor.ch + 2
					);
				}
			}

		});

		return completion;

  });
});
