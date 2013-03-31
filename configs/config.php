<?php
$config = array(
			'defaultController' => 'main',
			'defaultAction' => 'index',
		
			'components' => array(					
						'request' => 'BaseRequest',
						'router'  => 'BaseRouter',
					),
			
			'sql' => array(
						'address' => 'localhost',
						'dbName' => 'rodent',
						'username' => 'root',
						'password' => '123',
						'persist' => false				
					)
		);
