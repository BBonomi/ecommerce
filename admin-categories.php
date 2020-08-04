<?php
// Rota Criar Categorias /Admin
use Hcode\PageAdmin;
use Hcode\Model\Category;
use Hcode\Model\Product;
use Hcode\Model\User;

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

// Rota Aula 113 Produtos x Categorias
$app->get ( "/admin/categories/:idcategory/products", function ($idcategory) {
	User::verifyLogin ();
	$category = new Category ();
	$category->get ( ( int ) $idcategory );
	$page = new PageAdmin ();
	$page->setTpl ( "categories-products", [ 
			'category' => $category->getValues (),
			'productsRelated' => $category->getProducts (),
			'productsNotRelated' => $category->getProducts ( false )
	] );
} );

// Rota Botão Add template admin/categories-products.html Aula 113
$app->get ( "/admin/categories/:idcategory/products/:idproduct/add", function ($idcategory, $idproduct) {
	User::verifyLogin ();
	$category = new Category ();
	$category->get ( ( int ) $idcategory );
	$product = new Product ();
	$product->get ( ( int ) $idproduct );
	$category->addProduct ( $product );

	header ( "Location: /admin/categories/" . $idcategory . "/products" );

	exit ();
} );
// Rota Botão Remove template admin/categories-products.html Aula 113
$app->get ( "/admin/categories/:idcategory/products/:idproduct/remove", function ($idcategory, $idproduct) {

	User::verifyLogin ();
	$category = new Category ();
	$category->get ( ( int ) $idcategory );
	$product = new Product ();
	$product->get ( ( int ) $idproduct );
	$category->removeProduct ( $product );

	header ( "Location: /admin/categories/" . $idcategory . "/products" );

	exit ();
} );
