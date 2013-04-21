<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" >
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="HTML5/CSS3 Web chat Application, Ajax Powered" >
    <meta name="keywords" content="chat, web, html5, ajax, app, instant" >
    <meta name="author" content="Atef Sayadi" >
    <title>Chat Web Application</title>

    <link rel="stylesheet" type="text/css" href="js/jScrollPane/jScrollPane.css" />
    <link rel="stylesheet" type="text/css" href="css/page.css" />
    <link rel="stylesheet" type="text/css" href="css/chat.css" />
    <link rel="stylesheet" type="text/css" href="css/form.css" />
</head>

<body>

<div id="chatContainer">

    <div id="chat-box" style="display: none;">
        <div id="chatTopBar" class="rounded"></div>
            <div id="chatLineHolder"></div>
            
            <div id="chatUsers" class="rounded"></div>
            <div id="chatBottomBar" class="rounded">
                <div class="tip"></div>
                
                <!--<form id="loginForm3" method="post" action="">
                    <input id="name1" name="name1" class="rounded" maxlength="16" />
                    <input id="email1" name="email1" class="rounded" />
                    <input type="submit" class="blueButton" value="Login" />
                </form>-->
                
                <form id="submitForm" method="post" action="">

                    <!--<table>
                        <tr>
                            <td style="width: 100%;"><textarea id="chatText" name="chatText" class="rounded"></textarea></td>
                            <td><input type="submit" class="blueButton" value="Submit" /></td>
                        </tr>
                    </table>-->
                    <input id="chatText" name="chatText" class="rounded" maxlength="255" />
                    <input type="submit" class="blueButton" value="Submit" />
                </form>
                
        </div>
    </div>


    <div class="wrapper" id="custom_login_form">           
            <div class="content">
                <div id="form_wrapper" class="form_wrapper">
                    <form method="post" action="" class="register" id="register-form">
                        <h3>Register</h3>
                        <div class="column">
                            <div>
                                <label>Username:</label>
                                <input type="text" name="username" maxlength="16" id="username">
                                <span class="error">Please enter a valid Username</span>
                            </div>

                            <div>
                                <label>Email:</label>
                                <input type="text" name="email" maxlength="55" id="email">
                                <span class="error">Please enter a valid Email</span>
                            </div>
                        </div>
                        <div class="column">
                            <div>
                                <label>Password:</label>
                                <input type="password" name="password" id="password">
                                <span class="error">Please enter a valid Password</span>
                            </div>

                             <div>
                                <label>Password:</label>
                                <input type="password" name="password2" id="password2">
                                <span class="error">Please enter a valid Password</span>
                            </div>
                        </div>
                        <div class="bottom">
                            <input type="submit" value="Register" id="btn-register"/>
                            <a href="index.html" rel="login" class="linkform">You have an account already? Log in here</a>
                            <div class="clear"></div>
                        </div>
                    </form>
                
                    
                    <form id="loginForm" method="post" class="login active form-1">
                        <h3>Login</h3>
                        <p class="field">
                            <input type="email" name="email-login" id="email-login" maxlength="55" placeholder="Email">
                            <i class="icon-user icon-large" style="top: 16px;"></i>
                        
                        <p class="field">                           
                            <input type="password" name="password-login" id="password-login" placeholder="Password">
                            <i class="icon-lock icon-large" style="top: 2px;"></i>
                        </p>
                        <p class="submit">
                            <button type="submit" name="submit"><i class="icon-arrow-right icon-large"></i></button>
                        </p>
                    

                        <div class="bottom">
                            <!--<div class="remember"></div>
                            <form method="post" action=""  id="guestLoginForm" class="login active form-1" style="height:74px">-->
                                <p class="field">
                                    <input type="text" name="guest-login-i" id="guest-login-i" placeholder="Guest"  style="width: 112px; position: relative; top: -2px;">
                                    <i class="icon-user icon-large" style="top: 0px;"></i>
                                </p>
                                <!--<input type="checkbox" style="width:0px; height:0px;"/><span>Keep me logged in</span>-->
                                 <input type="submit" value="Login" id="guest-btn-login" style="position: absolute; left: 183px; top: 200px;">
                            <!--</form>-->
                            
                           
                            <a href="register.html" rel="register" class="linkform">You don't have an account yet? Register here</a>
                            
                            <a href="forgot_password.html" rel="forgot_password" class="linkform">Forgot your password?</a>
                            <div class="clear"></div>
                        </div>
                        <div class="clear"></div>
                    </form>


                    <form method="post" class="forgot_password">
                        <h3>Forgot Password</h3>
                        <div>
                            <label>Email:</label>
                            <input type="text" maxlength="55" id="forgotten-email">
                            <span class="error">This is an error</span>
                        </div>
                        <div class="bottom">
                            <input type="submit" value="Send reminder"></input>
                            <a href="index.html" rel="login" class="linkform">Suddenly remebered? Log in here</a>
                            <a href="register.html" rel="register" class="linkform">You don't have an account? Register here</a>
                            <div class="clear"></div>
                        </div>
                    </form>
                    
                    
                </div>
                <div class="clear"></div>
            </div>
            
        </div>
        

        <!-- The JavaScript -->
        <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
        <script type="text/javascript" src="js/jscripts/tiny_mce/tiny_mce.js"></script>
        <script type="text/javascript">
            $(function() {
                    //the form wrapper (includes all forms)
                var $form_wrapper   = $('#form_wrapper'),
                    //the current form is the one with class active
                    $currentForm    = $form_wrapper.children('form.active'),
                    //the change form links
                    $linkform       = $form_wrapper.find('.linkform');
                        
                //get width and height of each form and store them for later                        
                $form_wrapper.children('form').each(function(i){
                    var $theForm    = $(this);
                    //solve the inline display none problem when using fadeIn fadeOut
                    if(!$theForm.hasClass('active'))
                        $theForm.hide();
                    $theForm.data({
                        width   : $theForm.width(),
                        height  : $theForm.height()
                    });
                });
                
                //set width and height of wrapper (same of current form)
                setWrapperWidth();
                
                /*
                clicking a link (change form event) in the form
                makes the current form hide.
                The wrapper animates its width and height to the 
                width and height of the new current form.
                After the animation, the new form is shown
                */
                $linkform.bind('click',function(e){
                    var $link   = $(this);
                    var target  = $link.attr('rel');
                    $currentForm.fadeOut(400,function(){
                        //remove class active from current form
                        $currentForm.removeClass('active');
                        //new current form
                        $currentForm= $form_wrapper.children('form.'+target);
                        //animate the wrapper
                        $form_wrapper.stop()
                                     .animate({
                                        width   : $currentForm.data('width') + 'px',
                                        height  : $currentForm.data('height') + 'px'
                                     },500,function(){
                                        //new form gets class active
                                        $currentForm.addClass('active');
                                        //show the new form
                                        $currentForm.fadeIn(400);
                                     });
                    });
                    e.preventDefault();
                });
                
                function setWrapperWidth(){
                    $form_wrapper.css({
                        width   : $currentForm.data('width') + 'px',
                        height  : $currentForm.data('height') + 'px'
                    });
                }
                
                /*
                for the demo we disabled the submit buttons
                if you submit the form, you need to check the 
                which form was submited, and give the class active 
                to the form you want to show
                */
                $form_wrapper.find('input[type="submit"]')
                             .click(function(e){
                                e.preventDefault();
                             });    
            });
        </script>

        <script type="text/javascript">
            tinyMCE.init({
                // General options
                mode : "textareas",
                theme : "advanced",
                plugins : "emotions",
                
                // Theme options
                theme_advanced_buttons1 : "bold,italic,underline,strikethrough,|,emotions",
                width: "100%"
            });
        </script>
    
</div>


<script src="//ajax.googleapis.com/ajax/libs/jquery/1.6.4/jquery.min.js"></script>

<script src="js/jScrollPane/jquery.mousewheel.js"></script>
<script src="js/jScrollPane/jScrollPane.min.js"></script>
<script src="js/script.js"></script>



</body>
</html>
