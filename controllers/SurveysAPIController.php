<?

class SurveysAPIController extends APIController {
	
	public function get($id){
		if($id){
			$survey = new Survey($id);

			if(!$survey->exists()){
				$this->message = "Survey not found";
				$this->status = 404;
			}

			$this->response[$this->slug][] = $survey->apiData();
			return;
		}

		$surveyIDs = Survey::listAll($this->get['offset'], $this->get['num']);

		if(!count($surveyIDs)){
			$this->message = "No surveys found";
			$this->status = 204;
		}

		foreach($surveyIDs as $surveyID){
			$survey = new Survey($surveyID);
			$this->response[$this->slug][] = $survey->apiData();
		}
	}

	public function post(){
		if(!Session::$user['id']){
			$this->message = "Unauthorized";
			$this->status = 403;
			return;
		}

		if(!isset($this->post['name'])){
			$this->message = "Missing one or more required parameters";
			$this->status = 400;
			return;
		}

		$surveyID = Survey::create($this->post['name'], Session::$user['id']);
		$survey = new Survey($surveyID);

		$this->response[$this->slug][] = $survey->apiData();
	}

}

?>