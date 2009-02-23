$(function(){
	$("button, input[type=submit], input[type=button], input[type=reset]")
		.addClass("ui-widget")
		.addClass("ui-state-default")
		.addClass("ui-corner-all")
		.addClass("button");
	$("input.button[type=submit]")
		.addClass('ui-priority-primary');
	$('.ui-state-default').hover(
		function() { $(this).addClass('ui-state-hover'); },
		function() { $(this).removeClass('ui-state-hover'); }
	);
	$('.ui-state-default').click(
		function() { $(this).addClass('ui-state-active'); },
		function() { $(this).removeClass('ui-state-active'); }
	);
});