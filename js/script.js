jQuery(document).ready(function(){
	
	// Run the init method on document ready:
	chat.init();
	
});

var chat = {
	
	// data holds variables for use in the class:
	
	data : {
		lastID 		: 0,
		noActivity	: 0
	},
	
	// Init binds event listeners and sets up timers:
	
	init : function(){
		
		// Checking whether the user is already logged (browser refresh)
		
		jQuery.tzGET('checkLogged',function(r){
			if(r.logged){
				jQuery('#custom_login_form').hide(function(){
					jQuery('#chat-box').show();
				});
				chat.login(r.loggedAs.name,r.loggedAs.gravatar);
			} else {
				jQuery('#chat-box').hide(function(){
					jQuery('#custom_login_form').show();
				});
			
			}
		});

		// Using the defaultText jQuery plugin, included at the bottom:
		jQuery('#email').defaultText('Email (Gravatars are Enabled)');
		jQuery('#username').defaultText('Username');
		jQuery('#password').defaultText('Password');
		jQuery('#forgotten-email').defaultText('Email');

		// Converting the #chatLineHolder div into a jScrollPane,
		// and saving the plugin's API in chat.data:
		chat.data.jspAPI = jQuery('#chatLineHolder').jScrollPane({
			verticalDragMinHeight: 12,
			verticalDragMaxHeight: 12
		}).data('jsp');
		
		// We use the working variable to prevent
		// multiple form submissions:
		
		var working = false;
		guest = false;
		
		// Registering a new user
		jQuery('#btn-register').click(function(){
			jQuery('#register-form').submit();
		});

		jQuery('#guest-btn-login').click(function(){
			guest = true;
			jQuery('#loginForm').submit();
		});


		jQuery('#register-form').submit(function(){
			if(working) return false;
			working = true;
			
			// Using our tzPOST wrapper function
			// (defined in the bottom):

			jQuery.tzPOST('register',jQuery(this).serialize(),function(r){
					working = false;
					
					if(r.error){
						chat.displayError(r.error);
					}
					else chat.register(jQuery('#email').val(), jQuery('#username').val(), jQuery('#password').val());
				});
			
			return false;
		});


		// Logging a person in the chat:
		
		jQuery('#loginForm').submit(function(){
			
			if(working) return false;
			working = true;
			
			// Using our tzPOST wrapper function
			// (defined in the bottom):
			if (!guest)
			{
				jQuery.tzPOST('login',jQuery(this).serialize(),function(r){
					working = false;
					
					if(r.error){
						chat.displayError(r.error);
					}
					else chat.login(jQuery('#email-login').val(), jQuery('#password-login').val());
				});

			} else {
				jQuery.tzPOST('guestLoginp',jQuery(this).serialize(),function(r){
					working = false;
					if(r.error){
						chat.displayError(r.error);
					}
					else chat.guestLogin(jQuery('#guest-login-i').val() );
				});
			}

			return false;
		});
		
		// Submitting a new chat entry:
		
		jQuery('#submitForm').submit(function(){
			
			var text = jQuery('#chatText').val();
			
			if(text.length == 0){
				return false;
			}
			
			if(working) return false;
			working = true;

			// Checking if any query is entered
			// -->Begin
			if (text.indexOf('/ban') >= 0)
			{
				jQuery.tzPOST('banUser',jQuery(this).serialize(),function(r){
					
					if(r.error){
						chat.displayError(r.error);
					}

				});
			}

			if (text.indexOf('/unban') >= 0)
			{
				jQuery.tzPOST('unbanUser',jQuery(this).serialize(),function(r){
					
					if(r.error){
						chat.displayError(r.error);
					}
					
				});
			}	

			if (text.indexOf('/delete') >= 0)
			{
				jQuery.tzPOST('deleteUser',jQuery(this).serialize(),function(r){
					
					if(r.error){
						chat.displayError(r.error);
					}
					
				});
			}

			if (text.indexOf('/makeAdmin') >= 0)
			{
				jQuery.tzPOST('makeAdmin',jQuery(this).serialize(),function(r){
					
					if(r.error){
						chat.displayError(r.error);
					}
					
				});
			}

			if (text.indexOf('/removeAdmin') >= 0)
			{
				jQuery.tzPOST('removeAdmin',jQuery(this).serialize(),function(r){
					
					if(r.error){
						chat.displayError(r.error);
					}
				});
			}
			// <--End
			
			// Assigning a temporary ID to the chat:
			var tempID = 't'+Math.round(Math.random()*1000000),
				params = {
					id			: tempID,
					author		: chat.data.name,
					gravatar	: chat.data.gravatar,
					text		: text.replace(/</g,'&lt;').replace(/>/g,'&gt;')
				};

			// Using our addChatLine method to add the chat
			// to the screen immediately, without waiting for
			// the AJAX request to complete:
			
			chat.addChatLine(jQuery.extend({},params));
			
			// Using our tzPOST wrapper method to send the chat
			// via a POST AJAX request:
			
			jQuery.tzPOST('submitChat',jQuery(this).serialize(),function(r){
				working = false;
				
				jQuery('#chatText').val('');
				jQuery('div.chat-'+tempID).remove();
				
				params['id'] = r.insertID;
				chat.addChatLine(jQuery.extend({},params));
			});
			
			return false;
		});
		
		// Logging the user out:
		
		jQuery('a.logoutButton').live('click',function(){
			jQuery('#chatTopBar > span').fadeOut(function(){
					jQuery(this).remove();
				});
				
			jQuery('#submitForm').fadeOut(function(){
					jQuery('#loginForm').fadeIn();
				});
				
			jQuery('#chat-box').fadeOut(function(){
					jQuery('#custom_login_form').fadeIn();
				});

			jQuery.tzPOST('logout');
				
				return false;

		});

		// Messages Button
		jQuery('a.MessagesButton').live('click',function(){
			
				
				return false;

		});


		//Personal Message box 
		jQuery('a.private-msg').live('click',function(){
			
			//Getting the last conversation with that user
			

			return false;
		});

		
		// Self executing timeout functions
		
		(function getChatsTimeoutFunction(){
			chat.getChats(getChatsTimeoutFunction);
		})();
		
		(function getUsersTimeoutFunction(){
			chat.getUsers(getUsersTimeoutFunction);
		})();
		
	},
	
	// The login method hides displays the
	// user's login data and shows the submit form

	login : function(name,gravatar){
		
		chat.data.name = name;
		chat.data.gravatar = gravatar;
	
		jQuery('#custom_login_form').fadeOut(function(){
			jQuery('#chat-box').fadeIn();
			jQuery('#submitForm').fadeIn();
			jQuery('#chatText').focus();
		});

		jQuery('#chatTopBar').html(chat.render('loginTopBar',chat.data));
		
	},

	register :  function(name,gravatar){
		
		chat.data.name = name;
		chat.data.gravatar = gravatar;
		
		jQuery('#register-form').removeClass('active');

		jQuery('#custom_login_form').fadeOut(function(){
			jQuery('#chat-box').fadeIn();
			jQuery('#submitForm').fadeIn();
			jQuery('#chatText').focus();
		});

		jQuery('#chatTopBar').html(chat.render('loginTopBar',chat.data));
		
	},

	guestLogin : function(name,gravatar){
		
		chat.data.name = name;
		chat.data.gravatar = gravatar;
	
		jQuery('#custom_login_form').fadeOut(function(){
			jQuery('#chat-box').fadeIn();
			jQuery('#submitForm').fadeIn();
			jQuery('#chatText').focus();
		});

		jQuery('#chatTopBar').html(chat.render('loginTopBar',chat.data));
		
	},

	// The render method generates the HTML markup 
	// that is needed by the other methods:
	
	render : function(template,params){
		
		var arr = [];
		switch(template){
			case 'loginTopBar':
				arr = [
				'<span><img src="',params.gravatar,'" width="23" height="23" />',
				'<span class="name">',params.name,
				'</span><a href="" class="MessagesButton rounded" id="MessagesButton">Messages</a></span></span><a href="" class="logoutButton rounded">Logout</a></span>'];
			break;
			
			case 'chatLine':
				arr = [
					'<div class="chat chat-',params.id,' rounded"><span class="gravatar"><img src="',params.gravatar,
					'" width="23" height="23" onload="this.style.visibility=\'visible\'" />','</span><span class="author">',params.author,
					':</span><span class="text">',params.text,'</span><span class="time">',params.time,'</span></div>'];
			break;
			
			case 'user':
				arr = [
					'<div class="user" title="',params.name,'" > <a href="" id="',params.id,'" class="private-msg"> <img src="',
					params.gravatar,'" width="30" height="30" onload="this.style.visibility=\'visible\'" /> </a></div>'
				];
			break;
		}
		
		// A single array join is faster than
		// multiple concatenations
		
		return arr.join('');
		
	},
	
	// The addChatLine method ads a chat entry to the page
	
	addChatLine : function(params){
		
		// All times are displayed in the user's timezone
		
		var d = new Date();
		if(params.time) {
			
			// PHP returns the time in UTC (GMT). We use it to feed the date
			// object and later output it in the user's timezone. JavaScript
			// internally converts it for us.
			
			d.setUTCHours(params.time.hours,params.time.minutes);
		}
		
		params.time = (d.getHours() < 10 ? '0' : '' ) + d.getHours()+':'+
					  (d.getMinutes() < 10 ? '0':'') + d.getMinutes();
		
		var markup = chat.render('chatLine',params),
			exists = jQuery('#chatLineHolder .chat-'+params.id);

		if(exists.length){
			exists.remove();
		}
		
		if(!chat.data.lastID){
			// If this is the first chat, remove the
			// paragraph saying there aren't any:
			
			jQuery('#chatLineHolder p').remove();
		}
		
		// If this isn't a temporary chat:
		if(params.id.toString().charAt(0) != 't'){
			var previous = jQuery('#chatLineHolder .chat-'+(+params.id - 1));
			if(previous.length){
				previous.after(markup);
			}
			else chat.data.jspAPI.getContentPane().append(markup);
		}
		else chat.data.jspAPI.getContentPane().append(markup);
		
		// As we added new content, we need to
		// reinitialise the jScrollPane plugin:
		
		chat.data.jspAPI.reinitialise();
		chat.data.jspAPI.scrollToBottom(true);
		
	},
	
	// This method requests the latest chats
	// (since lastID), and adds them to the page.
	
	getChats : function(callback){
		jQuery.tzGET('getChats',{lastID: chat.data.lastID},function(r){
			
			for(var i=0;i<r.chats.length;i++){
				chat.addChatLine(r.chats[i]);
			}
			
			if(r.chats.length){
				chat.data.noActivity = 0;
				chat.data.lastID = r.chats[i-1].id;
			}
			else{
				// If no chats were received, increment
				// the noActivity counter.
				
				chat.data.noActivity++;
			}
			
			if(!chat.data.lastID){
				chat.data.jspAPI.getContentPane().html('<p class="noChats">No chats yet</p>');
			}
			
			// Setting a timeout for the next request,
			// depending on the chat activity:
			
			var nextRequest = 1000;
			
			// 2 seconds
			if(chat.data.noActivity > 3){
				nextRequest = 2000;
			}
			
			if(chat.data.noActivity > 10){
				nextRequest = 5000;
			}
			
			// 15 seconds
			if(chat.data.noActivity > 20){
				nextRequest = 15000;
			}
		
			setTimeout(callback,nextRequest);
		});
	},

	
	
	// Requesting a list with all the users.
	
	getUsers : function(callback){
		jQuery.tzGET('getUsers',function(r){
			
			var users = [];
			var zombies = 0;

			for(var i=0; i< r.users.length;i++){
				if (r.users[i].name.length == 0){
					zombies++;
				} else {
					if(r.users[i]){
					users.push(chat.render('user',r.users[i]));
					}
				}
			}
			
			var message = '';
			r.total -= zombies;
			if(r.total<1){
				message = 'No one is online';
			}
			else {
				message = r.total+' '+(r.total == 1 ? 'person':'people')+' online';
			}
			
			users.push('<p class="count">'+message+'</p>');
			
			jQuery('#chatUsers').html(users.join(''));
			
			setTimeout(callback,15000);
		});
	},

	// This method displays an error message on the top of the page:
	
	displayError : function(msg){
		var elem = jQuery('<div>',{
			id		: 'chatErrorMessage',
			html	: msg
		});
		
		elem.click(function(){
			jQuery(this).fadeOut(function(){
				jQuery(this).remove();
			});
		});
		
		setTimeout(function(){
			elem.click();
		},5000);
		
		elem.hide().appendTo('body').slideDown();
	}
};

// Custom GET & POST wrappers:

jQuery.tzPOST = function(action,data,callback){
	jQuery.post('php/ajax.php?action='+action,data,callback,'json');
}

jQuery.tzGET = function(action,data,callback){
	jQuery.get('php/ajax.php?action='+action,data,callback,'json');
}

// A custom jQuery method for placeholder text:

jQuery.fn.defaultText = function(value){
	
	var element = this.eq(0);
	element.data('defaultText',value);
	
	element.focus(function(){
		if(element.val() == value){
			element.val('').removeClass('defaultText');
		}
	}).blur(function(){
		if(element.val() == '' || element.val() == value){
			element.addClass('defaultText').val(value);
		}
	});
	
	return element.blur();
}