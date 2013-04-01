<?php
class Controller extends BaseController
{
	protected $types  = array(
			'default' => 'json',
			'xml'
		);
	
	protected function sendError($message, $type){
		$data = $this->formatData(array('error' => $message),$type);
		$this->render($this->action, array('data' => $data));
	}
	
	public function formatData($data, $type)
	{
		switch($type){
			case 'xml':
				$xml = Array2XML::createXML($this->action . 's', $data);
				$data = $xml->saveXML();
				break;
			default:
				$data = json_encode($data);
		}
	
		return $data;
	}
}