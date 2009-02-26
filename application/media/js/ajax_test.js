$(document).ready(function() {
	console.log('ready');
      //attach onSubmit to the form
	$('#test_form').submit(function(){
		console.log('submit!');
		//return false;
		//When submitted do an ajaxSubmit
		$(this).ajaxSubmit({
			dataType: 'json',
			success: function(data, responseCode) {
				//Show flash message as first element after body
				$('#response').html(data.response).fadeIn();
				console.log(data.response);
				//Show for 5 seconds
				/*
				setTimeout(function(){
					$('#response').fadeOut();
				}, 5000);
				*/
			}
		});
          //return false to prevent normal submit
          return false;
      });
	}
);

function clearInput()
{
	$('#response').text('');
}