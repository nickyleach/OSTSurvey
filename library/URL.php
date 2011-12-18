<?

class URL {
	public static function create($controller, $action = null, $args = array(), $params = array(), $extension = null){
		$argsStr = trim(implode('/', $args), '/');
		$paramsStr = http_build_query($params);
	
		$url = "http://" . $_SERVER['SERVER_NAME'] . '/' . ( $controller ? $controller . '/' : '' ) . ( $action ?  $action . '/' : '' ) . ( $argsStr ? $argsStr : '' ) . ( $extension ? ".$extension" : '' ) . ( $paramsStr ? '?' . $paramsStr : '' );
	
		$url = str_replace(' ', '-', $url);
	
		return $url;
	}
}

?>