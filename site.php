<?php
// Carregando Template Site
use Hcode\Page;
use Hcode\Model\Cart;
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
// Rota Carrinho Aula 116
$app->get ( "/cart", function () {
	$cart = Cart::getFromSession ();
	$page = new Page ();
	// var_dump ( $cart->getValues () );
	// exit ();

	$page->setTpl ( "cart", [ 
			'cart' => $cart->getValues (),
			'products' => $cart->getProducts (),
			'error' => Cart::getMsgError () // Aula 118 28:47
	] );
} );
// Rota Adicionar Produto no Carrinho Aula 117
$app->get ( "/cart/:idproduct/add", function ($idproduct) {

	$product = new Product ();

	$product->get ( ( int ) $idproduct );

	$cart = Cart::getFromSession ();

	$qtd = (isset ( $_GET ['qtd'] )) ? ( int ) $_GET ['qtd'] : 1;

	for($i = 0; $i < $qtd; $i ++) {

		$cart->addProduct ( $product );
	}

	header ( "Location: /cart" );

	exit ();
} );
// Rota Removendo 1 Produto do Carrinho Aula 117
$app->get ( "/cart/:idproduct/minus", function ($idproduct) {

	$product = new Product ();

	$product->get ( ( int ) $idproduct );

	$cart = Cart::getFromSession ();

	$cart->removeProduct ( $product );

	header ( "Location: /cart" );

	exit ();
} );
// Rota Remove todos os produtos do carrinho Aula 117
$app->get ( "/cart/:idproduct/remove", function ($idproduct) {

	$product = new Product ();

	$product->get ( ( int ) $idproduct );

	$cart = Cart::getFromSession ();

	$cart->removeProduct ( $product, true );

	header ( "Location: /cart" );

	exit ();
} );
// Rota CEP Aula 118
$app->post ( "/cart/freight", function () {

	$cart = Cart::getFromSession ();

	$cart->setFreight ( $_POST ['zipcode'] );

	header ( "Location: /cart" );

	exit ();
} );
		
		