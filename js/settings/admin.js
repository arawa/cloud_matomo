/* global OCP, OC */

$(function() {
   $('#matomoAdblockerWarning').hide();

   function showRequestResult(element, result) {
      if (element.attr('type') === 'checkbox') {
         element = $('label[for="' + element.attr('id') + '"]');
      }

      element.removeClass('matomo-success matomo-error');
      element.addClass('matomo-' + result);

      var timeout = element.data('timeout');

      if (timeout) {
         clearTimeout(timeout);
      }

      timeout = setTimeout(function() {
         element.removeClass('matomo-success matomo-error');
      }, 1000);

      element.data('timeout', timeout);
   }

   $('#matomoUrl').attr('placeholder', 'e.g. //' + window.location.host + '/matomo/');

   $('#matomoSettings input').change(function() {
      var element = $(this);
      var key = $(this).attr('name');
      var value = $(this).attr('type') === 'checkbox' ? $(this).prop('checked') : $(this).val();

      $.ajax({
         method: 'PUT',
         url: OC.generateUrl('apps/matomo/settings/' + key),
         data: {
            value: value
         },
         success: function(response) {
            showRequestResult(element, response.status)
         },
         error: function() {
            showRequestResult(element, 'error')
         }
      });
   });
});
