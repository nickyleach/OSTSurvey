<?

class Routing {
	protected static $qs;
	protected static $api;
	
	public static function action(){
		if(self::$api){
			return strtolower( $_GET['method'] ? $_GET['method'] : $_SERVER['REQUEST_METHOD'] );
		}
		
		return either(self::$qs[1], null);
	}

	public static function apiVersion(){
		return self::$qs[1];
	}
	
	public static function args(){
		return either(array_slice(self::$qs, 2), array());
	}
	
	public static function controllerName(){
		return either(self::$qs[0], 'Index');
	}

	public static function isAPI(){
		return either(self::$api, self::$qs[0] == 'API');
	}
	
	public static function init(){
		self::$qs = explode('/', $_GET['q']);
		
		if(self::isAPI()){
			self::$api = self::apiVersion();
			self::$qs = array_slice(self::$qs, 2);
		}
	}
}

?>