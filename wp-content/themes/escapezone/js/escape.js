(function () {
  // FIX ON SCROLL
  jQuery(document).scroll(function () {
    if (jQuery(window).scrollTop() > 116) {
      jQuery('#header').addClass('fix');
    } else {
      jQuery('#header').removeClass('fix');
    }
  });

  jQuery(document).ready(function () {
    jQuery('#menu').click(function ($) {
      jQuery('#nav').toggleClass('mobile-hidden');
    });

    try {
      jQuery('#imprimir').click(function () {
        window.print();
        return false;
      });
    } catch (error) {

    }
  });

})();
