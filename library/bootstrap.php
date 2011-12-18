<?

ini_set('display_errors', 'on');
error_reporting(E_ERROR);

function __autoload($class){
	$path = "library/$class.php";
	
	if(file_exists($path))
		require_once $path;
}

function die_dump(){
	foreach(func_get_args() as $arg){
		var_dump($arg);
	}

	exit;
}

function either(){
	foreach(func_get_args() as $arg){
		if($arg) return $arg;
	}
	
	return $arg;
}

?>