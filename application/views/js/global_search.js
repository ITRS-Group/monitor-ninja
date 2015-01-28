$(document).ready(function() {
	var query = $('#query');
	if(!query.length) {
		// fix to avoid autocomplete if element is not present.
		// the autocomplete source is minified and we've got a demo to run..
		// new-host wizard fails without this
		return;
	}
	query.autocomplete({
		serviceUrl:_site_domain + _index_page + '/search/ajax_auto_complete/',
		minChars:2,
		//delimiter: /(,|;)\s*/, // regex or character
		maxHeight:500,
		width:'auto',
		deferRequestBy: 300, //miliseconds
		// callback function:
		onSelect: function(value, data){ do_redirect(value, data); }
	});


	var search_old_refresh = 0;
	query.focus(function() {
		search_old_refresh = current_interval;
		ninja_refresh(0);
		$("#ninja_refresh_control").attr('checked', true);
		$('#ninja_refresh_lable').css('font-weight', 'bold');
	});

	query.blur(function() {
		if (current_interval === 0 && search_old_refresh !== 0) {
			current_interval = search_old_refresh;
			ninja_refresh(current_interval);
			$("#ninja_refresh_control").attr('checked', false);
			$('#ninja_refresh_lable').css('font-weight', '');
		}
	});
});

// function from http://forums.devshed.com/t39065/s84ded709f924610aa44fff827511aba3.html
// author appears to be Robert Pollard

function sprintf()
{
	if (!arguments || arguments.length < 1 || !RegExp) {
		return;
	}
	var str = arguments[0];
	var re = /([^%]*)%('.|0|\x20)?(-)?(\d+)?(\.\d+)?(%|b|c|d|u|f|o|s|x|X)(.*)/;
	var a = [], numSubstitutions = 0, numMatches = 0;
	var padding, subst;
	while (a = re.exec(str)) {
		var leftpart = a[1], pPad = a[2], pJustify = a[3], pMinLength = a[4];
		var pPrecision = a[5], pType = a[6], rightPart = a[7];

		numMatches++;
		if (pType == '%') {
			subst = '%';
		}
		else
		{
			numSubstitutions++;
			if (numSubstitutions >= arguments.length) {
				console.error('Error! Not enough function arguments (' + (arguments.length - 1)
				+ ', excluding the string)\n'
				+ 'for the number of substitution parameters in string ('
				+ numSubstitutions + ' so far).');
			}
			var param = arguments[numSubstitutions];
			var pad = '';
			if (pPad && pPad.substr(0,1) == "'")
				pad = leftpart.substr(1,1);
			else if (pPad)
				pad = pPad;
			var justifyRight = true;
			if (pJustify && pJustify === "-")
				justifyRight = false;
			var minLength = -1;
			if (pMinLength)
				minLength = parseInt(pMinLength, 10);
			var precision = -1;
			if (pPrecision && pType == 'f')
				   precision = parseInt(pPrecision.substring(1));
			subst = param;
			switch (pType) {
				case 'b':
					subst = parseInt(param).toString(2);
					break;
				case 'c':
					subst = String.fromCharCode(parseInt(param));
					break;
				case 'd':
					subst = parseInt(param) ? parseInt(param) : 0;
				break;
				 case 'u':
					subst = Math.abs(param);
					break;
				 case 'f':
					subst = (precision > -1)
						? Math.round(parseFloat(param) * Math.pow(10, precision))
						/ Math.pow(10, precision)
						: parseFloat(param);
					break;
				 case 'o':
					subst = parseInt(param).toString(8);
					break;
				 case 's':
					subst = param;
					break;
				 case 'x':
					subst = ('' + parseInt(param).toString(16)).toLowerCase();
					break;
				 case 'X':
					subst = ('' + parseInt(param).toString(16)).toUpperCase();
					break;
			}
			var padLeft = minLength - subst.toString().length;
			if (padLeft > 0) {
				var arrTmp = new Array(padLeft+1);
				padding = arrTmp.join(pad?pad:" ");
			} else {
				padding = "";
			}
		}
		str = leftpart + padding + subst + rightPart;
	}
	return str;
}



function do_redirect(value, data)
{
	// if user clicked on information message about
	// nr of rows returned, we shouldn't do anything
	if (data[0] === '') {
		$('#query').val('');
		return false;
	}

	var match = value.split(';');
	if (match.length) {
		value = match[1];
	}

	var str = sprintf(data[0], encodeURIComponent(data[1]), value);
	self.location.href = _site_domain + _index_page + str;
}
