$(document).on('click', '.edit_settings', function (e) {
    var href = $(this).attr('href');
    var lightbox = LightboxManager.ajax_form_from_href($(this).text(), href);
    e.preventDefault();
    $( ".lightbox-content" ).css({"overflow":"scroll", "display": "block"});

    return false;
});

$( document ).ajaxComplete(function( event, xhr, settings ) {
    if ( settings.url === edit_settings_url ) {
        setTimeout(function() {
            //filterable_init();
            $('.lightbox-content #header').remove();
            $('.lightbox-content').find('.filter-status').each(lightbox_filter_mapping_mapping);
            $('#report_type').trigger('change');
            $('#report_period').trigger('change');
            $('#show_all').trigger('change');
            set_report_mode($('#report_mode_form input:checked').val());
        }, 0);
    }
});

$(document).on('change', 'select#report_type', function( e ) {
    set_selection();
    var val =  e.target.value.replace( /s$/, "" );
    var filterable = jQuery.fn.filterable.find( $('select[name="objects[]"]') ),
        type = e.target.value.replace( /s$/, "" );
    var url = _site_domain + _index_page;
    url += '/listview/fetch_ajax?query=[' + type + 's] all&columns[]=key&limit=1000000';

    if ( filterable ) {
        $.ajax({
            url: url,
            dataType: 'json',
            error: function( xhr ) {
                console.log( xhr.responseText );
            },
            success: function( data ) {
                var names = [];
                for ( var i = 0; i < data.data.length; i++ ) {
                    names.push( data.data[ i ].key );
                }
                filterable.data = new Set( names );
                filterable.reset();
            }
        });
    }
});

$(document).on('change', 'select#report_period', function() {
    var val = $('select#report_period').val();
    show_calendar(val);
});

$(document).on('submit', '.report_form', function() {
    $('.filter-status:visible:checked', this).each(function() {
        $('#' + $(this).data('which')).find('input, select').attr('name', '');
    });
    $('.filter-status:not(:visible)', this).each(function() {
        $('#' + $(this).data('which')).find('input, select').attr('value', '-2');
    });
    return check_form_values();
});

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

$(document).on('change', '#show_all', function( e ) {
    if ($(this).is(':checked'))
        $('.obj_selector').hide();
    else
        $('.obj_selector').show();
});

function set_report_mode(type) {
    switch (type) {
        case 'standard':
            $('.standard').show();
            $('.custom').hide();
            break;
        case 'custom':
            $('.standard').hide();
            $('.custom').show();
            break;
    }
};
$(document).on('change', '#report_mode_form input', function() {
    set_report_mode(this.value);
});