<?php
require_once ("vendor/autoload.php");
use Hcode\Page;
use Hcode\PageAdmin;
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

	$page = new PageAdmin ();

	$page->setTpl ( "index" );
} );

$app->run ();

?>