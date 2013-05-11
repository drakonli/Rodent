<?php
$config = array(
			'defaultController' => 'get',
			'defaultAction' => 'index',
			'defaultLayout' => 'layout',
		
			'components' => array(				
						'request' => 'BaseRequest',
						'router'  => 'BaseRouter',
						'user'	  => 'BaseUser'
					),
			
			'sql' => array(
						'address' => 'localhost',
						'dbName' => 'rodent',
						'username' => 'root',
						'password' => '123',
						'persist' => false				
					),
		
			'appSecretKey' => 'd79e907d95e0e948bfeed537635cca66558671a757742855c534fb66af8a08428d591150'
		);
