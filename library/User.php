<?

class User extends RedisObject {

	const PASSWORD_SALT = "e6rqqQSNgV2rDbYc4Zup9kFUGKUwME1Ehjqbb8vc";

	public $username;
	public $password;

	public function __construct($id){
		parent::__construct($id);

		if(!$this->exists()) return;

		$this->surveys = $this->surveys();
	}

	public function addSurvey($surveyID){
		if(!$this->exists()) return;

		Redis::sadd("User:{$this->id}:surveys", $surveyID);
	}

	public function apiData($auth, $userID){
		$data = parent::apiData($auth, $userID);

		unset($data['password']);

		$data['surveys'] = $this->surveys();

		return $data;
	}

	private static function encryptPassword($passsword){
		return md5($password . self::PASSWORD_SALT);
	}

	public static function login($username, $password){
		$user = new User($username);

		if($user->exists() && $user->password == self::encryptPassword($password)){
			return $user->id;
		}

		return false;
	}

	public function remove(){
		if(!$this->exists()) return false;

		// Remove all of the user's surveys
		foreach($this->surveys as $surveyID){
			$survey = new Survey($surveyID);
			$survey->remove();
		}

		// And the container which references them
		Redis::del("User:{$this->id}:surveys");

		parent::remove();
	}

	public function removeSurvey($surveyID){
		if(!$this->exists()) return;

		Redis::srem("User:{$this->id}:surveys", $surveyID);
	}

	public function setPassword($password){
		$this->password = self::encryptPassword($password);
	}

	public function surveys(){
		return Redis::smembers("User:{$this->id}:surveys");
	}
	
}

?>