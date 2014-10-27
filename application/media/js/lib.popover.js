
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
    cache: true,
    position: 'bottom',
    root: $(document)

  };

  var tooltip = $('<div>').addClass('lib-popover-tip');
  var loading = '<span class="lib-popover-load"></span>';

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
        Popover.deactivate
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
              Popover.display(registry[data], target);
              return;
            }
            Popover.display(loading, target);
            handler(data, target);
          }
        });
      } else {
        Rules.push({rule: rule, handler: function(data, target){
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
        Popover.deactivate
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

      Popover.adjust(target, node);

      if(namespace){
        Popover.cache(namespace, node);
      }

      tooltip.empty();
      tooltip.append(node);

      target.after(tooltip);
      node = null;

    },

    /**
     * Adjusts the popover based on positioning
     * and the screen dimensions
     *
     * @todo   Current only supports bottom position sufficiently
     *
     * @param  jQuery   node  The targeted element
     * @param  mixed    data  What is being rendered into the popover
     */
    adjust: function(node, data){

      var offset = node.offset(),
          left = 0, top = 0,
          width = 272, p = settings.position,
          align = 'left';

      if(p === 'right'){
        left = offset.left + node.outerWidth();
        top = offset.top + (node.outerHeight() / 2);
      } else if(p === 'left'){
        left = offset.left - width;
        top = offset.top + (node.outerHeight() / 2);
        align = 'right';
      } else if(p === 'bottom'){
        left = offset.left;
        top = offset.top + node.outerHeight();
      }

      if(left > $(document).width() - width){
        left = offset.left - width;
        top = offset.top + (node.outerHeight() / 2);
        align = 'right';
      }

      tooltip.css({
        left: left + 'px',
        top: top + 'px',
        display: 'block',
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

    }

  };

  Popover.__init();
  $.popover = Popover;

  /**
   * Register common popover patterns
   */

  Popover.register(/^image\:/, function(data, target){

    $('<img>').attr(
      {src: data}
    ).on('load', function(e){
      Popover.display(e.target, target);
    })

  });

  Popover.register(/^help\:/, function(data, target){

    var ns = data.split('.');

    $.post('/ninja/index.php/ajax/get_translation', {
      controller: ns[0],
      key: ns[1]
    }, function(text){
      Popover.display(text, target, data);
    }).fail(function(){
      Popover.display('Failed.', target);
    });

  });

  Popover.register(/^post\:/, function(data, target){

    var post = target.data('popover-post');

    $.post(data, post, function(text){
      Popover.display(text, target, data);
    }).fail(function(){
      Popover.display('Failed.', target);
    });

  });

  Popover.register(/^get\:/, function(data, target){

    $.get(data, function(text){
      Popover.display(text, target, data);
    }).fail(function(){
      Popover.display('Failed.', target);
    });

  });

})();