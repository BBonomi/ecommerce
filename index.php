<?php
require_once ("vendor/autoload.php"); // do composer

use Hcode\Page;
use Slim\Slim;

$app = new Slim ();

$app->config ( 'debug', true );

$app->get ( '/', function () {
	// Classe Page
	$page = new Page ();
	$page->setTpl ( "index" );
	/*
	 * $sql = new Hcode\DB\Sql ();
	 * $results = $sql->select ( "SELECT *FROM tb_users" );
	 * echo json_encode ( $results );
	 */
} );

$app->run ();

?>