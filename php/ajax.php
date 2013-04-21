<?php


/* Database Configuration. Add your details below */


/*
$dbOptions = array(
	'db_host' => 'dreamlookupcom.ipagemysql.com',
	'db_user' => 'mana',
	'db_pass' => 'system',
	'db_name' => 'chat_app'
);

*/

$dbOptions = array(
	'db_host' => 'localhost',
	'db_user' => 'root',
	'db_pass' => '',
	'db_name' => 'chatApp'
);

/* Database Config End */


error_reporting(E_ALL ^ E_NOTICE);

require "classes/DB.class.php";
require "classes/Chat.class.php";
require "classes/ChatBase.class.php";
require "classes/ChatLine.class.php";
require "classes/ChatUser.class.php";

session_name('webchat');
session_start();

if(get_magic_quotes_gpc()){
	
	// If magic quotes is enabled, strip the extra slashes
	array_walk_recursive($_GET,create_function('&$v,$k','$v = stripslashes($v);'));
	array_walk_recursive($_POST,create_function('&$v,$k','$v = stripslashes($v);'));
}

try{
	
	// Connecting to the database
	DB::init($dbOptions);
	
	$response = array();
	
	// Handling the supported actions:
	
	switch($_GET['action']){
		case 'register':
			$response = Chat::register($_POST['email'],$_POST['username'],$_POST['password']);
		break;

		case 'login':
			$response = Chat::login($_POST['email-login'],$_POST['password-login']);
		break;

		case 'guestLoginp':
			$response = Chat::guestLogin($_POST['guest-login-i']);
		break;
		
		case 'checkLogged':
			$response = Chat::checkLogged();
		break;
		
		case 'logout':
			$response = Chat::logout();
		break;
		
		case 'submitChat':
			$response = Chat::submitChat($_POST['chatText']);
		break;
		
		case 'getUsers':
			$response = Chat::getUsers();
		break;
		
		case 'getChats':
			$response = Chat::getChats($_GET['lastID']);
		break;

		

		case 'getLoggedUsers':
			$response = Chat::getLoggedUsers();
		break;

		case 'banUser':
			$response = Chat::banUser($_POST['chatText']);
		break;

		case 'unbanUser':
			$response = Chat::unbanUser($_POST['chatText']);
		break;

		case 'deleteUser':
			$response = Chat::deleteUser($_POST['chatText']);
		break;

		case 'makeAdmin':
			$response = Chat::makeAdmin($_POST['chatText']);
		break;

		case 'removeAdmin':
			$response = Chat::removeAdmin($_POST['chatText']);
		break;
		
		default:
			throw new Exception('Wrong action');
	}
	
	echo json_encode($response);
}
catch(Exception $e){
	die(json_encode(array('error' => $e->getMessage())));
}

?>