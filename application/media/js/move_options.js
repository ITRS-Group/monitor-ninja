var NS4 = (navigator.appName == "Netscape" && parseInt(navigator.appVersion) < 5);

/**
*	Loop through all elements of a form
*	Verify that all multiselect fields (right hand side)
*	are set to selected
*/
function loopElements(f) {
	// Specify which form fields (select) we are NOT interested in
	var nosave_suffix = "_tmp";

	for(i=0; i<f.elements.length; i++){
		if (f.elements[i].type=="select-multiple"){
			// set all options selected if we can't find
			//the nosave_suffix in element name
			if (f.elements[i].name.search(nosave_suffix)<0){
				for (a=0;a<f.elements[i].length;a++){
					f.elements[i].options[a].selected=true;
				}
			} else {
				// Unselect all other options
				for (a=0;a<f.elements[i].length;a++){
					f.elements[i].options[a].selected=false;
				}
			}
		}
	}
}

/**
*	Disable a form element, change the className and
*	display a new value
*	Primarily for use on submit buttons where return values
*	not needed for further processing (e.g. host_wizard).
*/
function setDisabled(theID, textStr)
{
	theElement 					= document.getElementById(theID);
	theElement.className		= 'buttonTestService';
	theElement.disabled 		= true;
	theElement.value 			= textStr;
}

/**
*	cache the progress indicator image to show faster...
*/
var Image1 = new Image(16,16);
Image1.src = _site_domain + '/application/media/images/loading.gif';

/**
*	Show a progress indicator to inform user that something
*	is happening...
*/
function show_progress(the_id, info_str)
{
	$("#" + the_id).html('<br /><img id="progress_image_id" src="' + Image1.src + '"> ' + info_str).show();
}

/**
*	Enable a form element, change the className and
*	display a new value
*	Primarily for use on submit buttons where return values
*	not needed for further processing (e.g. host_wizard).
*/
function setEnabled(theID, textStr)
{
	theElement 					= document.getElementById(theID);
	theElement.className		= "buttonTestService";
	theElement.disabled 		= false;
	theElement.value 			= textStr;
}

function addOption(theSel, theText, theValue, theSelFrom)
{
	var newOpt = new Option(theText, theValue);
	if (theSelFrom) {
		newOpt.ondblclick = function(){
			moveOptions(theSel, theSelFrom);
		}
	}
	theSel.options[theSel.length] = newOpt;
}
function deleteOption(theSel, theIndex)
{
	if(theSel.length>0)
	{
		theSel.options[theIndex] = null;
	}
}

function moveOptions(theSelFrom, theSelTo)
{
	var selectedText 	= new Array();
	var selectedValues 	= new Array();
	var selectedCount 	= 0;

	var i;

	// Find the selected Options in reverse order
	// and delete them from the 'from' Select.
	for(i=theSelFrom.length-1; i>=0; i--)
	{	// only transfer selected AND visible items
		// The 'visible' condition is added because
		// of the new filter function which else will
		// transfer hidden options between the selected
		// ones.
		if(theSelFrom.options[i].selected && theSelFrom.options[i].style.display != 'none')
		{
			selectedText[selectedCount] 	= theSelFrom.options[i].text;
			selectedValues[selectedCount] 	= theSelFrom.options[i].value;
			deleteOption(theSelFrom, i);
			selectedCount++;
		} else {
			// The following might seem strange but the purpose is
			// to unselect the hidden options when removing a possible
			// filter.
			theSelFrom.options[i].selected = false;
		}
	}

	// Add the selected text/values in reverse order.
	// This will add the Options to the 'to' Select
	// in the same order as they were in the 'from' Select.
	for(i=selectedCount-1; i>=0; i--)
	{
		addOption(theSelTo, selectedText[i], selectedValues[i], theSelFrom);
	}
	sortlist(theSelFrom);
	sortlist(theSelTo);

	if(NS4) history.go(0);
}

function sortlist(the_sel) {
	arrTexts = new Array();

	for(i=0; i<the_sel.length; i++)  {
		arrTexts[i] = the_sel.options[i].text;
	}

	arrTexts.sort();

	for(i=0; i<the_sel.length; i++)  {
		the_sel.options[i].text = arrTexts[i];
		the_sel.options[i].value = arrTexts[i];
	}
}

/**
*	Check if needle exists in haystack
*/
function inArray(needle, haystack)
{
	var i;
	var found = false;
	for (i=0;i<haystack.length;i++){
		if (needle.search(haystack[i])>0){
			return true;
		}
	}
	return false;
}

/**
*	Remove whitespace from string
*/
function trim(str) {
	return str.replace(/^\s+|\s+$/g,"");
}

/**
*	Remove '[', ']' and other things to make element name
*	understandable to user...
*/
function getElementName(str)
{
	var elem = ""; // will hold a more readable name of current element name

	// locate first closing ]
	var startpos = str.indexOf(']');
	str = str.substring(startpos, str.length);

	// Remove brackets
	str = str.replace(/\]/g, "");
	str = str.replace(/\[/g, "");

	return trim(str);
}

