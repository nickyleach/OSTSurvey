<?

class RequestArray implements ArrayAccess {
	private $container;
	
	public function __construct(&$container = array()){
		$this->container = &$container;
	}
	
	public function offsetSet($offset, $value) {
		if (is_null($offset)) {
			$this->container[] = $value;
			$_REQUEST[] = $value;
		} else {
			$this->container[$offset] = $value;
			$_REQUEST[$offset] = $value;
		}
	}
	
	public function offsetExists($offset) {
		return isset($this->container[$offset]);
	}
	
	public function offsetUnset($offset) {
		unset($this->container[$offset]);
		unset($_REQUEST[$offset]);
	}
	
	public function offsetGet($offset) {
		return ( isset($this->container[$offset]) ? $this->container[$offset] : null );
	}
	
	public function toArray(){
		// !TODO - Handle this special case more elegantly
		$container = $this->container;
		unset($container['ign_querystring']);
		
		return $container;
	}
	
	public function update(&$arr = array()){
		$this->container = &$arr;
	}
}

?>