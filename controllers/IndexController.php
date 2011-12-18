<?

class IndexController extends Controller {
	
	public function index(){
		$this->loadView(new View('Index/index'), 'content');
	}

}

?>