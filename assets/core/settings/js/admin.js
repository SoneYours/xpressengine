(function (root, factory) {
  if (typeof define === 'function' && define.amd) {
    // AMD
    define([
      'exports',
      "github:twbs/bootstrap-sass@3.3.6/assets/javascripts/bootstrap.js"
    ], function (exports, twbs) {
      factory(exports, twbs);
    });
  } else if (typeof exports === 'object' && typeof exports.nodeName !== 'string') {
    // CommonJS
    factory(exports, require("github:twbs/bootstrap-sass@3.3.6/assets/javascripts/bootstrap.js"));
  } else {
    // Browser globals
    factory({});
  }
}(this, function (exports, twbs) {
  $(document).ready(function () {
    var $sidebar = $('.settings-nav-sidebar');
    var $dim = $('.dim');

    /* 사이드바 */
    $sidebar.on('setting.sidebar.open', function() {
      $sidebar.addClass('open');
      $dim.show();
      $('body').css('overflow', 'hidden');
      $('html').css('position', 'fixed');
    }).on('setting.sidebar.close', function() {
      $sidebar.removeClass('open');
      $dim.hide();
      $('body').css('overflow', '');
      $('html').css('position', '');
    }).on('setting.sidebar.toggle', function() {
      if ($(window).innerWidth() < 1068) {
        $('body').removeClass("sidebar-collapse");
        if ($sidebar.hasClass("open")) {
          $sidebar.trigger('setting.sidebar.close');
        } else {
          $sidebar.trigger('setting.sidebar.open');
        }
      } else {
        $('body').toggleClass("sidebar-collapse");
      }
    });
    $dim.on('click', function() {
      $sidebar.trigger('setting.sidebar.close');
    });
    $(".btn-slide").on('click', function() {
      $sidebar.trigger('setting.sidebar.toggle');
    });

    /* 사이드바 메뉴 */
    $('.snb-list').on('click', '.__xe_collapseMenu', function (event) {
      var $target = $(this);
      var $subdepth = $target.parent('.sub-depth');
      var $ul = $target.siblings('.sub-depth-list');

      if ($ul.is(':visible')) {
        $ul.find('.sub-depth-list').slideUp('fast');
        $ul.find('.sub-depth').removeClass('open');

        $ul.slideUp('fast');
        $subdepth.removeClass('open');
      } else {
        var $parent = $subdepth.parent();
        $parent.find('.sub-depth.open>.sub-depth-list').slideUp('fast');
        $parent.find('.sub-depth.open').removeClass('open');

        $ul.slideDown('fast');
        $subdepth.addClass('open');
      }

      return false;
    });
  });
}));
