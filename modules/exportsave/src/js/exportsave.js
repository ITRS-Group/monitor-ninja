$(document).ready(function() {
    $(document).on('click', '.view-export-details', function(e) {
        var lightbox = LightboxManager.create();
        var title = document.createElement('h1');
        title.textContent = '';

        lightbox.header(title);
        var fragment = document.createElement('div');
        fragment.innerHTML = '<img ' +
                'src="/monitor/application/media/images/loading_small.gif" ' +
                'title="Loading..." />';
        lightbox.content(fragment);
        lightbox.show();

        getExportSaveData(fragment);

        $(document).on('click', '.lightbox-header h1 .icon-cancel', function(e){
            fragment.value = false;
        });

        e.preventDefault();
        return false;
   });

   if($('div#export-page-banner').length > 0) {
       var div = $('div#export-page-banner');
       getExportBreifData(div);
   }
});

function getExportBreifData(div) {
    if($('div#export-page-banner').length == 0) {
        return false;
    }
    $.ajax({
        url : 'bannercontent',
        type : 'GET',
        success : function(data) {
            div.html(data);
            setTimeout(function() { getExportBreifData(div) } , 5000);
        }
    });
}

function getExportSaveData(fragment) {
    if(fragment.value == false) {
        return false;
    }

    $.ajax({
        url : 'details',
        type : 'GET',
        success : function(data) {
            fragment.innerHTML = data;
            setTimeout(function() { getExportSaveData(fragment) } , 5000);
        }
    });
    return;
}