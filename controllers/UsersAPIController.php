<?

class UsersAPIController extends APIController {
	
	public function get($id){
		if($id){
			$user = new User($id);

			if(!$user->exists()){
				$this->message = "User not found";
				$this->status = 404;
			}

			$this->response[$this->slug][] = $user->apiData();
			return;
		}

		$userIDs = User::listAll($this->get['offset'], $this->get['num']);

		if(!count($userIDs)){
			$this->message = "No users found";
			$this->status = 204;
		}

		foreach($userIDs as $userID){
			$user = new User($userID);
			$this->response[$this->slug][] = $user->apiData();
		}
	}

	public function post(){
		if(!isset($this->post['username'], $this->post['password'])){
			$this->message = "Missing one or more required parameters";
			$this->status = 400;
			return;
		}

		$user = new User($this->post['username']);
		if($user->exists()){
			$this->message = "User already exists";
			$this->status = 400;
			return;
		}

		$user->setData($this->post);
		$user->save();

		$this->message = "User created";
		$this->status = 201;

		$this->response[$this->slug][] = $user->apiData();
	}

}

?>