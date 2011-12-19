<?

class QuestionsAPIController extends APIController {
	
	public function get($id = null){
		if(!isset($this->get['survey_id'])){
			$this->message = "Missing one or more required parameters";
			$this->status = 400;
			return;
		}

		$survey = new Survey($this->get['survey_id']);

		if(!$survey->exists()){
			$this->message = "Survey does not exist";
			$this->status = 404;
			return;
		}

		$surveyData = $survey->apiData();
		if(isset($id)){
			if(!$surveyData['questions'][$id]){
				$this->message = "Question does not exist";
				$this->status = 404;
				return;
			}

			$this->response[$this->slug][] = $surveyData['questions'][$id];
			return;
		}

		$this->response[$this->slug][] = $surveyData['questions'];
	}

	public function post(){
		if(!isset($this->get['survey_id'], $this->post['question'])){
			$this->message = "Missing one or more required parameters";
			$this->status = 400;
			return;
		}

		$survey = new Survey($this->get['survey_id']);

		if(!$survey->exists()){
			$this->message = "Survey does not exist";
			$this->status = 404;
			return;
		}

		if(($questionID = $survey->addQuestion($this->post['question'])) === false){
			$this->message = 'Internal Server Error';
			$this->status = 500;
			return;
		}

		$surveyData = $survey->apiData();
		$this->response[$this->slug][] = $surveyData['questions'][$questionID];
		return;
	}
}

?>