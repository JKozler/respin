/** Inspired by https://github.com/contributte/reCAPTCHA/blob/master/assets/invisibleRecaptcha.js */
jQuery(document).on('ready', function () {
(function (document, grecaptcha) {
	var init = false;

	const MWRecaptcha = function (grecaptcha) {
		const forms = document.getElementsByClassName('g-recaptcha-form');

		if (forms.length === 0) {
			return;
		}

		grecaptcha.ready(function () {
			let resolved = false;

			let form;
			for (let i = 0; i < forms.length; i++) {
				form = forms[i];
				let $form = jQuery(form);

				// Add hidden input for reCAPTCHA response
				if ($form.find('g-recaptcha-response').length === 0) {
					const input = document.createElement('input');
					input.type = 'hidden';
					input.name = 'g-recaptcha-response';
					input.className = 'g-recaptcha-response';
					form.appendChild(input);

				}

				$form.find('[type="submit"]').on('click', function(e) {
					// we already have reCaptcha response, or the form is invalid - or submission is prevented for some other, unknown reason
					if (resolved/* || e.defaultPrevented*/) {
						return;
					}

					e.preventDefault();
					e.stopImmediatePropagation();

					grecaptcha.execute(MWReCaptchaSiteKey).then(function (token) {
						resolved = true;
						// reCaptcha token expires after 2 minutes; make it 5 seconds earlier just in case network is slow
						setTimeout(function () {
							resolved = false;
						}, (2 * 60 - 5) * 1000);

						let inputs = $form.find('.g-recaptcha-response');
						for (let i = 0; i < inputs.length; i++) {
							inputs[i].value = token;
						}

						$form.submit();

						// Clear response
						for (let i = 0; i < inputs.length; i++) {
							inputs[i].value = '';
						}

						resolved = false;
					});

				});
			}
		});

		init = true;
	};

	if (!init) {
		MWRecaptcha(grecaptcha);
	}

})(document, grecaptcha);

})
