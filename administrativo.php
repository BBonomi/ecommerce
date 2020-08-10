<?php
use Hcode\PageAdmin;
use Hcode\Model\User;

// Rota Template Admin
$app->get ( '/admin', function () {

	User::verifyLogin ();

	$page = new PageAdmin ();

	$page->setTpl ( "index" );
} );

// Rota Login (Admin)
$app->get ( '/admin/login', function () {
	$page = new PageAdmin ( [ 
			"header" => false,
			"footer" => false
	] );
	$page->setTpl ( "login" );
} );
// Rota Login Metodo Post arquivo login.html
$app->post ( '/admin/login', function () {
	// Validando Login (método estatico)
	User::login ( $_POST ["login"], $_POST ["password"] );
	// Redirecionando home page administração
	header ( "Location: /admin" );
	exit ();
} );
// Rota Logout
$app->get ( '/admin/logout', function () {
	User::logout ();
	header ( "Location: /admin/login" );
	exit ();
} );

// Rota Esqueci a senha aula 108
$app->get ( "/admin/forgot", function () {
	$page = new PageAdmin ( [ 
			"header" => false,
			"footer" => false
	] );
	$page->setTPL ( "forgot" );
} );
// Rota Esqueci a senha envio formulario metodo post aula 108
$app->post ( "/admin/forgot", function () {

	$user = User::getForgot ( $_POST ["email"] );
	header ( "Location: /admin/forgot/sent" );
	exit ();
} );
// Enviado email recuperação
$app->get ( "/admin/forgot/sent", function () {
	$page = new PageAdmin ( [ 
			"header" => false,
			"footer" => false
	] );
	$page->setTPL ( "forgot-sent" );
} );
// Rota Redefinir Senha
$app->get ( "/admin/forgot/reset", function () {

	$user = User::validForgotDecrypt ( $_GET ["code"] );
	$page = new PageAdmin ( [ 
			"header" => false,
			"footer" => false
	] );
	$page->setTPL ( "forgot-reset", array (
			"name" => $user ["desperson"],
			"code" => $_GET ["code"]
	) );
} );

$app->post ( "/admin/forgot/reset", function () {

	$forgot = User::validForgotDecrypt ( $_POST ["code"] );

	User::setFogotUsed ( $forgot ["idrecovery"] );
	// Trocando a senha do usuario
	$user = new User ();

	$user->get ( ( int ) $forgot ["iduser"] );

	$password = User::getPasswordHash ( $_POST ["password"] );

	$user->setPassword ( $password );

	$page = new PageAdmin ( [ 
			"header" => false,
			"footer" => false
	] );

	$page->setTpl ( "forgot-reset-success" );
} );				