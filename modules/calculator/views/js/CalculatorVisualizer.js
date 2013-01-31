var CalculatorPP = function CalculatorPP(){
	this.preprocess_name = function(value) {
		return value;
	};
	
	this.preprocess_float = function(value) {
		return parseFloat(value);
	};
	
	this.preprocess_integer = function(value) {
		return parseInt(value,10);
	};
	
	this.preprocess_op_add = function(value) {
		return value;
	};
	
	this.preprocess_op_sub = function(value) {
		return value;
	};
	
	this.preprocess_op_mult = function(value) {
		return value;
	};
	
	this.preprocess_op_div = function(value) {
		return value;
	};
	
	this.preprocess_par_l = function(value) {
		return value;
	};
	
	this.preprocess_par_r = function(value) {
		return value;
	};
	
	this.preprocess_brace_l = function(value) {
		return value;
	};
	
	this.preprocess_brace_r = function(value) {
		return value;
	};
	
};

var CalculatorEval = function CalculatorEval(){
	// entry: program := * expr end
	this.visit_entry = function(expr0) {
		return expr0;
	};
	
	// expr_add: expr := * expr op_add expr2
	this.visit_expr_add = function(expr0, expr2) {
		return expr0 + expr2;
	};
	
	// expr_sub: expr := * expr op_sub expr2
	this.visit_expr_sub = function(expr0, expr2) {
		return expr0 - expr2;
	};
	
	// expr_mult: expr2 := * expr2 op_mult expr3
	this.visit_expr_mult = function(expr0, expr2) {
		return expr0 * expr2;
	};
	
	// expr_div: expr2 := * expr2 op_dev expr3
	this.visit_expr_div = function(expr0, expr2) {
		return expr0 / expr2;
	};
	
	// expr_neg: expr2 := * op_sub expr3
	this.visit_expr_neg = function(expr1) {
		return -expr1;
	};
	
	// expr_func: expr3 := * name par_l expr par_r
	this.visit_expr_func = function(name0, expr2) {
		var res = expr2;
		switch(name0) {
		case 'sqrt': res = Math.sqrt(expr2); break;
		}
		return res;
	};
	
	// expr_var: expr3 := * name
	this.visit_expr_var = function(name0) {
		/* FIXME: variables */
		return 0;
	};
	
	this.accept = function(result) {
		return result;
	};
	
};

var CalculatorVisualPP = function CalculatorVisualPP(){
	this.preprocess_name = function(value) {
		return [4,$('<span />').text(value)];
	};
	
	this.preprocess_float = function(value) {
		return [4,$('<span />').text(value)];
	};
	
	this.preprocess_integer = function(value) {
		return [4,$('<span />').text(value)];
	};
	
	this.preprocess_op_add = function(value) {
		return value;
	};
	
	this.preprocess_op_sub = function(value) {
		return value;
	};
	
	this.preprocess_op_mult = function(value) {
		return value;
	};
	
	this.preprocess_op_div = function(value) {
		return value;
	};
	
	this.preprocess_par_l = function(value) {
		return value;
	};
	
	this.preprocess_par_r = function(value) {
		return value;
	};
	
	this.preprocess_brace_l = function(value) {
		return value;
	};
	
	this.preprocess_brace_r = function(value) {
		return value;
	};
	
};
var CalculatorVisual = function CalculatorVisual(){
	// entry: program := * expr end
	this.visit_entry = function(expr0) {
		return expr0;
	};
	
	// expr_add: expr := * expr op_add expr2
	this.visit_expr_add = function(expr0, expr2) {
		var base = expr0[1];
		if( expr0[0] > 1 ) {
			base = $('<span />');
			base.append(expr0[1]);
		}
		base.append('+');
		base.append(expr2[1]);
		return [1,base];
	};
	
	// expr_sub: expr := * expr op_sub expr2
	this.visit_expr_sub = function(expr0, expr2) {
		var base = $('<span />');
		base.append(expr0[1]);
		base.append('-');
		if( expr2[0] == 1 ) {
			base.append('(');
			base.append(expr2[1]);
			base.append(')');
		} else {
			base.append(expr2[1]);
		}
		return [1,base];
	};
	
	// expr_mult: expr2 := * expr2 op_mult expr3
	this.visit_expr_mult = function(expr0, expr2) {
		var base = $('<span />');
		if( expr0[0] < 2 ) {
			base.append('(');
			base.append(expr0[1]);
			base.append(')');
		} else {
			base.append(expr0[1]);
		}
		base.append('*');
		if( expr2[0] < 2 ) {
			base.append('(');
			base.append(expr2[1]);
			base.append(')');
		} else {
			base.append(expr2[1]);
		}
		return [2,base];
	};
	
	// expr_div: expr2 := * expr2 op_dev expr3
	this.visit_expr_div = function(expr0, expr2) {
		var base = $('<table style="border: 0; width: auto; font-size: 1em; display: inline-table; vertical-align: middle; margin: 0 0.25em;"/>');
		base.append($('<tr>').append($('<td style="border: 0; padding: 0 0.25em; text-align: center;">').append(expr0[1])));
		base.append($('<tr>').append($('<td style="border-left: 0; border-right: 0; border-bottom: 0; border-top: 1px solid black; padding: 0 0.25em; text-align: center;">').append(expr2[1])));
		return [3,base];
	};
	
	// expr_neg: expr2 := * op_sub expr3
	this.visit_expr_neg = function(expr1) {
		var res = $('<span />');
		res.append( '-' );
		res.append( expr1[1] );
		return [expr1[0],res];
	};
	
	// expr_func: expr3 := * name par_l expr par_r
	this.visit_expr_func = function(name0, expr2) {
		var base = $('<span />').append(name0[1]);
		base.append('(');
		base.append(expr2[1]);
		base.append(')');
		return [4,base];
	};
	
	// expr_var: expr3 := * name
	this.visit_expr_var = function(name0) {
		return name0;
	};
	
	this.accept = function(result) {
		return result[1];
	};
	
};



var visualizeCalculator = function(evt) {
	var string = $('#calculator_query').val();
	try {
		var parser = new Calculator(new CalculatorPP(), new CalculatorEval());
		var result_value = parser.parse(string);
		
		var parser = new Calculator(new CalculatorVisualPP(), new CalculatorVisual());
		var result = parser.parse(string);
		$('#calculator_visual').empty().append($('<span />').append(result).append("= "+result_value));
	} catch( ex ) {
		$('#calculator_visual').empty().append(ex);
	}
}

$().ready(function() {
	visualizeCalculator(false);
	$('#calculator_query').bind('input propertychange',visualizeCalculator);
});
