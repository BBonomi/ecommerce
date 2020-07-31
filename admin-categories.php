<?php
// Rota Criar Categorias /Admin
use Hcode\Page;
use Hcode\PageAdmin;
use Hcode\Model\Category;
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
// Rota Categorias Rodapé Site Principal Aula 110
$app->get ( "/categories/:idcategory", function ($idcategory) {
	$category = new Category ();
	$category->get ( ( int ) $idcategory );
	$page = new Page ();
	$page->setTpl ( "category", [ 
			'category' => $category->getValues (),
			'products' => [ ]
	] );
} );