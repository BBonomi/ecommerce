<?php
// Rota todos os Usuarios aula 107
use Hcode\PageAdmin;
use Hcode\Model\User;

$app->get ( "/admin/users", function () {
	User::verifyLogin ();
	$users = User::listAll ();
	$page = new PageAdmin ();
	$page->setTpl ( "users", array (
			"users" => $users
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