
/**
 * Lib Popover
 *
 * Usage:
 *
 *  <input data-popover="<popover-describer>" />
 *  There, now it has a tooltip!
 *
 * ALl events are delegated, no DOM nodes are left floating
 * in the wild .
 *
 * Possible describers:
 *   "Text" - A free text popover, simply displays the text
 *
 *   "image:/a/image/url" - The popover will contain the image referenced
 *
 *   "get:/a/get/url" - A GET will be sent to the url, the return is displayed
 *       as text.
 *
 *   "post:/a/post/url" - A POST will be sent to the url, assign paramters
 *       with data-popover-post attribute, where the value is a query-string:
 *       data-popover-post="name=something&key=else". The return is displayed
 *       as text
 *
 *   "help:controller.key" - The popover will fetch the help text from
 *       get_translation and display it as raw text
 *
 *  Describers can be added, by appending rules:
 *
 *    $.popover.register(RegExp rule, function handler)
 *      where handler recieves:
 *        data - The attribute value
 *        target - The jQuery wrapped node that is being used
 *
 *  e.g $.popover.register(/^user\:/, function(data, target){
 *      var name = data.split(':')[1];
 *      var user = .. fetch some user data ..
 *      Popover.display(user);
 *    });
 */
(function(){

  var Rules = [];

  var registry = {};
  var settings = {

    toggle: 'mouseenter focus',
    untoggle: 'mouseleave blur',
    selector: '*[data-popover]',
    delay: 400,
    cache: true,
    position: 'bottom',
    root: $(document)

  };

  var tooltip = $('<div>').addClass('lib-popover-tip');
  var loading = '<span class="lib-popover-load"></span>';
  var request = null;
  var timer = null;

  var abort = false;
  var Popover = {

    /**
     * Initializes popover by binding the document-wide
     * event-listeners
     */
    __init: function(){

      settings.root.on(
        settings.toggle,
        settings.selector,
        Popover.activate
      );

      settings.root.on(
        settings.untoggle,
        settings.selector,
        Popover.abort
      );

    },

    /**
     * Registers a new popover string-pattern to
     * display something in a special manner.
     *
     * @param  regexp    rule     The matching pattern is removed from the data
     * @param  function  handler  Handler recieves data and target
     */
    register: function(rule, handler){

      if(settings.cache){

        Rules.push({
          rule: rule,
          handler: function(data, target){
            if(registry[data]) {
              abort = false;
              Popover.display(registry[data], target);
              return;
            }
            Popover.abort();
            Popover.display(loading, target);
            handler(data, target);
          }
        });
      } else {
        Rules.push({rule: rule, handler: function(data, target){
          Popover.abort();
          Popover.display(loading, target);
          handler();
        }});
      }

    },

    /**
     * Adds the item into the cache if caching is enabled
     */
    cache: function(namespace, data){
      if(settings.cache){
        registry[namespace] = data;
      }
    },

    /**
     * Reconfigure lib.popover with new options,
     * it rebinds that document-wide events.
     *
     * @param  object  options  An object of option properties
     */
    config: function(options){

      settings.root.off(
        settings.toggle,
        settings.selector,
        Popover.activate
      );

      settings.root.off(
        settings.untoggle,
        settings.selector,
        Popover.abort
      );

      var o = null;
      for(o in options){
        if(settings[o] != void(0)){
          settings[o] = options[o];
        }
      }

      Popover.__init();

    },

    /**
     * Displays a popover at the <target> node
     * with the renderable <node> which can be a
     * jquery node, DOMNode or string.
     *
     * @param  mixed  node          The rendereable to popover
     * @param  jquery node  target  The target of the popover
     */
    display: function(node, target, namespace){

      if (typeof(node) == 'string') {
        var tmp = document.createElement("p");
        tmp.innerHTML = node;
        tmp.style.width = '256px';
        node = tmp;
      }

      node = $(node);

      if (abort) {
        abort = false;
        return;
      }

      abort = false;
      clearTimeout(timer);

      timer = setTimeout(function () {

        if(namespace){
          Popover.cache(namespace, node);
        }

        tooltip.empty();
        tooltip.append(node);
        tooltip.css('display', 'block');
        target.after(tooltip);
        Popover.adjust(target, node);

      }, settings.delay);

    },

    /**
     * Adjusts the popover based on positioning
     * and the screen dimensions
     *
     * @todo   Current only supports bottom position sufficiently
     *
     * @param  jQuery   target  The targeted element
     * @param  jQuery   node  What is being rendered into the popover
     */
    adjust: function(target, node){

      var offset = target.offset(),
          left = 0, top = 0,
          width = node.width(),
          height = node.height(),
          screen_w = $(document).width() / 2,
          screen_h = $(document).height() / 2;

      top = offset.top;

      if(offset.left > screen_w){
        left = (offset.left - width) - 8;
        align = "right";
      } else{
        left = (offset.left + target.outerWidth()) + 8;
        align = "left";
      }

      if(offset.top > screen_h){
        top = (offset.top - height) - 8;
      }

      tooltip.css({
        left: left + 'px',
        top: top + 'px',
        'text-align': align
      });

    },

    /**
     * Only used on event based popovers
     * to activate based on HTML.
     *
     * @param  event  e [description]
     */
    activate: function(e){

      var target = $(e.currentTarget),
          data = target.data('popover'),
          rule = null;

      for(rule in Rules){
        rule = Rules[rule];
        if(data.match(rule.rule)){
          rule.handler(data.replace(rule.rule, ''), target);
          return;
        }
      }

      Popover.display(data, target);

    },

    /**
     * Only used on event based popovers
     * to deactivate based on Event.
     */
    deactivate: function(){

      tooltip.empty();
      tooltip.css('display', 'none');

    },

    /**
     * Aborts the current request
     */
    abort: function(){

      clearTimeout(timer);
      if(request && request.abort)
        request.abort();

      abort = true;
      request = null;
      Popover.deactivate();

    }

  };

  Popover.__init();
  $.popover = Popover;

  /**
   * Register common popover patterns
   */

  Popover.register(/^image\:/, function(data, target){

    var img = $('<img>').one('load', function(e){
      Popover.display(img.get(0), target);
      tooltip.css('width', 'auto');
    }).one('error', function(e){
      Popover.display("Could not fetch image", target);
    }).attr(
      {src: data}
    );

  });

  Popover.register(/^help\:/, function(data, target){

    var ns = data.split('.'),
      controller = ns.shift(),
      key = ns.join('.');

    request = $.post('/ninja/index.php/ajax/get_translation', {
      controller: controller,
      key: key,
      csrf_token: _csrf_token
    }, function(text){
      Popover.display(text, target, data);
    }).fail(function(xhr, status){
      if (status !== 'abort') {
        Popover.display('Could not fetch helptext', target);
      }
    });

  });

  Popover.register(/^pnp\:/, function(data, target){

    var ns = data.split(';'),
      host = encodeURIComponent(ns[0]),
      service = "_HOST_";

    if(ns[1]) service = encodeURIComponent(ns[1]);

    var img = $('<img>').one('load', function(e){
      Popover.display(img.get(0), target);
      tooltip.css('width', 'auto');
    }).one('error', function(e){
      Popover.display("Could not fetch graph", target);
    }).attr({
      src: _pnp_web_path + 'image?host=' + host + '&srv=' + service + '&source=0&view=0'
    });

  });

  Popover.register(/^post\:/, function(data, target){

    var post = target.data('popover-post');
    data.csrf_token = _csrf_token;

    $.post(data, post, function(text){
      Popover.display(text, target, data);
    }).fail(function(xhr, status){
      if (status !== 'abort') {
        Popover.display('Failed to retrieve popover information', target);
      }
    });

  });

  Popover.register(/^get\:/, function(data, target){

    $.get(data, function(text){
      Popover.display(text, target, data);
    }).fail(function(xhr, status){
      if (status !== 'abort') {
        Popover.display('Failed to retrieve popover information', target);
      }
    });

  });

})();
