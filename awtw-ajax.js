jQuery(document).ready(function($) {
	$("#awtw-feedback-form").submit(function(e) {
		e.preventDefault();
		var data = $(this).serialize()+'&action=awtw_ajax_front';

		var feedback = jQuery("#awtw-feedback-msg").val()
		// console.log (feedback.length);
		if(feedback.length > awtw_params.minLenght) {
			jQuery.ajax({
			type: "POST",
			beforeSend: function() {
				jQuery("#awtw-feedback-send-msg-btn").val('Sending...');
			},
			complete: function() {
				jQuery("#awtw-feedback-send-msg-btn").val('Send');
			},
			url: awtw_params.url,
			data: data
		}).done(function(message,response) {
			if (message === 'spam') {
				var text = "Bummer ! your FeedBack was marked as Spam."
				jQuery("#awtw-feedback-result").addClass("error").css('display','block').html(text);
			} else {
				var text = "Thank you ! Feedback successfully received.";
				jQuery("#awtw-feedback-result").removeClass("error").css('display','block').html(text);
			}
		});

		} else {
			var text = "Feedback is too short !";
			jQuery("#awtw-feedback-result").addClass("error").css('display','block').html(text);
		} // End if
	}); // End onSubmit jQuery
}); // End jQuery