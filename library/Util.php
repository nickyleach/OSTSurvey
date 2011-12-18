<?

class Util {
	public static function execute($closure, $args = array()){
		call_user_func_array($closure, $args);
	}
	
	public static function redirect($url, $hard = false){
		if($hard){
			header('HTTP/1.1 301 Moved Permanently');
		} else {
			header('Cache-Control:no-store, no-cache, must-revalidate, post-check=0, pre-check=0  ');
			header('Pragma: no-cache ');
		}
		
		header("Location: $url");
		exit;
	}
	
	public static function mutlibulk_to_array($response){
		$keys = array();
		
		foreach($response as $key => $value){
			if($key % 2 == 0){
				$keys[$value] = $response[$key + 1];
			}
		}
		
		return $keys;
	}
}

?>