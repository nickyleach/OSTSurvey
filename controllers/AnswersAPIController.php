<?

class AnswersAPIController extends APIController {
	
	public function get($id = null){
		if(!isset($this->get['survey_id'], $this->get['question_id'])){
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
		if(!$surveyData['questions'][$this->get['question_id']]){
			$this->message = "Question does not exist";
			$this->status = 404;
			return;
		}

		if(isset($id)){
			if(!$surveyData['questions']['answers'][$id]){
				$this->message = "Answer does not exist";
				$this->status = 404;
				return;
			}

			$this->response[$this->slug][] = $surveyData['questions'][$id]['answers'][$id];
			return;
		}

		$this->response[$this->slug][] = $surveyData['questions'][$this->get['question_id']]['answers'];
	}

	public function post(){
		if(!isset($this->get['survey_id'], $this->get['question_id'], $this->post['answer'])){
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
		if(!$surveyData['questions'][$this->get['question_id']]){
			$this->message = "Question does not exist";
			$this->status = 404;
			return;
		}

		if(($answerID = $survey->addAnswer($this->get['question_id'], $this->post['answer'])) === false){
			$this->message = 'Internal Server Error';
			$this->status = 500;
			return;
		}

		$surveyData = $survey->apiData();
		$this->response[$this->slug][] = $surveyData['questions'][$this->get['question_id']]['answers'][$answerID];
		return;
	}
}

?>