$(document).ready(function() {
   $('#query').autocomplete({
    serviceUrl:_site_domain + _index_page + '/ajax/global_search/',
    minChars:2,
    //delimiter: /(,|;)\s*/, // regex or character
    maxHeight:500,
    width:269,
    deferRequestBy: 0, //miliseconds
    // callback function:
    onSelect: function(value, data){ do_redirect(value, data); }
  });


	var search_old_refresh = 0;
	$('#query').focus(function() {
		search_old_refresh = current_interval;
		ninja_refresh(0);
		$("#ninja_refresh_control").attr('checked', true);
		$('#ninja_refresh_lable').css('font-weight', 'bold');
	});

	$('#query').blur(function() {
		if (current_interval == 0 && search_old_refresh != 0) {
			current_interval = search_old_refresh;
			ninja_refresh(current_interval);
			$("#ninja_refresh_control").attr('checked', false);
			$('#ninja_refresh_lable').css('font-weight', '');
		}
	});


	$('.item_selectcomment').hide();

	// restore left border for first cell of each row
	$('#comment_search_table').find('tr:eq(0) th:eq(0)').css('border-left', '1px solid #dcdccd');
	$('#comment_search_table').find('tr:eq(0) th:eq(1)').css('border-left', '1px solid #dcdccd');
	$('#comment_search_table').find('tr td:nth-child(2)').css('border-left', '1px solid #dcdccd');

	var old_refresh = 0;
	var refresh_is_paused = false;
	$('#select_multiple_comment_items').click(function() {
		if (!refresh_is_paused) {
			if (!$('.item_selectcomment').is(':visible')) {
				// pausing and un-pausing refresh might be
				// irritating for users that already has selected
				// to pause refresh

				// save previous refresh rate
				// to be able to restore it later
				old_refresh = current_interval;
				$('#ninja_refresh_lable').css('font-weight', 'bold');
				ninja_refresh(0);
				$("#ninja_refresh_control").attr('checked', true);
			} else {
				// restore previous refresh rate
				ninja_refresh(old_refresh);
				$("#ninja_refresh_control").attr('checked', false);
				$('#ninja_refresh_lable').css('font-weight', '');
			}
		}

		if ($('.item_selectcomment').is(':hidden'))
			$(	'.item_selectcomment').show();
		else
			$(	'.item_selectcomment').hide();

		return false;
	});

	$('.select_all_items').live('click', function() {
		if ($(this).attr('checked')) {
			//$('.select_all_items').attr('checked', true);
			$(".item_selectcomment input[type='checkbox']").not('.select_all_items').each(function() {
				if (!$(this).attr('disabled') && !$(this).is(':hidden')) {
					$(this).attr('checked', true);
				}
				else if ($(this).is(':hidden')) {
					$(this).attr('checked', false);
				}
			});
		} else {
			$('.select_all_items').attr('checked', false);
			$(".item_selectcomment input[type='checkbox']").not('.select_all_items').each(function() {
				$(this).attr('checked', false);
			});
		}
	});
});

// function from http://forums.devshed.com/t39065/s84ded709f924610aa44fff827511aba3.html
// author appears to be Robert Pollard

function sprintf()
{
   if (!arguments || arguments.length < 1 || !RegExp)
   {
      return;
   }
   var str = arguments[0];
   var re = /([^%]*)%('.|0|\x20)?(-)?(\d+)?(\.\d+)?(%|b|c|d|u|f|o|s|x|X)(.*)/;
   var a = b = [], numSubstitutions = 0, numMatches = 0;
   while (a = re.exec(str))
   {
      var leftpart = a[1], pPad = a[2], pJustify = a[3], pMinLength = a[4];
      var pPrecision = a[5], pType = a[6], rightPart = a[7];

      numMatches++;
      if (pType == '%')
      {
         subst = '%';
      }
      else
      {
         numSubstitutions++;
         if (numSubstitutions >= arguments.length)
         {
            alert('Error! Not enough function arguments (' + (arguments.length - 1)
               + ', excluding the string)\n'
               + 'for the number of substitution parameters in string ('
               + numSubstitutions + ' so far).');
         }
         var param = arguments[numSubstitutions];
         var pad = '';
                if (pPad && pPad.substr(0,1) == "'") pad = leftpart.substr(1,1);
           else if (pPad) pad = pPad;
         var justifyRight = true;
                if (pJustify && pJustify === "-") justifyRight = false;
         var minLength = -1;
                if (pMinLength) minLength = parseInt(pMinLength);
         var precision = -1;
                if (pPrecision && pType == 'f')
                   precision = parseInt(pPrecision.substring(1));
         var subst = param;
         switch (pType)
         {
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
         if (padLeft > 0)
         {
            var arrTmp = new Array(padLeft+1);
            var padding = arrTmp.join(pad?pad:" ");
         }
         else
         {
            var padding = "";
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
	if (data[0] == '') {
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
