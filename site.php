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
	$page = (isset ( $_GET ['page'] )) ? ( int ) $_GET ['page'] : 1; // Adicionado aula 114 Paginação
	$category = new Category ();
	$category->get ( ( int ) $idcategory );
	$pagination = $category->getProductsPage ( $page ); // Adicionado aula 114 Paginação 9:55
	$pages = [ ]; // Adicionado aula 114 Paginação 11:48
	for($i = 1; $i <= $pagination ['pages']; $i ++) {
		array_push ( $pages, [ 
				'link' => '/categories/' . $category->getidcategory () . '?page=' . $i,
				'page' => $i
		] );
	}

	$page = new Page ();

	$page->setTpl ( "category", [ 
			'category' => $category->getValues (),
			'products' => $pagination ["data"], // Alterado aula 114

			'pages' => $pages
	] );
} );
// Rota Detalhes do Produto Aula 115
$app->get ( "/products/:desurl", function ($desurl) {
	$product = new Product ();
	$product->getFromURL ( $desurl );
	$page = new Page ();
	$page->setTpl ( "product-detail", [ 
			'product' => $product->getValues (),
			'categories' => $product->getCategories ()
	] );
} );