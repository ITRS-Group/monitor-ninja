
var Notify = (function () {

  var bar = null;
  var zone = null;
  var init = function () {
    bar  = $('<div>').addClass('notify-notification-bar');
    zone  = $('<div>').addClass('notify-notification-zone');
    $('#header > .clear').append(bar);
    $('#content').append(zone);
  };

  var current = [];

  return {

    options: {
      /* info, warning, error, success */
      type: "info",
      sticky: false,
      /* Can the message be removed? otherwise it will not
        fade away nor be closable by button */
      removable: true,
      /* Time before a non-sticky message fades away, ms or 'auto' */
      fadetime: "auto",
      /* Animation time for fade-out */
      removetime: 400,
      configurable: false,
      buttons: false,
      nag: false,
      icon: true,
      signature: false
    },

    /**
     * Usage:
     *  Notify.clear() - Clear every notification
     *  Notify.clear('message') - Clear message notifications
     *  Notify.clear('nagbar') - Clear nagbar notifications
     *
     * @param  {string}  type  The notification type
     */
    clear: function (type) {

      var i = current.length;
      type = (typeof(type) === 'string') ? type : 'all';

      for (i; i--; ) {
        if (current[i].type === type || type === 'all') {
          current[i].remove();
        }
      }

    },

    /**
     * See options above for possible options and their values
     *
     * @param  {string} message  The notification message
     * @param  {object} options  Options
     * @return {notification}    The created notification
     */
    message: function (message, options) {

      if (bar === null) init();

      var key;
      var notification = {
        element: $('<div>').addClass('notify-notification'),
        message: '<span class="notify-notification-message">' + message + '</span>',
        type: 'message'
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

        var index = current.indexOf(notification);
        current.splice(index, 1);

        time = time ? time : options.removetime;

        if (notification.type === 'nagbar') {
          notification.element.remove();
        } else {
          notification.element.fadeOut(time, function () {
            notification.element.remove();
          });
        }

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
        notification.type = 'nagbar';

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

      current.push(notification);
      return notification;

    }

  };

}());

$.notify = Notify.message;