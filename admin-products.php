<?php
use Hcode\PageAdmin;
use Hcode\Model\Product;
use Hcode\Model\User;
// Rota Produtos /Admin Aula 111
$app->get ( "/admin/products", function () {
	User::verifyLogin ();
	$products = Product::listAll ();
	$page = new PageAdmin ();
	$page->setTpl ( "products", [ 
			"products" => $products
	] );
} );
// Rota Pagina Criar Produtos /Admin Aula 111
$app->get ( "/admin/products/create", function () {
	User::verifyLogin ();
	$page = new PageAdmin ();
	$page->setTpl ( "products-create" );
} );
// Rota Criar Produtos /Admin Aula 111
$app->post ( "/admin/products/create", function () {
	User::verifyLogin ();
	$product = new Product ();
	$product->setData ( $_POST );
	$product->save ();
	header ( "Location: /admin/products" );
	exit ();
} );
// Rota Editar Produtos Aula 111
$app->get ( "/admin/products/:idproduct", function ($idproduct) {
	User::verifyLogin ();
	$product = new Product ();
	$product->get ( ( int ) $idproduct );
	$page = new PageAdmin ();
	$page->setTpl ( "products-update", [ 
			'product' => $product->getValues ()
	] );
} );
// Rota Editar Produtos POST Aula 111
$app->post ( "/admin/products/:idproduct", function ($idproduct) {
	User::verifyLogin ();
	$product = new Product ();
	$product->get ( ( int ) $idproduct );
	$product->setData ( $_POST );
	$product->sabe ();
	$product->setPhoto ( $_FILES ["file"] );
	header ( 'Location:/admin/products' );
	exit ();
} );

// Rota Delete Aula 111
$app->get ( "/admin/products/:idproduct/delete", function ($idproduct) {
	User::verifyLogin ();
	$product = new Product ();
	$product->get ( ( int ) $idproduct );
	$product->delete ();
	header ( 'Location:/admin/products' );
	exit ();
} );