<?php
$config = array(
			'defaultController' => 'get',
			'defaultAction' => 'index',
			'defaultLayout' => 'layout',
		
			'components' => array(					
						'request' => 'BaseRequest',
						'router'  => 'BaseRouter',
					),
			
			'sql' => array(
						'address' => 'localhost',
						'dbName' => 'rodent',
						'username' => 'root',
						'password' => 'stolker',
						'persist' => false				
					)
		);
