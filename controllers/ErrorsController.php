<?php
class ErrorsController extends Controller
{
	protected $action = 'index';
	public function indexAction($variables){
		$error = isset($variables['error']) ? $variables['error'] : null;
		
		$this->render('error', array('error' => $error));
	}
}