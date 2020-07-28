<?php
session_start (); // iniciando a sessão
require_once ("vendor/autoload.php");
use Hcode\Page;
use Hcode\PageAdmin;
use Hcode\Model\User;
use Slim\Slim;

$app = new Slim ();

$app->config ( 'debug', true );

/*
 * Teste Banco de Dados
 * $app->get ( '/', function () {
 *
 * $sql = new Hcode\DB\Sql ();
 *
 * $results = $sql->select ( "SELECT *FROM tb_users" );
 *
 * echo json_encode ( $results );
 * } );
 */
// Carregando Template Site
$app->get ( '/', function () {

	$page = new Page ();

	$page->setTpl ( "index" );
} );
// Template Admin
$app->get ( '/admin', function () {
	// Metodo estatico validando se esta logado
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

// Rota todos os Usuarios aula 107
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
// Metodo delete Usuario Aula 107
$app->get ( "/admin/users/:iduser/delete", function ($iduser) {
	User::verifyLogin ();
} );
// Rota Update Usuarios aula 107
$app->get ( '/admin/users/:iduser', function ($iduser) {

	User::verifyLogin ();

	// $user = new User ();

	// $user->get ( ( int ) $iduser );

	$page = new PageAdmin ();

	$page->setTpl ( "users-update" ); // , array ("user" => $user->getValues ()));
} );
// Metodo post Create Usuario Aula 107
$app->post ( "/admin/users/create", function () {
	User::verifyLogin ();
} );
// Metodo post Update Usuario Aula 107
$app->post ( "/admin/users/:iduser", function ($iduser) {
	User::verifyLogin ();
} );

$app->run ();

?>