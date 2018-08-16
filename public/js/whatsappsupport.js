(function ($) {
  'use strict';

  $(function () {
    var delay_on_start = 3000;
    var $whatsappsupport = $('.whatsappsupport');
    var wame_settings = $whatsappsupport.data('settings');

    // In some strange cases data settings are empty
    if (typeof (wame_settings) == 'undefined') {
      try {
        wame_settings = JSON.parse($whatsappsupport.attr('data-settings'));
      } catch (error) {
        wame_settings = undefined;
      }
    }

    // only works if whatsappsupport is defined
    if ($whatsappsupport.length && !!wame_settings && !!wame_settings.telephone) {
      whatsappsupport_magic();
    }

    function whatsappsupport_magic() {
      var is_mobile = !!navigator.userAgent.match(/Android|iPhone|BlackBerry|IEMobile|Opera Mini/i);
      var timeoutID = null;

      // stored values
      var is_clicked = localStorage.whatsappsupport_click == 'yes';
      var views = wame_settings.message_text === '' ? 0 : parseInt(localStorage.whatsappsupport_views || 0) + 1;
      localStorage.whatsappsupport_views = views;

      // show button / dialog
      if (!wame_settings.mobile_only || is_mobile) {
        setTimeout(function () {
          $whatsappsupport.addClass('whatsappsupport--show');
        }, delay_on_start);
        if (views > 1 && !is_clicked) {
          setTimeout(function () {
            $whatsappsupport.addClass('whatsappsupport--dialog');
          }, delay_on_start + wame_settings.message_delay);
        }
      }

      if (!is_mobile && wame_settings.message_text !== '') {
        $('.whatsappsupport__button').mouseenter(function () {
          timeoutID = setTimeout(function () {
            $whatsappsupport.addClass('whatsappsupport--dialog');
          }, 1600);
        }).mouseleave(function () {
          clearTimeout(timeoutID);
        });
      }

      $('.whatsappsupport__button').click(function () {
        var link = whatsapp_link(wame_settings.telephone, wame_settings.message_send);

        $whatsappsupport.removeClass('whatsappsupport--dialog');
        localStorage.whatsappsupport_click = 'yes';

        if (typeof gtag == 'function') {
          // Send event (Global Site Tag - gtag.js)
          gtag('event', 'click', {
            'event_category': 'WhatsAppSupport',
            'event_label': link,
            'transport_type': 'beacon'
          });
        } else if (typeof ga == 'function') {
          // Send event (Universal Analtics - analytics.js)
          ga('send', 'event', {
            'eventCategory': 'WhatsAppSupport',
            'eventAction': 'click',
            'eventLabel': link,
            'transport': 'beacon'
          });
        }

        // Open WhatsApp link
        window.open(link, 'whatsappsupport');
      });

      $('.whatsappsupport__close').click(function () {
        $whatsappsupport.removeClass('whatsappsupport--dialog');
        localStorage.whatsappsupport_click = 'yes';
      });
    }

    // Return WhatsApp link with optional message
    function whatsapp_link(phone, message) {
      var link = 'https://web.whatsapp.com/send?phone=' + phone;
      if (typeof (message) == 'string' && message != '') {
        link += '&text=' + encodeURIComponent(message);
      }

      return link;
    }

  });

})(jQuery);
