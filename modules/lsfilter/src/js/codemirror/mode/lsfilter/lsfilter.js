// CodeMirror, copyright (c) by Marijn Haverbeke and others
// Distributed under an MIT license: http://codemirror.net/LICENSE

(function(mod) {
  if (typeof exports == "object" && typeof module == "object") // CommonJS
    mod(require("../../lib/codemirror"));
  else if (typeof define == "function" && define.amd) // AMD
    define(["../../lib/codemirror"], mod);
  else // Plain browser env
    mod(CodeMirror);
})(function(CodeMirror) {
"use strict";

CodeMirror.defineMode("lsfilter", function(config, parserConfig) {
  "use strict";

  var client         = parserConfig.client || {},
      atoms          = parserConfig.atoms || {"false": true, "true": true, "null": true},
      builtin        = parserConfig.builtin || {},
      keywords       = parserConfig.keywords || {},
      operatorChars  = parserConfig.operatorChars || /^[*+\-%<>!=&|~^\{\}\[\]]/;

	var unique_id_index = 0;
	function unique_id () {
		unique_id_index++;
		return 'token-id-' + unique_id_index;
	}

  function tokenBase(stream, state) {

		var ch = stream.next();
		if (ch == '[') {
			return (state.tokenize = function(stream, state) {
				var ch;
				while ((ch = stream.next()) != null) {
					if (ch == ']') {
						state.tokenize = tokenBase;
						break;
					}
				}
				return "keyword";
			})(stream, state);
		} else if (ch.charCodeAt(0) > 47 && ch.charCodeAt(0) < 58) {
      stream.match(/^[0-9]*\.?[0-9]+([eE][-+]?[0-9]+)?/);
      return "number";
    } else if (ch == "'" || (ch == '"')) {
			return (state.tokenize = function(stream, state) {
				var escaped = false, ich;
				while ((ich = stream.next()) != null) {
					if (ich == ch && !escaped) {
						state.tokenize = tokenBase;
						break;
					}
					escaped = !escaped && ich == "\\";
				}
				return "string";
			})(stream, state);
    } else if (/^[\(\),\;\[\]]/.test(ch)) {
      // no highlighting
      return null;
    } else if (ch == "/" && stream.eat("*")) {
      return (state.tokenize = function (stream, state) {
				while (true) {
					if (stream.skipTo("*")) {
						stream.next();
						if (stream.eat("/")) {
							state.tokenize = tokenBase;
							break;
						}
					} else {
						stream.skipToEnd();
						break;
					}
				}
				return "comment";
			})(stream, state);
    } else if (ch == ".") {
      return "variable-2";
    } else if (operatorChars.test(ch)) {
      stream.eatWhile(operatorChars);
      return "operator " + unique_id();
    } else {
      stream.eatWhile(/^[_\w\d]/);
      return "variable-3";
    }

  }

  function pushContext(stream, state, type) {
    state.context = {
      prev: state.context,
      indent: stream.indentation(),
      col: stream.column(),
      type: type
    };
  }

  function popContext(state) {
    state.indent = state.context.indent;
    state.context = state.context.prev;
  }

  var ls_filter_mode = {

		startState: function() {
      return {
				tokenize: tokenBase,
				context: null
			};
    },

    token: function(stream, state) {
      if (stream.sol()) {
        if (state.context && state.context.align == null)
          state.context.align = false;
      }
      if (stream.eatSpace()) return null;

      var style = state.tokenize(stream, state);
      if (style == "comment") return style;

      if (state.context && state.context.align == null)
        state.context.align = true;

      var tok = stream.current();
      if (tok == "(")
        pushContext(stream, state, ")");
      else if (tok == "[")
        pushContext(stream, state, "]");
      else if (state.context && state.context.type == tok)
        popContext(state);
      return style;
    },

    indent: function(state, textAfter) {
      var cx = state.context;
      if (!cx) return CodeMirror.Pass;
      var closing = textAfter.charAt(0) == cx.type;
      if (cx.align) return cx.col + (closing ? 0 : 1);
      else return cx.indent + (closing ? 0 : config.indentUnit);
    },

    blockCommentStart: "/*",
    blockCommentEnd: "*/"
  };

	return ls_filter_mode;

});

(function() {
  "use strict";

  // turn a space-separated list into an array
  function set(str) {
    var obj = {}, words = str.split(" ");
    for (var i = 0; i < words.length; ++i) obj[words[i]] = true;
    return obj;
  }

  CodeMirror.defineMIME("text/x-lsfilter", {
    name: "lsfilter",
    keywords: set("and or"),
    builtin: set("date"),
    atoms: set("false true"),
    operatorChars: /^[*+\-%<>!=]/
  });

}());

});

