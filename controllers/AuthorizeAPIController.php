<?

class AuthorizeAPIController extends APIController {
	
	public function post(){
		if(!($userID = User::login($this->post['username'], $this->post['password']))){
			$this->status = 403;
			$this->message = "Invalid username or password";
		} else { 
			Session::login($userID);
			
			$this->response = array(
				$this->slug => array(
					'auth_id' => $userID,
					'auth_token' => APIController::authToken($userID),
				),
			);
		}
	}

}

?>