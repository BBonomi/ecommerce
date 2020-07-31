<?php
session_start (); // iniciando a sessão
require_once ("vendor/autoload.php");
use Hcode\Page;
use Hcode\PageAdmin;
use Hcode\Model\Category;
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

// Rota de Categorias /Admin
$app->get ( "/admin/categories", function () {

	User::verifyLogin ();
	$categories = Category::listAll ();

	$page = new PageAdmin ();
	$page->setTpl ( "categories", [ 
			'categories' => $categories
	] );
} );

// Rota Criar Categorias /Admin
$app->get ( "/admin/categories/create", function () {
	User::verifyLogin ();
	$page = new PageAdmin ();
	$page->setTpl ( "categories-create" );
} );
// Rota Criar Categorias POST /Admin
$app->post ( "/admin/categories/create", function () {
	User::verifyLogin ();
	$category = new Category ();
	$category->setData ( $_POST );
	$category->save ();
	header ( 'Location: /admin/categories' );
	exit ();
} );

// Rota Deletar Categoria
$app->get ( "/admin/categories/:idcategory/delete", function ($idcategory) {
	User::verifyLogin ();
	$category = new Category ();
	$category->get ( ( int ) $idcategory );
	$category->delete ();
	header ( 'Location: /admin/categories' );
	exit ();
} );

// Rota Editar Categoria /ADMIN
$app->get ( "/admin/categories/:idcategory", function ($idcategory) {
	User::verifyLogin ();
	$category = new Category ();
	$category->get ( ( int ) $idcategory );
	$page = new PageAdmin ();
	$page->setTpl ( "categories-update", [ 
			'category' => $category->getValues ()
	] );
} );

// Rota Editar Categoria POST
$app->post ( "/admin/categories/:idcategory", function ($idcategory) {
	User::verifyLogin ();
	$category = new Category ();
	$category->get ( ( int ) $idcategory );
	$category->setData ( $_POST );
	$category->save ();
	header ( 'Location: /admin/categories' );
	exit ();
} );

$app->run ();

?>