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

$app->run ();

?>