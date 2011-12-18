<?

class APIController {

	const TOKEN_SALT = "sUmPVhQAEGYNq3FJZQ7KQUKvmWITLs1A7nuH1a2E";

	public $slug;
	public $url;
	public $version;

	// Execution
	protected $autoRender;
	protected $defaultToIndex;
	protected $isAjax;
	protected $isHTTP;
	protected $name;
	protected $useDataStream;

	// Path
	protected $action;
	protected $args;
	protected $extension;

	// Data
	public $delete;
	public $file;
	public $get;
	public $put;
	public $post;
	
	// View
	protected $layout;

	// Response
	public $meta;
	public $response;
	
	// Status
	protected $message;
	protected $status;
	
	public function __construct(array $args) {
		$delete = &$args['delete'];
		$files = &$args['files'];
		$get = &$args['get'];
		$post = &$args['post'];
		$put = &$args['put'];
		unset($args['delete'], $args['files'], $args['get'], $args['post'], $args['put']);
		
		// Only use the data stream if the PUT and DELETE variables haven't been explicitly set
		$this->useDataStream = (bool) empty($put) && empty($delete);
		
		$this->args = array();
		
		$this->delete = ( $delete ? new RequestArray($delete) : new RequestArray() );
		$this->files = ( $files ? new RequestArray($files) : new RequestArray($_FILES) );
		$this->get = ( $get ? new RequestArray($get) : new RequestArray($_GET) );
		$this->post = ( $post ? new RequestArray($post) : new RequestArray($_POST) );
		$this->put = ( $put ? new RequestArray($put) : new RequestArray() );
		
		$this->layout = ( $this->get['callback'] ? 'JSONP' : ( $this->get['json_pretty_print'] ? 'JSON_pretty' : 'JSON' ) );
		
		$this->slug = $this->type;
		
		$this->isHttp = true;
		
		$this->meta = array();
		$this->response = array();
		
		$this->message = "OK";
		$this->status = 200;
		
		// Assign the rest of the constructor values to the appropriate properties
		foreach($args as $key => $value){ if(property_exists($this, $key)) $this->$key = $value; }
	}
	
	public function afterFilter(){ }
	
	public function afterRender(){ }
	
	public function beforeFilter(){
		// Auth level of an unauthenticated user is 1
		self::login($this->get['auth_id'], $this->get['auth_token']) + 1;

		if(!isset($this->get['num'])) $this->get['num'] = 10;
		if(!isset($this->get['offset'])) $this->get['offset'] = 0;
		if(isset($this->args[0])) $this->get['type'] = 'resource';
	}
	
	public function beforeRender(){
		$this->meta = array_merge(array(
			'message' => $this->message,
			'status' => $this->status,
			'time' => time(),
			'auth_id' => Session::$user['id'],
			'auth_token' => self::authToken(Session::$user['id']),
		), $this->meta);
		
		if($this->response === array()){
			$this->response = new stdClass();
		}
		
		$this->data = array(
			'meta' => &$this->meta,
			'response' => &$this->response,
		);
		
		if($this->get['cast_to_string']){
			array_walk_recursive($this->meta, function(&$val){ $val = (string)$val; });
			array_walk_recursive($this->response, function(&$val){ $val = (string)$val; });
		}
	}

	public function filter($action, array $args = array()){
		$this->action = $action;
		$this->args = ( $args ? $args : array() );
		$this->extension = "json";
		
		$this->url = URL::create($this->slug, null, $this->args);
		$this->fullURL = URL::create($this->slug, null, $this->args, $this->get->toArray());
		
		// Remove empty values from the args array
		foreach($this->args as $key=>$arg){
			if(empty($arg) || $arg == '') unset($this->args[$key]);
		}
		
		if(!in_array(strtolower($this->action), array('post', 'get', 'put', 'delete'))){
			throw new RESTException("Invalid API verb: {$this->action}. Choose either 'POST', 'GET', 'PUT', 'DELETE'");
		}
		
		// Return a 404 for methods that don't exist
		if(!method_exists($this, $this->action)){
			$this->status = 404;
			$this->message = "Method '{$this->action}' not supported on this resource";
		}
		
		// Hack PHP into supporting DELETE and PUT verbs
		// (Merge in the POST array because clients that don't support
		//  all of the RESTful verbs are going to POST to use PUT)		
		if($this->useDataStream && in_array(strtolower($this->action), array('delete', 'put'))){
			$data = array();
			parse_str(file_get_contents('php://input'), $data);
			$this->$action->update(array_merge($this->post->toArray(), $data));
		}
		
		if($this->beforeFilter() === false) return;
		call_user_method_array($this->action, $this, $this->args);
		if($this->afterFilter() === false) return;
		
		$this->render();
	}
	
	public function render(){
		$this->beforeRender();
		
		if($this->isHttp){
			// Discard any output buffers
			while(@ob_end_clean());

			if($this->get['suppress_response_status']){
				header("{$_SERVER['SERVER_PROTOCOL']} 200 OK");
			} else {
				header("{$_SERVER['SERVER_PROTOCOL']} {$this->status} {$this->message}");
			}

			header('Content-Type: ' . ( $this->get['content_type'] ? $this->get['content_type'] : 'application/json' ));
			include "views/{$this->layout}.php";
		} else {
			include "views/{$this->layout}.php";
		}
		
		$this->afterRender();
	}

	public static function create(array $args){
		$controllerName = "{$args['controller']}APIController";
		$controllerFile = "controllers/$controllerName.php";

		if(file_exists($controllerFile))
			require_once $controllerFile;

		if(class_exists($controllerName, false)){
			return new $controllerName($args);
		} else {
			throw new Exception("Undefined controller - $controllerName");
		}
	}
	
	public static function authToken($userID){
		if(!$userID) return null; 
		return md5("User::{$userID}_" . self::TOKEN_SALT);
	}
	
	public static function login($userID = null, $token = null){
		if($userID && $token == self::authToken($userID)){
			return Session::login($userID);
		}
		
		return false;
	}
}

class RESTException extends Exception {}

?>