<?

class Session {

	public static $user = array();
	
	public static function login($userID){
		self::$user['id'] = $userID;
		return true;
	}

	public static function logout(){
		self::$user = array();
		return true;
	}

}

?>