<?php
// Rota todos os Usuarios aula 107
use Hcode\PageAdmin;
use Hcode\Model\User;
// Aula 132 Alterando Senha Admin Users
$app->get ( "/admin/users/:iduser/password", function ($iduser) {

	User::verifyLogin ();

	$user = new User ();

	$user->get ( ( int ) $iduser );

	$page = new PageAdmin ();

	$page->setTpl ( "users-password", [ 

			'msgSuccess' => User::getSuccess (),

			'msgError' => User::getError (),

			'user' => $user->getValues ()
	] );
} );

$app->post ( "/admin/users/:iduser/password", function ($iduser) {

	User::verifyLogin ();

	if (isset ( $_POST ['despassword'] ) && $_POST ['despassword'] === '') {

		User::setError ( "Preencha a nova senha." );

		header ( "Location: /admin/users/$iduser/password" );

		exit ();
	}

	if (isset ( $_POST ['despassword-confirm'] ) && $_POST ['despassword-confirm'] === '') {

		User::setError ( "Preencha a confirmação da nova senha." );

		header ( "Location: /admin/users/$iduser/password" );

		exit ();
	}

	if ($_POST ['despassword'] !== $_POST ['despassword-confirm']) {

		User::setError ( "Confirme a nova senha corretamente." );

		header ( "Location: /admin/users/$iduser/password" );

		exit ();
	}

	$user = new User ();

	$user->get ( ( int ) $iduser );

	$user->setPassword ( User::getPasswordHash ( $_POST ['despassword'] ) );

	User::setSuccess ( "Senha alterada com sucesso." );

	header ( "Location: /admin/users/$iduser/password" );

	exit ();
} );

$app->get ( "/admin/users", function () { // Alterado Aula 128
	User::verifyLogin ();
	$search = (isset ( $_GET ['search'] )) ? $_GET ['search'] : "";
	$page = (isset ( $_GET ['page'] )) ? ( int ) $_GET ['page'] : 1;
	if ($search != '') {

		$pagination = User::getPageSearch ( $search, $page );
	} else {

		$pagination = User::getPage ( $page );
	}

	$pages = [ ];

	for($x = 0; $x < $pagination ['pages']; $x ++) {

		array_push ( $pages, [ 

				'href' => '/admin/users?' . http_build_query ( [ 

						'page' => $x + 1,

						'search' => $search
				] ),

				'text' => $x + 1
		] );
	}

	$page = new PageAdmin ();
	$page->setTpl ( "users", array (
			"users" => $pagination ['data'],
			"search" => $search,
			"pages" => $pages
	) );
} );

// Rota Create Usuarios aula 107
$app->get ( "/admin/users/create", function () {
	User::verifyLogin ();
	$page = new PageAdmin ();
	$page->setTpl ( "users-create" );
} );
// Metodo DELETE Usuario Aula 107
$app->get ( "/admin/users/:iduser/delete", function ($iduser) {
	User::verifyLogin ();
	$user = new User ();
	$user->get ( ( int ) $iduser );
	$user->delete ();
	header ( "Location: /admin/users" );
	exit ();
} );
// Rota Update Usuarios aula 107
$app->get ( '/admin/users/:iduser', function ($iduser) {

	User::verifyLogin ();

	$user = new User ();

	$user->get ( ( int ) $iduser );

	$page = new PageAdmin ();

	$page->setTpl ( "users-update", array (
			"user" => $user->getValues ()
	) );
} );
// Metodo post Create Usuario Aula 107
$app->post ( "/admin/users/create", function () {
	User::verifyLogin ();
	// var_dump ( $_POST ); // verificando a rota
	$user = new User (); // criando novo usuario
	$_POST ["inadmin"] = (isset ( $_POST ["inadmin"] )) ? 1 : 0; // definido 1 - não definido 0
	$user->setData ( $_POST ); // usando metodo setData Model.php
	                           // var_dump ( $user ); // vizualizando a criação do objeto usuario
	$user->save ();
	header ( "Location: /admin/users" );
	exit ();
} );
// Metodo post Update Usuario Aula 107
$app->post ( "/admin/users/:iduser", function ($iduser) {
	User::verifyLogin ();
	$user = new User (); // Video 33:20
	$_POST ["inadmin"] = (isset ( $_POST ["inadmin"] )) ? 1 : 0;
	$user->get ( ( int ) $iduser );
	$user->setData ( $_POST );
	$user->update ();
	header ( "Location: /admin/users" );
	exit ();
} );