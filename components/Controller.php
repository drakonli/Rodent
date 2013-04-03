<?php
class Controller extends BaseController
{
	protected $types  = array(
			'default' => 'json',
			'xml'
		);
	protected $type;
	
	protected function sendError($message){
		$type = isset($this->type) ? $this->type : $this->types['default'];

		$data = $this->formatData(array('error' => $message),$type);
		$this->render($this->action, array('data' => $data));
	}

	public function formatData($data, $type)
	{
		switch($type){
			case 'xml':
				$xml  = Array2XML::createXML($this->action . 's', $data);
				$data = $xml->saveXML();
				break;
			case 'json':
				$data = json_encode($data);
				break;
			default:
				$data = null;
		}
	
		return $data;
	}
}