<?php
// Carregando Template Site
use Hcode\Page;
use Hcode\Model\Category;
use Hcode\Model\Product;
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
	$products = Product::listAll ();
	$page = new Page ();

	$page->setTpl ( "index", [ 
			'products' => Product::checklist ( $products ) // Aula 112
	] );
} );
// Rota Categorias Rodapé Site Principal Aula 110
$app->get ( "/categories/:idcategory", function ($idcategory) {
	$category = new Category ();
	$category->get ( ( int ) $idcategory );
	$page = new Page ();
	$page->setTpl ( "category", [ 
			'category' => $category->getValues (),
			'products' => Product::checkList ( $category->getProducts () )
	] );
} );