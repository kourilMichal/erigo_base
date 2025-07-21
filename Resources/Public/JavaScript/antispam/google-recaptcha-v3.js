ebFormCallbacks.antispam = function(form, hasAjaxSubmit) {
	var protectionField = null;
	var siteKey = null;
	var grecaptchaAction = 'form_submit';
	
	if (form.find('.form-antispam-protection').length > 0) {
		protectionField = form.find('.form-antispam-protection').first();
		siteKey = protectionField.attr('data-site-key');
	}
	
	if (form.attr('id') != '') {
		grecaptchaAction = form.attr('id');
		grecaptchaAction = grecaptchaAction.replace(/-/, '_');
		grecaptchaAction = grecaptchaAction.replace(/[^a-zA-Z_]/g, '');
		grecaptchaAction = grecaptchaAction.replace(/_$/, '');
		grecaptchaAction = grecaptchaAction.replace(/^_/, '');
	}
	
	grecaptcha.ready(function() {
		grecaptcha.execute(siteKey, {action: grecaptchaAction}).then(function(token) {
			if (protectionField != null) {
				protectionField.val(token);
			}
			
			if (hasAjaxSubmit) {
				eb_submitFormWithAjax(form);
				
			} else {
				if (protectionField != null) {
					protectionField.removeClass('form-antispam-protection');
				}
				
				form.submit();
			}
		});
	});
};