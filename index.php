<?

include 'library/bootstrap.php';

Routing::init();

try {
	if(Routing::isAPI()){
		$controller = APIController::create(array(
			'controller' => Routing::controllerName()
		));
	} else {
		$controller = Controller::create(array(
			'controller' => Routing::controllerName()
		));	
	}
} catch (Exception $e){
	Util::redirect('/404.html');
}

$controller->beforeFilter();
$controller->filter(Routing::action(), Routing::args());
$controller->afterFilter();

?>
