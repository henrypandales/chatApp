<?php

/* The Chat class exploses public static methods, used by ajax.php */

class Chat{

	public static function register($email,$username,$password){
		if(!$email || !$username || !$password){
			throw new Exception('Fill in all the required fields Except Name Field.' . $email . ' - ' . $username . ' - ' . $password);
			return false;
		}

		// Preparing the gravatar hash
		$gravatar = md5(strtolower(trim($email)));
		$password = md5($password);

		$user = new ChatUser(array(
			'email'	    => $email,
			'username'  => $username,
			'password'  => $password,
			'gravatar'	=> $gravatar
		));
		
		if ($user->emailExist()){
			throw new Exception($email . ' is already registered.');
			return false;
		}

		if ($user->usernameExist() || $user->guestUsernameExist()){
			throw new Exception($username . ' is in use, PLease choose another nick.');
			return false;
		}


		// The save method returns a MySQLi object
		if($user->save()->affected_rows == 1){
			$user->saveSession();
			$_SESSION['user']	= array(
				'email'	    => $email,
				'name'  => $username,
				'gravatar'	=> $gravatar,
			);

			return array(
				'status'	=> 1,
				'name'		=> $username,
				'gravatar'	=> Chat::gravatarFromHash($gravatar)
			);
		} else {
			throw new Exception('Oops, an error just occured. Please refresh the page and try again.');
			return false;
		}
	}
	

	public static function guestLogin($username){
		if(!$username){
			throw new Exception('Fill in a username.');
		}

		//Just filling some informations
		$email = 'guest@chatapp.com';
		$gravatar = 'empty';
		$password = 'empty';

		$user = new ChatUser(array(
			'email'	    => $email,
			'username'  => $username,
			'password'  => $password,
			'gravatar'	=> $gravatar
		));


		if ($user->usernameExist() || $user->guestUsernameExist()){
			throw new Exception($username . ' is in use, PLease choose another nick.');
			return false;
		}

		//Saving the Guest session
		$user->saveSession();
		$_SESSION['user']	= array(
			'email'	    => $email,
			'name'  => $username,
			'isAdmin' => $user->isAdmin(),
			'gravatar'	=> $gravatar,
			);
			

		return array(
			'status'	=> 1,
			'name'		=> $username,
			'gravatar'	=> Chat::gravatarFromHash($gravatar)
		);
	}

	public static function getUsername ($password, $email){
		$query = DB::query("SELECT username FROM users WHERE (password like '".$password."' AND email like '".$email."')");

		$row = $query->fetch_array();
		$row = (array) $row;
		return $row[0];
	}

	public static function login($email,$password){
		
		if(!$password || !$email){
			throw new Exception('Fill in all the required fields.');
		}
		
		/*if(!filter_input(INPUT_POST,'email',FILTER_VALIDATE_EMAIL)){
			throw new Exception('Your email is invalid.');
		}*/

		// Preparing the gravatar hash:
		$gravatar = md5(strtolower(trim($email)));
		$password = md5($password);
		$usr = Chat::getUsername($password, $email);
		$user = new ChatUser(array(
			'email'	    => $email,
			'username'  => $usr,
			'password'  => $password,
			'gravatar'	=> $gravatar
		));

		if ($user->isBanned()){
			throw new Exception('You are Banned form the chat.');
		}
		
		if ($user->isRegistered()){
			//The user is now logged into the chat App.
			//Saving the temporary Session
			$user->saveSession();
			
			
			$_SESSION['user']	= array(
			'email'	    => $email,
			'name'  => $usr,
			'isAdmin' => $user->isAdmin(),
			'gravatar'	=> $gravatar,
			);
			
			DB::query("UPDATE users SET online = 1 WHERE email like '".$email."';");

			return array(
				'status'	=> 1,
				'name'		=> $usr,
				'gravatar'	=> Chat::gravatarFromHash($gravatar)
			);
		} else {
			throw new Exception('It Seems that you are not Registered or Entered wrong information.');
		}
		
	}
	
	public static function checkLogged(){
		$response = array('logged' => false);
			
		if($_SESSION['user']['name'] != ''){
			$response['logged'] = true;
			$response['loggedAs'] = array(
				'name'		=> $_SESSION['user']['name'],
				'gravatar'	=> Chat::gravatarFromHash($_SESSION['user']['gravatar'])
			);

		}
		return $response;
	}
	
	public static function logout(){
		DB::query("DELETE FROM webchat_users WHERE name like '".DB::esc($_SESSION['user']['name'])."'");
		DB::query("UPDATE users SET online = 0 WHERE email like '".DB::esc($_SESSION['user']['email'])."';");

		$aaa = $_SESSION['user']['name'];

		$_SESSION = array();
		unset($_SESSION);

		return array('status' => $aaa);
	}
	
	public static function submitChat($chatText){
		if(!$_SESSION['user']){
			throw new Exception('You are not logged in');
		}
		
		if(!$chatText){
			throw new Exception('You haven\' entered a chat message.');
		}
	
		$chat = new ChatLine(array(
			'author'	=> $_SESSION['user']['name'],
			'gravatar'	=> $_SESSION['user']['gravatar'],
			'text'		=> $chatText
		));
	
		// The save method returns a MySQLi object
		$insertID = $chat->save()->insert_id;
	
		return array(
			'status'	=> 1,
			'insertID'	=> $insertID
		);
	}
	
