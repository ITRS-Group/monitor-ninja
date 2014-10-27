
(function(){

  var registry = {};
  var settings = {

    toggle: 'mouseenter focus',
    untoggle: 'mouseleave blur',
    selector: '*[data-popover]',
    memoize: true,
    position: 'bottom',
    root: $(document),

    rules: {

      image: {
        rule: /^image\:/,
        handler: function(data, target){

          var node = $('<img>').attr({
            src: data.replace(settings.rules.image.rule, '')
          });

          node.on('load', function(){
            Popover.display(node, target);
          })

          Popover.display('<span class="lib-popover-load"></span>', target);

        }
      },

      get: {
        rule: /^get\:/,
        handler: function(data, target){

          if(settings.memoize && registry[data]){
            Popover.display(registry[data], target);
            return;
          }

          $.get(data.replace(settings.rules.get.rule, ''), function(text){
            if(settings.memoize){registry[data] = text;}
            Popover.display(text, target);
          }).fail(function(){
            Popover.display('Failed.', target);
          });

          Popover.display(loading, target);

        }
      },

      post: {
        rule: /^post\:/,
        handler: function(data, target){

          if(settings.memoize && registry[data]){
            Popover.display(registry[data], target);
            return;
          }

          var post = target.data('popover-post');

          $.post(data.replace(settings.rules.post.rule, ''), post, function(text){
            if(settings.memoize){registry[data] = text;}
            Popover.display(text, target);
          }).fail(function(){
            Popover.display('Failed.', target);
          });

          Popover.display(loading, target);

        }
      },

      help: {
        rule: /^help\:/,
        handler: function(data, target){

          if(settings.memoize && registry[data]){
            Popover.display(registry[data], target);
            return;
          }

          /* Recieves a <controller>.<key> namespace */

          var ns = data.replace(settings.rules.help.rule, '').split('.'),
              controller = ns[0],
              key = ns[1];

          $.post('/ninja/index.php/ajax/get_translation', {
            controller: controller,
            key: key
          }, function(text){
            if(settings.memoize){registry[data] = text;}
            Popover.display(text, target);
          }).fail(function(){
            Popover.display('Failed.', target);
          });

          Popover.display(loading, target);

        }
      }

    }
  };

  var tooltip = $('<div>')
      .addClass('lib-popover-tip');

  var loading = '<span class="lib-popover-load"></span>';

  var Popover = {

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

    },

    display: function(node, target){

      Popover.adjust(target, node);

      tooltip.empty();
      tooltip.append(node);

      target.after(tooltip);
      node = null;

    },

    adjust: function(node, data){

      var offset = node.offset(),
          left = 0, top = 0,
          width = 216, p = settings.position,
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

    deactivate: function(){

      tooltip.empty();
      tooltip.css('display', 'none');

    },

    activate: function(e){

      var target = $(e.currentTarget),
          data = target.data('popover'),
          rule = null;

      for(rule in settings.rules){
        rule = settings.rules[rule];
        if(data.match(rule.rule)){
          rule.handler(data, target);
          return;
        }
      }

      Popover.display(data, target);

    }

  };

  Popover.__init();
  $.popover = Popover;

})();