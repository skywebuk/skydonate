;(function ($) {
  $(window).on('elementor/frontend/init', function () {
    $('.donation-form-wrapper').each(function () {
      var $scope = $(this);
      var $form = $scope.find('.donation-form');
      var productId = $form.data('product');
      $form.fadeIn(300);

      var $customInput = $form.find('.custom-amount-input');
      var $periodSelect = $form.find('.period-select-options');
      var $frequencySelect = $form.find('select.select-option');
      var $dateGroup = $form.find('.donation-daily-dates-group');
      var $typeSwitch = $form.find('.donation-type-switch button');
      var default_amount = 0;

      // ===== Default Amount =====
      var $activeAmountBtn = $form.find('.donation-amount-group.active .donation-btn.active');
      if ($activeAmountBtn.length) {
        default_amount = $activeAmountBtn.data('amount') || 0;
        $customInput.val(default_amount).trigger('change');
      }

      // ===== Plaque Visibility =====
      function updatePlaqueVisibility() {
        var inputValue = parseFloat($customInput.val()) || 0;
        $form.find('.name-on-plaque').each(function () {
          var $this = $(this);
          var visibleNumber = parseFloat($this.data('visible')) || 0;
          if (inputValue >= visibleNumber) {
            $this.show(200);
          } else {
            $this.hide(200).find('input[name="cart_custom_text"]').val('');
          }
        });
      }
      updatePlaqueVisibility();
      
    // ===== Custom Input Focus & Blur =====
    $customInput.on('focus', function () {
        $(this).data('old-value', $(this).val());
        $(this).val('').trigger('change');
        $(this).siblings('.custom-placeholder').hide();
    });

    $customInput.on('focusout', function () {
        var $input = $(this);
        var val = $.trim($input.val());
        if (val === '') {
            var oldVal = $input.data('old-value') || default_amount;
            $input.val(oldVal).trigger('change');
        }
        $(this).siblings('.custom-placeholder').show();
        updatePlaqueVisibility();
    });

    $customInput.on('input change', function () {
        updatePlaqueVisibility();
    });


      // ===== Donation Type Switch (Recurring / One-off) =====
      $typeSwitch.on('click', function () {
        var $btn = $(this);
        $typeSwitch.removeClass('active');
        $btn.addClass('active');

        if ($btn.hasClass('recurring')) {
          $periodSelect.fadeIn(200);
          var freq = $frequencySelect.val();
          switchAmountGroup(freq);
        } else {
          $periodSelect.fadeOut(200);
          switchAmountGroup('once');
        }

        updateDateGroup();
      });

      // ===== Recurring Frequency Select Change =====
      $frequencySelect.on('change', function () {
        var freq = $(this).val();
        switchAmountGroup(freq);
        updateDateGroup();
      });

      // ===== Show/Hide Relevant Amount Group =====
      function switchAmountGroup(group) {
        $form.find('.donation-amount-group').removeClass('active').hide();
        var $target = $form.find('.donation-amount-group[data-group="' + group + '"]');
        $target.addClass('active').fadeIn(200);

        var $active = $target.find('.donation-btn.active');
        default_amount = $active.data('amount') || 0;
        $customInput.val(default_amount).trigger('change');
        updatePlaqueVisibility();
      }

      // ===== Donation Button Click =====
      $form.on('click', '.donation-btn', function () {
        var $btn = $(this);
        var amount = $btn.data('amount') || 0;
        $btn.addClass('active').siblings().removeClass('active');
        $customInput.val(amount).trigger('change');
        default_amount = amount;
      });

      // ===== Show Daily Date Fields if Needed =====
      function updateDateGroup() {
        var isRecurring = $form.find('.donation-type-switch .recurring').hasClass('active');
        var freq = $frequencySelect.val();
        if (isRecurring && freq === 'daily') {
          $dateGroup.fadeIn(200);
        } else {
          $dateGroup.fadeOut(200);
        }
      }

      updateDateGroup(); // initial state

      // ===== Form Submit / AJAX =====
      $form.on('submit', function (e) {
        e.preventDefault();

        var $submitBtn = $form.find('button[type="submit"]');
        var inputVal = $customInput.val();
        var selectedAmount = inputVal ? $.trim(inputVal) : default_amount;
        var isRecurring = $form.find('.donation-type-switch .recurring').hasClass('active');
        var selectedFrequency = isRecurring ? $frequencySelect.val() : 'once';
        var name_on_plaque = $form.find('input[name="cart_custom_text"]').val() || '';

        var start_date = '';
        var end_date = '';

        if (selectedFrequency === 'daily') {
            start_date = $form.find('input[name="start_date"]').val() || '';
            end_date = $form.find('input[name="end_date"]').val() || '';

            start_date = convertDMYtoYMD(start_date);
            end_date = convertDMYtoYMD(end_date);

            if (end_date != '' && start_date != '' && start_date >= end_date) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Date Range',
                    text: 'End date must be greater than start date.',
                });
                $form.find('input[name="end_date"]').focus();
                return false;
            }
        }

        if (!productId || productId <= 0) {
          Swal.fire({
            icon: 'error',
            title: 'Invalid Fund',
            text: 'Please select a valid fund before proceeding.',
          });
          return false;
        }

        if (!selectedAmount || isNaN(selectedAmount) || selectedAmount <= 0) {
          Swal.fire({
            icon: 'warning',
            title: 'Invalid Amount',
            text: 'Please enter a valid donation amount.',
          });
          return false;
        }

        // Disable submit & show loading-running
        $submitBtn.addClass('loading-running').prop('disabled', true);

        var data = {
          action: 'add_extra_donation_to_cart',
          product_id: productId,
          amount: selectedAmount,
          donation_frequency: selectedFrequency,
          start_date: start_date,
          end_date: end_date,
          name_on_plaque: name_on_plaque,
          nonce: skyweb_extra_donation_ajax.nonce,
        };

        $.ajax({
          url: skyweb_extra_donation_ajax.ajax_url,
          type: 'POST',
          data: data,
          success: function (response) {
            if (response && response.error) {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: response.error,
              });
            } else {
              $(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $submitBtn]);
              $(document.body).trigger('wc_fragment_refresh');
              $('.cart-widget-side').addClass('wd-opened');
              $('.wd-close-side').addClass('wd-close-side-opened');
            }
          },
          error: function () {
            Swal.fire({
              icon: 'error',
              title: 'Request Failed',
              text: 'Error adding donation to cart. Please try again.',
            });
          },
          complete: function () {
            $submitBtn.removeClass('loading-running').prop('disabled', false);
          },
        });
        
      });
    });

    // Function to convert d-m-Y to Y-m-d
    function convertDMYtoYMD(dateStr) {
        if (!dateStr) return '';
        let parts = dateStr.split('-'); // split by '-'
        if (parts.length !== 3) return '';
        let [day, month, year] = parts;
        return `${year}-${month.padStart(2, '0')}-${day.padStart(2, '0')}`;
    }
  });
})(jQuery);
