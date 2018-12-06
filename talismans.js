(function($) {
	jQuery('<div />').appendTo('body')
		.attr('id','alertBanner')
		.css('display','none');

	//Find talisman on page, use title to send to PHP to get data
	var talisman = $(document).find('.talisman').attr('id');
	if (talisman) {
		var data = {
	   _ajax_nonce: my_ajax_obj.nonce,
	    action: 'is_user_logged_in',
	    talisman: talisman
		};
		
		//add specific talisman data to response
		$.post(my_ajax_obj.ajax_url, data, function(response) {
	    response = JSON.parse(response);
	    if(response.success) {
	    	var talismanData = response.talisman;
	    	if (talismanData) {
	    		talismanData = talismanData[0];
		    	var collected = response.collected;
		    	//If talisman is already collected remove it from DOM
		    	if (collected) {
		    		$("#"+talismanData.id).remove();
		    	}
			    //If not, show it (talismans are hidden by default)
		    	else {
		    		//Setup talisman on page
		    		//console.log("#"+talismanData.id);
		    		var temp = $("#"+talismanData.id);
		    		temp.html('<img src="https://modernmasters.org/wp-content/uploads/2016/10/'+talismanData.filename+'" />');
						temp.css('width','33px');
						if (talismanData.difficulty === 'easy') temp.css('cursor','pointer');
						else if (talismanData.difficulty === 'hard') {
							temp.css('width','11px');
						}
						temp.css('display','block');
						temp.css('position','relative');
						temp.removeClass('talisman');

			      temp.on('click', function() {
						  var this2 = this;
						  $.ajax(my_ajax_obj.ajax_url, {
						    method: "POST",
						    data: { _ajax_nonce: my_ajax_obj.nonce, action: "collect_talisman", talisman: this.id },
						  	success: function(data) {
						      $(this2).remove();
						      var tmessage = encodeURI("I just found the "+data+" Talisman at Modern Masters!");
						      alertBanner("You found the <strong>"+data+"</strong> Talisman! You've added it to your <a href='https://modernmasters.org/talismans/'>collection</a>. <a target='_blank' href='https://twitter.com/share?url=https%3A%2F%2Fmodernmasters.org%2F&via=modernmasters22&hashtags=talisman%2CMM22&text="+tmessage+"'>Tweet!</a>");
						    },
						    error: function(data) {
						    	$(this2).remove();
						      alertBanner("Something went wrong. Please use the <a href='https://modernmasters.org/chat/'>chat</a> feature or <a href='https://modernmasters.org/about-modern-masters/contact-us/'>Contact Us</a>.");
						    },
						    beforeSend: function() {
						    	$(this2).off('click');
						    	$(this2).html('<div class="spinner"><div class="bounce1"></div></div>');
						    }
						  });
						});
			    }
			  }
	    }
	    else {
	    	var talismanData = response.talisman;
	    	if (talismanData) {
	    		talismanData = talismanData[0];
	    		var temp = $("#"+talismanData.id);
	    		temp.html('<img src="https://modernmasters.org/wp-content/uploads/2016/10/'+talismanData.filename+'" />');
					temp.css('width','33px');
					if (talismanData.difficulty === 'easy') temp.css('cursor','pointer');
					else if (talismanData.difficulty === 'hard') {
						temp.css('width','11px');
					}
					temp.css('display','block');
					temp.css('position','relative');
					temp.removeClass('talisman');

		    	// localstorage?
		    	temp.on('click', function() {
		    		var tmessage = encodeURI("I just found the "+talismanData.title+" Talisman at Modern Masters!");
		    		alertBanner("You found the <strong>"+talismanData.title+"</strong> Talisman! <a href='https://modernmasters.org/login/'>Log In</a> or <a href='https://modernmasters.org/join-us/membership-levels/'>Join Us</a> for free to gather it. <a target='_blank' href='https://twitter.com/share?url=https%3A%2F%2Fmodernmasters.org%2F&via=modernmasters22&hashtags=talisman%2CMM22&text="+tmessage+"'>Tweet!</a>");
				  });
		    }
	    }
		});
	}
})( jQuery );

//If talisman is already collected remove it from DOM
// Returns a random integer between min (included) and max (excluded)
// Using Math.round() will give you a non-uniform distribution!
function getRandomInt(min, max) {
  min = Math.ceil(min);
  max = Math.floor(max);
  return Math.floor(Math.random() * (max - min)) + min;
}

function toTitleCase(str)
{
	return str.replace(/\w\S*/g, function(txt){return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();});
}

function alertBanner(msg)
{
	jQuery('#alertBanner').html(msg);
	jQuery('<div />').appendTo('#alertBanner')
		.attr('id','closeButton')
		.html('&times;')
		.click(function() {
			jQuery('#alertBanner').slideUp();
		});
	jQuery('#alertBanner').slideDown();
}