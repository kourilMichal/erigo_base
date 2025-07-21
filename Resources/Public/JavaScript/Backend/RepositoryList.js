define(['jquery', 'TYPO3/CMS/Backend/Enum/Severity', 'TYPO3/CMS/Backend/Modal'], function($, EnumSeverity, Modal) {
	
	$('.table-fit').on('click', '.t3js-record-delete', function(event) {
		event.preventDefault();
		
		var linkElem = $(this);
		
		Modal.confirm(
				linkElem.data('title'),
				linkElem.data('message'),
				EnumSeverity.SeverityEnum.warning,
				[
				 	{ text: linkElem.data('button-close-text') || TYPO3.lang['button.cancel'] || 'Cancel', active: true, btnClass: 'btn-default', name: 'cancel' },
				 	{ text: linkElem.data('button-ok-text') || TYPO3.lang['button.delete'] || 'Delete', btnClass: 'btn-warning', name: 'delete' },
				 ]
				
			).on('button.clicked', function(buttonEvent) {
				Modal.dismiss();
				
				if ($(buttonEvent.target).attr('name') == 'delete') {
					window.location.href = linkElem.attr('href');
				}
			});
	});
	
});