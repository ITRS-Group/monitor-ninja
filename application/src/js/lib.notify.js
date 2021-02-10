
var Notify = (function () {

  var zones = null;
  var active = [];

  var initialize = function () {
    zones = {};
    zones.nagbar = $('<div>').addClass('notify-notification-bar');
    zones.message = $('<div>').addClass('notify-notification-zone');

    $('#header > .clear').append(zones.nagbar);
    $('#content').append(zones.message);
  };

  var fadesettings = {
    "constanttime": 500,
    "wordtime": 500,
    "maxtime": 10000,
    "default": 5000
  };

  /**
   * Creates a new notification object but has not yet
   * applied option-specific behaviour.
   */
  var Notification = function (message, options) {

    if (typeof(options) === 'object') {
      for (key in Notify.options) {
        if (typeof(options[key]) === 'undefined' || options[key] === null) {
          options[key] = Notify.options[key];
        }
      }
    } else {
      options = Notify.options;
    }

    var notification = {
      'element': $('<div>')
        .addClass('notify-notification')
        .addClass(options.type),
      'message': '<span class="notify-notification-message">' + message + '</span>',
      'options': options,
      'zone': 'message'
    };

    var getIndexOf = function (a, value) {
      if (a.indexOf) {
        return a.indexOf(notification);
      } else {
        var i = 0;
        for (i = 0; i < a.length; i++) {
          if (value === a[i]) {
            return i;
          }
        }
        return -1;
      }
    }

    notification.remove = function (time) {

      var index = getIndexOf(active, notification);
      time = time ? time : notification.options.animationtime;
      active.splice(index, 1);

      if (notification.zone === 'nagbar') {
        notification.element.remove();
        if (typeof(notification.options.remove) === 'function') {
          notification.options.remove();
        }
      } else {
        notification.element.fadeOut(time, function () {
          notification.element.remove();
          if (typeof(notification.options.remove) === 'function') {
            notification.options.remove();
          }
        });
      }

    };

    notification.button = function (label, callback) {

      if (!notification.buttonwrap) {
        notification.buttonwrap = $('<div>')
          .addClass('notify-notification-buttons');
        notification.element.append(notification.buttonwrap);
      }

      notification.buttonwrap.append(
        $('<button>').html(label)
          .on('click', function () {
            callback.call(null, notification);
          })
      );

    };

    notification.fadetime = function () {

      var time = notification.options.fadetime;
      var words = message.split(/\s+/);

      if (time === 'auto') {
        time = (words.length * fadesettings.wordtime);
        time += fadesettings.constanttime;
        return (time > fadesettings.maxtime) ? fadesettings.maxtime : time;
      }

      return (typeof(time) === 'number') ? time : fadesettings['default'];

    };

    return notification;

  };

  return {

    options: {
      /* info, warning, error, success */
      type: "info",
      /* Sticky messages */
      sticky: false,
      /* Can the message be removed? otherwise it will not
        fade away nor be closable by button */
      removable: true,
      /* Time before a non-sticky message fades away, ms or 'auto'
         auto uses (fadesettings.wordtime * <wordcount>) + fadesettings.constanttime,
         anything over fadesettings.maxtime uses maxtime */
      fadetime: "auto",
      /* Animation time for jquery fade-out */
      animationtime: 400,
      /* An object of button-labels with callbacks as values */
      buttons: false,
      /* nag messages ignore many options, such as sticky. */
      nag: false
    },

    /**
     * Usage:
     *  Notify.clear() - Clear every notification
     *  Notify.clear('message') - Clear message notifications
     *  Notify.clear('nagbar') - Clear nagbar notifications
     *
     * @param  {string}  zone  The notification zone to clear
     */
    clear: function (zone) {

      var i = active.length;
      zone = (typeof(zone) === 'string') ? zone : 'all';

      for (i; i--; ) {
        if (active[i].zone === zone || zone === 'all') {
          active[i].remove();
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
      if(typeof message !== "string") {
        throw new Error("A Notify message must be a string, got "+(typeof message));
      }
      if(!message.length) {
        throw new Error("A Notify message cannot be empty");
      }

      if (zones === null) initialize();
      var notification = Notification(message, options);

      notification.element.html(notification.message);

      /**
       * If the nag option is true then we append the notification
       * to the nagbar instead and make it non-removable as well as
       * ignoring sicky and fadetime options.
       */
      if (notification.options.nag) {
        notification.zone = 'nagbar';
        notification.options.removable = false;
      } else {
        if (notification.options.sticky === false) {
          setTimeout(notification.remove, notification.fadetime());
        }
      }

      if (notification.options.removable) {
        notification.button('Close', function () {
          notification.remove();
        });
      }

      if (notification.options.buttons) {
        for (var key in notification.options.buttons) {
          notification.button(key, notification.options.buttons[key]);
        }
      }

      zones[notification.zone].append(notification.element);
      active.push(notification);

      return notification;

    }

  };

}());

$.notify = Notify.message;
