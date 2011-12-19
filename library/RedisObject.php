<?

class RedisObject {

	public $id;
	private $exists;
	
	public function __construct($id){
		$this->id = either($id, uniqid());
		$this->exists = false;

		$data = Util::multibulk_to_array(Redis::hgetall($this->key()));
		foreach($data as $key => $val){
			$this->$key = json_decode($val, true);
		}

		$this->exists = count($data) > 0;
	}

	public function apiData($auth, $userID){
		return $this->getFields();
	}

	public function encodeFields(){
		$data = array();
		foreach($this->getFields() as $key => $value){
			$data[$key] = json_encode($value);
		}

		return $data;
	}

	public function getFields(){
		$getFields = function($obj) { return get_object_vars($obj); };

		return $getFields($this);
	}

	public function exists(){
		return $this->exists;
	}

	private function key(){
		return get_class($this) . ':' . $this->id;
	}

	public static function listAll($offset = 0, $num = -1){
		return Redis::zrange(get_called_class(), $offset, ( $num == -1 ? $num : $offset + $num ));
	}

	public function remove(){
		if(!$this->id) return false;

		Redis::del($this->key());
		Redis::zrem(get_class($this), $this->id);
	}

	public function save(){
		if(!$this->id) return false;

		$args = Util::array_to_multibulk($this->encodeFields());
		unset($args['id']);

		call_user_func_array(array('Redis', 'hmset'), array_merge(array($this->key()), $args));
		Redis::zadd(get_class($this), microtime(true), $this->id);
	}

	public function setData($fields){
		foreach($this->getFields() as $field => $value){
			$this->$field = $fields[$field];
		}
	}

}

?>