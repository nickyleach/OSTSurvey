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