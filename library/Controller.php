<?

class Controller {
	public $slug;
	public $url;

	// Execution
	protected $autoRender;
	protected $defaultToIndex;
	protected $isAjax;
	protected $name;

	// Path
	protected $action;
	protected $args;
	protected $extension;

	// Data
	public $file;
	public $get;
	public $post;
	
	// View
	protected $meta;
	protected $title;
	protected $views;
	
	/**********************************************************************************************/
  	// - 
  	// !Implementation of the Controlable interface
  	// - 
  	/**********************************************************************************************/
	
	public function __construct(array $args) {
		$get = &$args['get'];
		$post = &$args['post'];
		$file = &$args['file'];
		$name = $args['name'];
		$isAjax = $args['isAjax'];
		unset($args);
		
		$this->args = array();
		
		$this->get = ( $get ? ( is_a($get, 'RequestArray') ? $get : new RequestArray($get) ) : new RequestArray($_GET) );
		$this->post = ( $post ? ( is_a($post, 'RequestArray') ? $post : new RequestArray($post) ) : new RequestArray($_POST) );
		$this->file = ( $file ? ( is_a($file, 'RequestArray') ? $file : new RequestArray($file) ) : new RequestArray($_FILE) );
		
		$this->autoRender = true;
		$this->defaultToIndex = true;
		$this->name = ( $name ? $name : get_class($this));
		$this->isAjax = ( isset($isAjax) ? $isAjax : isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' ) || $_GET['ajax'];
		
		// Convert the current controller name to what we'd use in the URL
		$slug = $this->name;
		$slug = str_replace("Controller", "", $slug);
		$this->slug = $slug;
		
		// Path
		$this->meta = array();
		$this->title = "";
		$this->views = array();
	}
	
	public function afterFilter() { }
	
	public function afterRender() {
		flush();
	}
	
	public function beforeFilter(){
		// Wrap controller execution in an output buffer
		ob_start();
	}
	
	public function beforeRender(){ }
	
	public function filter($action = "index", array $args = array()){
		$this->action = ( $action ? $action : "index" );
		$this->args = ( $args ? $args : array() );
		$this->extension = pathinfo($this->args[count($this->args) - 1], PATHINFO_EXTENSION);
		
		$this->url = URL::create($this->slug, ( $this->defaultToIndex && $this->action == 'index' ? null : $this->action ), $this->args);
		$this->fullURL = URL::create($this->slug, ( $this->defaultToIndex && $this->action == 'index' ? null : $this->action ), $this->args, $this->get->toArray());
		
		if($this->extension){
			$this->args[count($this->args) - 1] = str_replace(".{$this->extension}", '', $this->args[count($this->args) - 1]);
		} else {
			$this->extension = "html";
		}
		
		if(!method_exists($this, $this->action)){
			if($this->defaultToIndex && method_exists($this, 'index')){
				$this->action = to_camel_case($this->action);
				array_unshift($this->args, $this->action);
				$this->action = 'index';
				$this->filter($this->action, $this->args);
				return;
			}
			throw new NotFoundException("Undefined method - {$this->name}/{$this->action}");
		}
		
		if($this->beforeFilter() === false) return;
		call_user_method_array($this->action, $this, $this->args);
		$this->afterFilter();
			
		if($this->autoRender) $this->render();
	}
	
	public function render(){
		$meta = array_merge($this->meta, array(
			'description'=>'',
			'author'=>'',
		));
		$title = $this->title;
		$views = $this->views;
		
		Util::execute(function() use ($meta, $title, $views){
			extract($views);
			include 'views/HTML.php';
		});
		
		$this->afterRender();
	}

	public function loadView($view, $outlet){
		$this->views[$outlet][] = $view;
	}
  	
  	public static function create(array $args){
		$controllerName = "{$args['controller']}Controller";
		$controllerFile = "controllers/$controllerName.php";

		if(file_exists($controllerFile))
			require_once $controllerFile;

		if(class_exists($controllerName, false)){
			return new $controllerName($args);
		} else {
			throw new Exception("Undefined controller - $controllerName");
		}
	}
	
	public static function redirect($controller, $action = null, array $args = array(), array $params = array(), $hard = false){
		
		if(stristr($controller, 'http://') || stristr($controller, 'https://')){
			//if page is a URL, then set $url as page
			$url = $controller;
		} else {
			//if page is NOT a URL, then set  generate a $url from page
			$url = URL::create($controller, $action, $args, $params);
		}

		Util::redirect($url, $hard);
		exit;
	}
}

class NotFoundException extends Exception {};

?>