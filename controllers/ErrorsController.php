<?php
class ErrorsController extends BaseController
{
	public function indexAction($variables){
		$error = ($variables['error']) ? $variables['error'] : null;
		
		$this->render('error', array('error' => $error));
	}
}