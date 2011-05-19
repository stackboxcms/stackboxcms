$(function(){

	$("select, input:checkbox, input:radio, input:file").uniform();

	if (".welcome-message h2 span") {
		swapElement = $(".welcome-message h2 span");
		shouldBeFree(swapElement);
	}

});


// Welcome page text cycle
function shouldBeFree(swapElement) {	
	$(swapElement).delay(5000).fadeOut(200, function() {
		$(this).text("free").fadeIn(200,
			function() {
				shouldBePowerful(swapElement);
			}
		);
	});
}
function shouldBePowerful(swapElement) {	
	$(swapElement).delay(5000).fadeOut(200, function() {
		$(this).text("powerful").fadeIn(200,
			function() {
				shouldBeEasy(swapElement);
			}
		);
	});
}
function shouldBeEasy(swapElement) {	
	$(swapElement).delay(5000).fadeOut(200, function() {
		$(this).text("easy").fadeIn(200,
			function() {
				shouldBeFree(swapElement);
			}
		);
	});
}