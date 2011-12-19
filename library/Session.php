<?

class Session {

	public static $user = array();
	
	public static function login($userID){
		$user = new User($userID);
		self::$user = $user->apiData(API_SELF, $userID);
	}

}

?>