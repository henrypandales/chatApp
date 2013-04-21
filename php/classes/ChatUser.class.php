<?php

class ChatUser extends ChatBase{
	
	protected $email = '', $gravatar = '', $username = '', $password = '';
	//$gravatar variable is the User email.
	
	public function save(){
		
		DB::query("
			INSERT INTO users (username, email, password, gravatar)
			VALUES (
				'".DB::esc($this->username)."',
				'".DB::esc($this->email)."',
				'".DB::esc($this->password)."',
				'".DB::esc($this->gravatar)."'
		)");
	
		return DB::getMySQLiObject();
	}

	public function saveSession(){
		if (!DB::esc($this->username) ){
			throw new Exception('Erreur !!');
			return false;
		}
		//|| DB::esc($this->username).length == 0
		DB::query("
			INSERT INTO webchat_users (name, gravatar)
			VALUES (
				'".DB::esc($this->username)."',
				'".DB::esc($this->gravatar)."'
		)");
		
	}

	public function emailExist (){
		DB::query("SELECT * FROM users WHERE email like '".DB::esc($this->email)."'");
		return DB::getMySQLiObject()->affected_rows;
	}

	public function usernameExist (){
		DB::query("SELECT * FROM users WHERE username like '".DB::esc($this->username)."'");
		return DB::getMySQLiObject()->affected_rows;
	}

	public function guestUsernameExist (){
		DB::query("SELECT * FROM webchat_users WHERE name like '".DB::esc($this->username)."'");
		return DB::getMySQLiObject()->affected_rows;
	}
	
	public function isRegistered (){
		DB::query("SELECT * FROM users WHERE (password like '".DB::esc($this->password)."' AND email like '".DB::esc($this->email)."')");
		return DB::getMySQLiObject()->affected_rows;
	}

	/*public function getUsername (){
		$query = DB::query("SELECT username FROM users WHERE (password like '".DB::esc($this->password)."' AND email like '".DB::esc($this->email)."')");

		$row = $query->fetch_array();
		$row = (array) $row;
		return $row[0];
	}*/

	public function update(){
		DB::query("
			INSERT INTO webchat_users (name, gravatar)
			VALUES (
				'".DB::esc($this->username)."',
				'".DB::esc($this->gravatar)."'
			) ON DUPLICATE KEY UPDATE last_activity = NOW()");
	}

	public function isAdmin(){
		$query = DB::query("SELECT isAdmin FROM users WHERE (password like '".DB::esc($this->password)."' AND email like '".DB::esc($this->email)."')");

		$row = $query->fetch_array();
		$row = (array) $row;

		return $row['isAdmin'];
	}
	
	public function isBanned(){
		$query = DB::query("SELECT isAdmin FROM users WHERE (password like '".DB::esc($this->password)."' AND email like '".DB::esc($this->email)."')");

		$row = $query->fetch_array();
		$row = (array) $row;

		return $row['banned'];
	}

	public function banMe($name){
		DB::query("UPDATE users SET banned = 1 WHERE username LIKE '". $name ."';");
		return DB::getMySQLiObject()->affected_rows;
	}

	public function unbanMe($name){
		DB::query("UPDATE users SET banned = 0 WHERE username LIKE '". $name ."';");
		return DB::getMySQLiObject()->affected_rows;
	}

	public function deleteMe($name){
		DB::query("DELETE FROM users WHERE username like '". $name ."';");
		return DB::getMySQLiObject()->affected_rows;
	}

	public function makeAdmin($name){
		DB::query("UPDATE users SET isAdmin = 1 WHERE username LIKE '". $name ."';");
		return DB::getMySQLiObject()->affected_rows;
	}

	public function removeAdmin($name){
		DB::query("UPDATE users SET isAdmin = 0 WHERE username LIKE '". $name ."';");
		return DB::getMySQLiObject()->affected_rows;
	}

}

?>