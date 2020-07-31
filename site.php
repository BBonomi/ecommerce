<?php
// Carregando Template Site
use Hcode\Page;
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
$app->get ( '/', function () {

	$page = new Page ();

	$page->setTpl ( "index" );
} );