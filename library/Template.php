<?

class Template {
	
	public static function render($views = array()){
		foreach($views as $view){
			Util::execute(function() use ($view) {
				extract($view->args());
				include $view->path();
			});
		}
	}
}

?>