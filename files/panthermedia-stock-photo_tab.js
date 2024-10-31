jQuery(document).ready(function($) {
   
   var PSP = PanthermediaStockPhoto;
   console.log(PSP);
   
   
   var MediaFrame;
   wp.media.controller.MPP = wp.media.controller.State.extend({
    initialize: function() {
      return this;
    },
    refresh: function() {
      return this.frame.toolbar.get().refresh();
    },
    _renderTitle: function(view) {
      return view.$el.html(this.get('headerTitle'));
    },
    _renderMenu: function(view) {
      var menuItem, priority, title;
      menuItem = view.get('menuItem');
      title = this.get('title');
      priority = this.get('priority');
      if (!menuItem) {
        menuItem = {
          html: title
        };
      }
      if (priority) {
        menuItem.priority = priority;
      }
      return view.set(this.id, menuItem);
    }
  });
  
  wp.media.view.MPP = wp.media.View.extend({
    template: wp.media.template('mpp-content'),
    initialize: function() {
      return this.module = this.options.module;
    },
    render: function() {
      return this;
    }
  });
  
  MediaFrame = wp.media.view.MediaFrame.Post;
  return wp.media.view.MediaFrame.Post = MediaFrame.extend({
    initialize: function() {
      var module, _i, _j, _len, _len1, _ref, _ref1, _results, _this = this;
      MediaFrame.prototype.initialize.apply(this, arguments);
      _ref1 = PanthermediaStockPhoto.modules;
      _results = [];
      for (_j = 0, _len1 = _ref1.length; _j < _len1; _j++) {
        module = _ref1[_j];
        this.states.remove(this.states.where({
          id: 'iframe:' + module.name
        }));
        this.states.add([
          new wp.media.controller.MPP({
            id: 'mpp-' + module.name,
            menu: 'default',
            content: 'mpp-' + module.name,
            toolbar: 'mpp-' + module.name,
            title: '<img src="' + PSP.icon + '" width="16" height="16" style="margin-right: 3px; position: relative; top: 2px;" /> ' + module.title,
            headerTitle: '<img src="' + PSP.icon + '" width="16" height="16" style="margin-right: 5px;" /> ' + module.title,
            priority: 200,
            type: 'link'
          })
        ]);
        this.on('content:render:mpp-' + module.name, _.bind(this.moduleContent, this, module));
        _results.push(this.on('toolbar:create:mpp-' + module.name, this.createToolbar, this));
      }
      return _results;
    },
    moduleContent: function(module) {
       var content, def_content, iframe, view;
      this.$el.addClass('hide-router');
      if (!this.$el.find('.mpp-frame-content-' + module.name).length) {
        iframe = '<iframe src="' + module.src + '" class="mpp-iframe-' + module.name + '" width="100%" height="100%"></iframe>';
        def_content = this.$el.find('.media-frame-content');
        content = $('<div class="mpp-frame-content mpp-frame-content-' + module.name + '">' + iframe + '</div>');
        content.css({
          'position': def_content.css('position'),
          'top': def_content.css('top'),
          'left': def_content.css('left'),
          'bottom': def_content.css('bottom'),
          'right': def_content.css('right'),
          'margin': def_content.css('margin')
        });
        this.$el.append(content);
      } else {
        this.$el.find('.mpp-frame-content-' + module.name).show();
      }
      view = new wp.media.view.MPP({
        controller: this,
        model: this.state().props,
        className: 'MPP media-' + module.name,
        module: module
      });
      return this.content.set(view);
    }
  });
   
   
   
   
   
   
   
   
   
   
   
   
   
   // div.media-menu div.separator
   
   //alert( wp.media.view.settings.tabs );
   /*
   wp.media.controller.MPP = wp.media.controller.State.extend({
    initialize: function() {
      return this;
    },
    refresh: function() {
      return this.frame.toolbar.get().refresh();
    },
    _renderTitle: function(view) {
      return view.$el.html(this.get('headerTitle'));
    },
    _renderMenu: function(view) {
      var menuItem, priority, title;
      menuItem = view.get('menuItem');
      title = this.get('title');
      priority = this.get('priority');
      if (!menuItem) {
        menuItem = {
          html: title
        };
      }
      if (priority) {
        menuItem.priority = priority;
      }
      return view.set(this.id, menuItem);
    }
  });
   
   //console.log(wp.media.view);
   console.log( wp.media.view.settings.tabs );
   var wpTabs = wp.media.view.settings.tabs;
   for( var tab in wpTabs ) {
      console.log( tab );
      
      if(tab === 'panthermedia-stock-photo_mymedia') {
         //wpTabs[tab] = '<img src="./pathermedia-favicon.png">'+wpTabs[tab];
         MediaFrameSelect = wp.media.view.MediaFrame.Select;
         wp.media.view.MediaFrame.Select = MediaFrameSelect.extend({
            initialize: function() {
               delete wp.media.view.settings.tabs[tab];
               this.states.remove(this.states.where({
                 id: 'iframe:' + tab
               }));
               this.states.add([
                  new wp.media.controller.MPP({
                     id: tab,
                     menu: 'default',
                     content: tab,
                     toolbar: tab,
                     title: '<img src="./panthermedia-favicon.png" width="16" height="16" style="margin-right: 3px; position: relative; top: 2px;" /> MyImages',
                     headerTitle: '<img src="./panthermedia-favicon.png" width="16" height="16" style="margin-right: 5px;" /> MyImages',
                     priority: 200,
                     type: 'link'
                   })
               ]);
               alert('Hm');
            },
            moduleContent: function() {}
         });
         
      }
   }/**/
   
   //wp.media.view.settings.tabs = [];
   
   
   
   //var MediaFrame = wp.media.view.MediaFrame.Post;
   
});
