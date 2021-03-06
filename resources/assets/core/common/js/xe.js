(function (root, factory) {
  if (typeof define === 'function' && define.amd) {
    define([
      'exports',
      'vendor:/lodash',
      'xecore:/common/js/xe.lang',
      'xecore:/common/js/xe.progress',
      'xecore:/common/js/xe.request',
      'xecore:/common/js/xe.component',
      ], function (exports, $, XeLang, XeProgress, XeRequest) {
        if(typeof root.XE === "undefined") {
          factory((root.XE = exports), XeLang, XeProgress, XeRequest);
        }
      });
  } else if (typeof exports === 'object' && typeof exports.nodeName !== 'string') {
    if(typeof root.XE === "undefined") {
      factory((root.XE = exports), require('vendor:/lodash'), require('xecore:/common/js/xe.lang'), require('xecore:/common/js/xe.progress'), require('xecore:/common/js/xe.request'), require('xecore:/common/js/xe.component'));
    }
  } else {
    if(typeof root.XE === "undefined") {
      factory((root.XE = {}));
    }
  }
}(this, function (exports, _, XeLang, XeProgress, XeRequest, XeComponent) {
  'use strict';

  var INSTANCE = null;
  var $ = window.jQuery;

  var XE = function () {
    var self = this;
    this.Lang = XeLang;
    this.Progress = XeProgress;
    this.Request = XeRequest;
    this.Component = XeComponent;

    this.options = {};

    this.setup = function (options) {
      self.options.loginUserId = options.loginUserId;

      self.Request.setup({
        headers: {
          'X-CSRF-TOKEN': options['X-CSRF-TOKEN']
        }
      });
    };

    this.configure = function (options) {
      $.extend(self.options, options);
    };

    // @DEPRECATED
    this.cssLoad = function(url) {
      $('head').append($('<link>').attr('rel', 'stylesheet').attr('href', url));
    };

    this.toast = function (type, message) {
      if (type == '') {
        type = 'danger';
      }
      System.import('xecore:/common/js/modules/griper/griper').then(function (griper) {
        return griper.toast(type, message);
      });
    };

    this.toastByStatus = function (status, message) {
      System.import('xecore:/common/js/modules/griper/griper').then(function (griper) {
        return griper.toast(griper.toast.fn.statusToType(status), message);
      });
    };

    this.formError = function ($element, message) {
      System.import('xecore:/common/js/modules/griper/griper').then(function (griper) {
        return griper.form($element, message);
      });
    };

    this.formError.clear = function ($form) {
      System.import('xecore:/common/js/modules/griper/griper').then(function (griper) {
        return griper.form.fn.clear($form);
      });
    };

    this.validate = function ($form) {
      System.import('xecore:/common/js/modules/validator').then(function (validator) {
        validator.validate($form);
      });
    };

    this.import = function(name, parentName, parentAddress) {
      if(_.isArray(name)) {
        var modules = _.map(name, function(module){
          return System.import(module);
        });
        return Promise.all(modules);
      } else {
          return System.import(name);
      }
    };

    this.getLocale = function() {
      return self.options.locale;
    }

    this.getDefaultLocale = function() {
      return self.options.defaultLocale;
    }

     if(this.Request) {
      self.ajax = self.Request.ajax = function(url, options) {
        if ( typeof url === "object" ) {
          options = $.extend({}, self.Request.options, url);
          url = undefined;
        } else {
          options = $.extend({}, options, self.Request.options, {url: url});
          url = undefined;
        }

        $.ajaxSetup(options);
        var jqXHR = $.ajax(url, options);
        return jqXHR;
      };

      // $.ajaxPrefilter(function(options, originalOptions, jqXHR ) {
      //   $.extend(options, self.Request.options);
      // });
    }
  };

  var getInstance = function (){
    if (INSTANCE === null) {
      INSTANCE = new XE();
    }

    return INSTANCE;
  };

  $.extend(exports, getInstance());
}));
