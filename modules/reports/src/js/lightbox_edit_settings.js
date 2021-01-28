$(function() {
	var edit_settings_url = $('.edit_settings').attr('href');

	var settings = {

		"limit": 1000,
		"selector": "select[data-filterable]",
		"host": window.location.protocol + "//" + window.location.host,

		"datasource": function ( select ) {

			var type = select.attr( 'data-type' ),
				root = settings.host + _site_domain + _index_page;

			return root + '/listview/fetch_ajax?query=[' + type + 's] all&columns[]=key&limit=1000000';

		},

		"collector": function ( select, data ) {

			var names = [];
			for ( var i = 0; i < data.data.length; i++ ) {
				names.push( data.data[ i ].key );
			}
			select.filterable( names );

		},

		"ajax": {
			dataType: 'json',
			error: function( xhr ) {
				console.log( xhr.responseText );
			}
		}

	};

	$(document).on('click', '.edit_settings', function (e) {
		var href = $(this).attr('href');
		var lightbox = LightboxManager.ajax_form_from_href($(this).text(), href);
		e.preventDefault();
		return false;
	});

	$( document ).ajaxComplete(function( event, xhr, settings ) {
		if ( settings.url === edit_settings_url ) {
			setTimeout(function() {
				filterable_init();
				$( ".lightbox-content" ).css({"display": "block"});
				$('.lightbox-content #header').remove();
				$('.lightbox-content').find('.filter-status').each(lightbox_filter_mapping_mapping);
				$('#report_type').trigger('change');
				$('#report_period').trigger('change');
				$('#show_all').prop( "checked", false ).trigger('change');
				lightbox_set_report_mode($('#report_mode_form input:checked').val());
			}, 0);
		}
	});

	$(document).on('change', '#report_period', function() {
		var val = $('#report_period').val();
		show_calendar(val);
	});

	$(document).on('change', '#show_all', function( e ) {
		if ($(this).is(':checked'))
			$('.obj_selector').hide();
		else
			$('.obj_selector').show();
	});

	function selectload ( index, element ) {

		var select = $( element );

		if ( select.attr( 'data-type' ) ) {

			settings.ajax.success = function ( data ) {
				settings.collector( select, data );
			};

			settings.ajax.url = settings.datasource( select );
			$.ajax( settings.ajax );

		} else if (select.length) {

			var options = $.map( select.children(), function( option ) {
				return option.text;
			});

			select.children().each( function() {
				if (!$(this).attr('selected')) {
					select.find('option[value="' + this.text + '"]').remove();
				}
			} );

			select.filterable( options );

		}

	}

	function filterable_init() {
		var selects = $(settings.selector );
		selects.each(selectload);
	}

	function lightbox_filter_mapping_mapping() {
		if ($(this).is(':checked'))
			$('#' + $(this).data('which')).hide();
		else
			$('#' + $(this).data('which')).show();
		// when checking if the child is visible, the container must be visible
		// or we'd be checking the wrong thing.
		$(this).siblings('.lightbox-content .configure_mapping').show();
		if (!$(this).siblings('.lightbox-content .configure_mapping').find('.lightbox-content .filter_map:visible').length)
			$(this).siblings('.lightbox-content .configure_mapping').hide();
	}

	function lightbox_set_report_mode(type) {
		switch (type) {
			case 'standard':
				$('.lightbox-content .standard').show();
				$('.lightbox-content .custom').remove();
				break;
			case 'custom':
				$(".lightbox-content .standard").remove();
				$('.lightbox-content .custom').show();
				break;
		}
	}
});
