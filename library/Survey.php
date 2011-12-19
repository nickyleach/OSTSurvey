<?

class Survey extends RedisObject {

	public $name;
	public $ownerID;
	public $questions;

	public function __construct($id){
		parent::__construct($id);

		if(!$this->exists()) return;
	}

	public function addAnswer($questionID, $answer){
		if(!$this->questions[$questionID]) return false;

		$this->questions[$questionID]['answers'][] = array(
			'id' => count($this->questions[$questionID]['answers']),
			'answer' => $answer,
		);

		$this->save();

		return count($this->questions[$questionID]['answers']) - 1;
	}

	public function addQuestion($question){
		$this->questions[] = array(
			'id' => count($this->questions),
			'answers' => array(),
			'question' => $question,
		);
		
		$this->save();

		return count($this->questions) - 1;
	}

	public function answer($questionID, $answerID, $userID){
		Redis::sadd("Survey:{$this->id}:{$questionID}:{$answerID}", $userID);
	}

	public function apiData($auth, $userID){
		$data = parent::apiData($auth, $userID);

		foreach($this->results() as $questionID => $question){
			foreach($question['answers'] as $answerID => $votes){
				$data[$questionID]['answers'][$answerID]['votes'] = $votes;
			}
		}

		return $data;
	}

	public static function create($name, $userID){
		$survey = new Survey();
		$survey->name = $name;
		$survey->ownerID = $userID;
		$survey->save();

		$user = $survey->owner();
		$user->addSurvey($survey->id);

		return $survey->id;
	}

	public function owner(){
		return new User($this->ownerID);
	}

	public function remove(){
		// Find all of the keys used to store answers
		$answerKeys = array();
		foreach($this->questions as $questionID => $question){
			foreach($question['answers'] as $answerID => $answer){
				$answerKeys[] = "Survey:{$this->id}:{$questionID}:{$answerID}";
			}
		}

		// Remove all of the answer sets
		if(count($answerKeys)){
			call_user_func_array(array('Redis', 'del'), $answerKeys);
		}

		parent::remove();
	}

	public function removeQuestion($questionID){
		$this->questions[$questionID] = null;
		
		$this->save();
	}

	public function results(){
		$results = array();
		foreach($this->questions as $questionID => $question){
			if(!$question) continue;

			foreach($question['answers'] as $answerID => $answer){
				// Result is the number of users who answered the question
				$results[$questionID][$answerID] = Redis::scard("Survey:{$this->id}:{$questionID}:{$answerID}");
			}
		}

		return $results;
	}

}

?>