
var Notify = (function () {

  var bar = null;
  var zone = null;
  var init = function () {
    bar  = $('<div>').addClass('notify-notification-bar');
    zone  = $('<div>').addClass('notify-notification-zone');
    $('#header > .clear').append(bar);
    $('#content').append(zone);
  };

  return {

    options: {
      type: "info",
      sticky: false,
      removable: true,
      fadetime: "auto",
      configurable: false,
      buttons: false,
      nag: false,
      icon: true,
      signature: false
    },

    message: function (message, options) {

      if (bar === null) init();

      var key;
      var notification = {
        element: $('<div>').addClass('notify-notification'),
        message: '<span class="notify-notification-message">' + message + '</span>'
      };

      if (typeof(options) === 'object') {
        for (key in Notify.options) {
          if (typeof(options[key]) === 'undefined' || options[key] === null) {
            options[key] = Notify.options[key];
          }
        }
      } else {
        options = Notify.options;
      }

      notification.remove = function (time) {

        time = time ? time : options.fadetime;
        notification.element.fadeOut(400, function () {
          notification.element.remove();
        });

      };

      notification.element.addClass('notify-notification-' + options.type);

      if (options.icon) {
        if (options.type === 'warning') {
          notification.message = '<span class="icon-menu menu-outages"></span>' + notification.message;
        } else if (options.type === 'info') {
          notification.message = '<span class="icon-menu menu-processinfo"></span>' + notification.message;
        } else if (options.type === 'success') {
          notification.message = '<span class="icon-16 x16-start-execute"></span>' + notification.message;
        }  else if (options.type === 'error') {
          notification.message = '<span class="icon-16 x16-remove"></span>' + notification.message;
        }
      }

      notification.element.html(notification.message);

      /**
       * If the nag option is true(thy) we append the notification
       * to the nagbar instead and make it non-removable as well as
       * ignoring sicky and fadetime options.
       */
      if (options.nag) {

        bar.append(notification.element);
        options.removable = false;

      } else {

        zone.append(notification.element);

        if (options.sticky === false) {
          setTimeout(notification.remove, (function () {
            var time = options.fadetime;
            if (time === 'auto') {
              time = (message.length * 150) + 500;
              return (time > 8000) ? 8000 : time;
            } else if (typeof(time) === 'number') {
              return time;
            }
            return 3000;
          }()));
        }

      }

      if (options.removable) {
        if (!options.buttons) {
          options.buttons = {};
        }
        options.buttons['Close'] = function () {};
      }

      if (options.buttons) {

        var wrapper = $('<div>').addClass('notify-notification-buttons');
        var create_button = function (key, callable) {
          return $('<button>').html(key)
            .one('click', function () {
              callable.call(notification);
              notification.remove();
            });
        }
        for (var key in options.buttons) {
          wrapper.append(
            create_button(key, options.buttons[key])
          );
        }

        notification.element.append(wrapper);

      }

      return notification;

    }

  };

}());

$.notify = Notify.message;