	public static function getUsers(){
		if($_SESSION['user']['name']){
			$user = new ChatUser(array('name' => $_SESSION['user']['name']));
			$user->update();
		}
		
		// Deleting chats older than 5 minutes and users inactive for 30 Minutes
		
		DB::query("DELETE FROM webchat_lines WHERE ts < SUBTIME(NOW(),'0:5:0')");
		DB::query("DELETE FROM webchat_users WHERE last_activity < SUBTIME(NOW(),'0:30:00')");
		
		$result = DB::query('SELECT * FROM webchat_users ORDER BY name ASC LIMIT 18');
		
		$users = array();
		while($user = $result->fetch_object()){
			$user->gravatar = Chat::gravatarFromHash($user->gravatar,30);
			$users[] = $user;
		}
	
		return array(
			'users' => $users,
			'total' => DB::query('SELECT COUNT(*) as cnt FROM webchat_users')->fetch_object()->cnt
		);
	}
	
	public static function getChats($lastID){
		//$lastID = (int)$lastID;
	
		$result = DB::query('SELECT * FROM webchat_lines ORDER BY ts DESC');
	
		$chats = array();
		while($chat = $result->fetch_object()){
			
			// Returning the GMT (UTC) time of the chat creation:
			
			$chat->time = array(
				'hours'		=> gmdate('H',strtotime($chat->ts)),
				'minutes'	=> gmdate('i',strtotime($chat->ts))
			);
			
			$chat->gravatar = Chat::gravatarFromHash($chat->gravatar);
			
			$chats[] = $chat;
		}
	
		return array('chats' => $chats);
	}
	
	public static function getConversation($id){
		$id = (int)$id;

		$result = DB::query('SELECT * FROM messages WHERE `to` = '.$id.';');

		$chats = array();
		if (DB::getMySQLiObject()->affected_rows > 0){
			while($chat = $result->fetch_object()){
				// Returning the GMT (UTC) time of the chat creation:
				
				$chat->time = array(
					'hours'		=> gmdate('H',strtotime($chat->ts)),
					'minutes'	=> gmdate('i',strtotime($chat->ts))
				);
				
				$chat->gravatar = Chat::gravatarFromHash($chat->gravatar);
				
				$chats[] = $chat;
			}
		}
		

		return array('chats' => $chats);
	}

	public static function banUser($usr)
	{
		$user = new ChatUser(array(
			'email'	    => $_SESSION['user']['email'],
			'username'  => $_SESSION['user']['name'],
			'password'  => '',
			'gravatar'	=> $_SESSION['user']['gravatar']
		));

		$usr = str_replace('/ban ', '', $usr);

		if ($_SESSION['user']['isAdmin'] == 1)
		{
			if ($user->banMe($usr))
			{
				throw new Exception('You just banned : ' . $usr);
			} else {
				throw new Exception('An error just occured, Make sure you entered the right username.');
			}
		} else {
			return false;
		}
	}

	public static function unbanUser($usr)
	{
		$user = new ChatUser(array(
			'email'	    => $_SESSION['user']['email'],
			'username'  => $_SESSION['user']['name'],
			'password'  => '',
			'gravatar'	=> $_SESSION['user']['gravatar']
		));

		if ($_SESSION['user']['isAdmin'] == 1)
		{
			$usr = str_replace('/unban ', '', $usr);
			if ($user->unbanMe($usr))
			{
				throw new Exception('You just unbanned : ' . $usr);
			} else {
				throw new Exception('An error just occured, Make sure you entered the right username.');
			}
		} else {
			return false;
		}
	}

	public static function deleteUser($usr)
	{
		$user = new ChatUser(array(
			'email'	    => $_SESSION['user']['email'],
			'username'  => $_SESSION['user']['name'],
			'password'  => '',
			'gravatar'	=> $_SESSION['user']['gravatar']
		));

		if ($_SESSION['user']['isAdmin'] == 1)
		{
			$usr = str_replace('/delete ', '', $usr);
			if ($user->deleteMe($usr))
			{
				throw new Exception('You just deleted : ' . $usr);
			} else {
				throw new Exception('An error just occured, Make sure you entered the right username.');
			}
		} else {
			return false;
		}
	}

	public static function makeAdmin($usr)
	{
		$user = new ChatUser(array(
			'email'	    => $_SESSION['user']['email'],
			'username'  => $_SESSION['user']['name'],
			'password'  => '',
			'gravatar'	=> $_SESSION['user']['gravatar']
		));

		if ($_SESSION['user']['isAdmin'] == 1)
		{
			$usr = str_replace('/makeAdmin ', '', $usr);
			if ($user->makeAdmin($usr))
			{
				throw new Exception('You just made "' . $usr . '" Admin.');
			} else {
				throw new Exception('An error just occured, Make sure you entered the right username.');
			}
		} else {
			return false;
		}
	}

	public static function removeAdmin($usr)
	{
		$user = new ChatUser(array(
			'email'	    => $_SESSION['user']['email'],
			'username'  => $_SESSION['user']['name'],
			'password'  => '',
			'gravatar'	=> $_SESSION['user']['gravatar']
		));

		if ($_SESSION['user']['isAdmin'] == 1)
		{
			$usr = str_replace('/removeAdmin ', '', $usr);
			if ($user->removeAdmin($usr))
			{
				throw new Exception('You just removed Admin privileges for ' . $usr .  '.');
			} else {
				throw new Exception('An error just occured, Make sure you entered the right username.');
			}
		} else {
			return false;
		}
	}

	public static function gravatarFromHash($hash, $size=23){
		return 'http://www.gravatar.com/avatar/'.$hash.'?size='.$size.'&amp;default='.
				urlencode('http://www.gravatar.com/avatar/ad516503a11cd5ca435acc9bb6523536?size='.$size);
	}
}


?>