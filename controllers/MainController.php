<?php 

class MainController extends BaseController
{
	// это такая сейчас стандратная конструкция контроллеров. роутер перенаправляет на контроллер
	// а Action это отдельный метод, который вызывается для этого контроллера
	// объясняю: контроллер может работать со всеми урлами, допустим, после admin/
	// и будет admin/index admin/bam admin/fuck
	public function indexAction() 
	{	
		$myName = 'Vova';
		$mySurname = 'Vivonaev';
		
		//model
		$modelResults = array('miSHA'=>'stupidfuck','artur'=>'thebest');
	
		$book = new BooksModel();
		$book->id = 8;
		
		$bam = $book->findOne();
		$bam->created = time();
		$bam->save();

		$this->render('mainpage');
	}
}