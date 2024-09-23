jQuery(document).ready(function ($) {
	// Target the invoice and packaging slip action buttons and force them to open in a new tab
	$('a.invoice, a.packaging-slip').attr('target', '_blank');
});