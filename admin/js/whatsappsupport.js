(function ($) {
  'use strict';

  $(function () {
    var country_request = JSON.parse(localStorage.whatsappsupport_country_code || '{}');
    var country_code = (country_request.code && country_request.date == new Date().toDateString()) ? country_request.code : false;
    var $phone = $("#whatsappsupport_phone");

    $phone.intlTelInput({
      hiddenInput: "whatsappsupport[telephone]",
      initialCountry: "auto",
      preferredCountries: [country_code || ''],
      geoIpLookup: function (callback) {
        if (country_code) {
          callback(country_code);
        } else {
          $.getJSON('https://ipinfo.io').always(function (resp) {
            var countryCode = (resp && resp.country) ? resp.country : "";
            localStorage.whatsappsupport_country_code = JSON.stringify({ code: countryCode, date: new Date().toDateString() });
            callback(countryCode);
          });
        }
      },
      utilsScript: "https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/12.1.9/js/utils.js"
    });

    $phone.on("keyup change", function () {
      $phone.css('border-color', '');
    });
    $phone.on("blur", function () {
      $phone.css('border-color', $.trim($phone.val()) && !$phone.intlTelInput("isValidNumber") ? '#ff0000' : '');
    });

  });
})(jQuery);
