$(document).ready(function() {
    $(document).on('click', '.view-export-details', function(e) {
        var lightbox = LightboxManager.create();
        var title = document.createElement('h1');
        title.textContent = '';
        lightbox.header(title);

        var cancel = document.createElement('input');
        cancel.setAttribute('type', 'button');
        cancel.setAttribute('name', 'close');
        cancel.setAttribute('value', 'Close');
        cancel.setAttribute('class', 'export-close-button');
        lightbox.footer(cancel);

        var fragment = document.createElement('div');
        fragment.innerHTML = '<img ' +
                'src="/ninja/application/media/images/rolling-1s-200px.gif" ' +
                'title="Loading..." width="40" height="40" />';
        lightbox.content(fragment);
        lightbox.show();

        update_data(title, 'current_status');
        update_data(fragment, 'details');

        $(document).on('click', '.lightbox-footer input.export-close-button', function(e){
            fragment.value = false;
            lightbox.hide();
        });

        e.preventDefault();
        return false;
   });

   if($('div#export-page-banner').length > 0) {
       var div = $('div#export-page-banner');
       update_data(div, 'banner_content', true);
   }
});

function update_data(container, url, is_div) {
    if((is_div === true && container.length == 0) || container.value == false) {
        return false;
    }

    $.ajax({
        url : url,
        type : 'GET',
        success : function(data) {
            if(is_div === true) {
                container.html(data);
            } else {
                container.innerHTML = data;
            }
            setTimeout(function() { update_data(container, url, is_div) } , 5000);
        }
    });
    return;
}
