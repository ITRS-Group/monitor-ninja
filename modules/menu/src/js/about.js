
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
  }).click(function (ev) {
    ev.preventDefault();
    $('#fancybox-outer').css({
      'width': '480px',
      'background-color': '#fff'
    });
    $('#fancybox-content')
      .text('Loading about...')
      .load($(this).attr('href'), function() {
        $('#fancybox-content').css({'width': '480px', 'border-width': '0'})
        $.fancybox.center();
        $('#fancybox-close').show();
    });
  });

});
