
Wizard.create = function ( object ) {

	// This should format the stored DOM-structures
	// into a proper data format to pass on when saving
	// and append them to the Wizard.all structure

	Wizard.all.push( {
		'name': object.NAME.val(),
		'age': object.AGE.val()
	} );

};

Wizard.list = function () {

	// This should format the data-structures 
	// This should make all created items into some form of list

	var list = $('<table>').populate( Wizard.all );
	Wizard.insert( list );

};

Wizard.save = function () {

	// This should perform the API requests

	console.log( 'API requesting, pewpewpewpew' );

	$('.wizard-scan-progress').css( 'width', '100%' );
	$('.wizard-scan-status').html( 'Finished saving all objects, you may now close this window!' );
	$('.closebutton').removeAttr('disabled');

};

