function eb_submitFormWithAjax(form) {
	var formData = new FormData(form[0]);
	
	for (var callbackKey in ebFormCallbacks.beforeAjaxRequest) {
		ebFormCallbacks.beforeAjaxRequest[callbackKey](form, formData);
	}
	
	$.ajax({
		url: form.attr('action'),
		type: 'POST',
		data: formData,
		contentType: false,
		processData: false,
		success: function (data) {
			for (var callbackKey in ebFormCallbacks.onSuccess) {
				ebFormCallbacks.onSuccess[callbackKey](form, data);
			}
		},
		error: function (jqXHR, textStatus, errorThrown) {
			for (var callbackKey in ebFormCallbacks.onError) {
				ebFormCallbacks.onError[callbackKey](form, jqXHR);
			}
		}
	});
}

$(document).on('submit', 'form', function(event) {
	var hasAntispamProtection = false;
	var hasAjaxSubmit = false;
	
	if ($(this).find('.form-antispam-protection').length > 0 && ebFormCallbacks.antispam != null) {
		hasAntispamProtection = true;
	}
	
	if ($(this).attr('data-ajax-submit') == 'true') {
		hasAjaxSubmit = true;
	}
	
	if (hasAntispamProtection || hasAjaxSubmit) {
		event.preventDefault();
		
		for (var callbackKey in ebFormCallbacks.afterSubmit) {
			ebFormCallbacks.afterSubmit[callbackKey]($(this));
		}
		
		if (hasAntispamProtection) {
			ebFormCallbacks.antispam($(this), hasAjaxSubmit);
			
		} else {
			eb_submitFormWithAjax($(this));
		}
	}
});