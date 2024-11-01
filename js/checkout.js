jQuery(document).ready(function( $ )
{
  if ('undefined' === typeof(rubricParams)) {
    return;
  }
  // enhanced toc
  // console.log(rubricParams, 1 == rubricParams.enhanced_tc);
  var applyTermsLayover = function() {
    $('.rubric-terms-and-conditions-link').on('click', function(e){
        e.preventDefault();
        $.magnificPopup.open({
            items: {
            src: rubricParams.tc_url,
            type: 'iframe'
          },
          mainClass: 'rubric_terms_content'
        });
        return false;
    });
  };
  // enhanced fields
  var applyInputMasks = function()
  {
    new Cleave('#rubric_card_number', {
      creditCard: true,
      onCreditCardTypeChanged: function (type) {
        var logo = $('.card-type-logo').find('.fa');
        if ('visa' === type || 'mastercard' === type || 'discover' === type || 'amex' === type) {
            var style = 'fa-cc-' + type;
            if (!logo.hasClass(style)) {
                logo.removeClass().addClass('fa fa-lg ' + style);
            }
            return;
        }
        // no recognized card
        if (!logo.hasClass('fa-credit-card')) {
            logo.removeClass().addClass('fa fa-credit-card fa-lg');
        }
        // console.log(type);
      }
    });
    new Cleave('#rubric_card_expiration', {
      numericOnly: true,
      delimiter: '/',
      /*prefix: 'BE',*/
      blocks: [2, 4]
    });
    new Cleave('#rubric_card_cvv', {
      numericOnly: true,
      blocks: [4]
    });
  };
  if (1 != rubricParams.enhanced && 1 != rubricParams.enhanced_tc) {
    return;
  }
  // bind to when xhr checkout review is updated
  $( document.body ).on( 'updated_checkout', function(e, data) {
    // console.log(data);
    if (1 == rubricParams.enhanced) {
      applyInputMasks();
    }
    if (1 == rubricParams.enhanced_tc) {
      applyTermsLayover();
    }
  });
});
