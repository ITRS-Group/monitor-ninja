
$(document).ready(function() {

  $('#open-about-button').fancybox({
      'overlayOpacity': 0.7,
      'overlayColor' : '#000000',
      'hideOnContentClick': false,
      'hideOnOverlayClick': false,
      'titleShow': false,
      'showCloseButton': false,
      'enableEscapeButton': false,
      'autoDimensions': true,
      'width': 480,
      'height': 10
  }).click(function () {
    $('#fancybox-outer').css({
      'width': '480px',
      'background-color': '#fff'
    });
    $('#fancybox-content')
      .text('Loading about...')
      .load(_site_domain + 'index.php/menu/about', function() {
        $('#fancybox-content').css({'width': '480px', 'border-width': '0'})
        $.fancybox.center();
        $('#fancybox-close').show();
    });
  });

});