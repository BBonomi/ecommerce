<?php
// Carregando Template Site
use Hcode\Page;
use Hcode\Model\Address;
use Hcode\Model\Cart;
use Hcode\Model\Category;
use Hcode\Model\Product;
use Hcode\Model\User;
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
// Rota Finalizar Compra Aula 119
$app->get ( "/checkout", function () {
	User::verifyLogin ( false );
	$cart = Cart::getFromSession ();
	$address = new Address ();
	$page = new Page ();
	$page->setTpl ( "checkout", [ 
			'cart' => $cart->getValues (),
			'address' => $address->getValues ()
	] );
} );
// Rota Login Usuario Aula 119
$app->get ( "/login", function () {
	$page = new Page ();
	$page->setTpl ( "login", [ 
			'error' => User::getError (),
			'errorRegister' => User::getErrorRegister (), // Adicionado aula 120
			'registerValues' => (isset ( $_SESSION ['registerValues'] )) ? $_SESSION ['registerValues'] : [ 
					'name' => '',
					'email' => '',
					'phone' => ''
			] // Adicionado aula 120
	] );
} );
// Rota Verificando Login via post Aula 119
$app->post ( "/login", function () {
	try {
		User::login ( $_POST ['login'], $_POST ['password'] );
	} catch ( Exception $e ) {
		User::setError ( $e->getMessage () );
	}
	header ( "Location: /checkout" );
	exit ();
} );
// Rota Logout Aula 119 18:01
$app->get ( "/logout", function () {
	User::logout ();
	header ( "Location: /login" );
	exit ();
} );
// Rota de Registro novo Usuario Aula 120
$app->post ( "/register", function () {

	$_SESSION ['registerValues'] = $_POST;

	if (! isset ( $_POST ["name"] ) || $_POST ['name'] == '') {
		User::setErrorRegister ( "Preencha o seu nome" );
		header ( "Location: /login" );
		exit ();
	}

	if (! isset ( $_POST ["name"] ) || $_POST ['email'] == '') {
		User::setErrorRegister ( "Preencha o seu e-mail" );
		header ( "Location: /login" );
		exit ();
	}

	if (! isset ( $_POST ["name"] ) || $_POST ['password'] == '') {
		User::setErrorRegister ( "Preencha a senha" );
		header ( "Location: /login" );
		exit ();
	}

	if (User::checkLoginExist ( $_POST ['email'] ) === true) {

		User::setErrorRegister ( "Este endereço de e-mail já está sendo usado por outro usuário" );
		header ( "Location: /login" );
		exit ();
	}

	$user = new User ();

	$user->setData ( [ 
			'inadmin' => 0,
			'deslogin' => $_POST ["email"],
			'desperson' => $_POST ["name"],
			'desemail' => $_POST ["email"],
			'despassword' => $_POST ["password"],
			'nrphone' => $_POST ["phone"]
	] );

	$user->save ();

	User::login ( $_POST ['email'], $_POST ["password"] );

	header ( 'Location: /checkout' );
	exit ();
} );
// Rota Esqueceu a Senha Usuario Aula 121
$app->get ( "/forgot", function () {

	$page = new Page ();

	$page->setTpl ( "forgot" );
} );

$app->post ( "/forgot", function () {

	$user = User::getForgot ( $_POST ["email"], false );

	header ( "Location: /forgot/sent" );

	exit ();
} );

$app->get ( "/forgot/sent", function () {

	$page = new Page ();

	$page->setTpl ( "forgot-sent" );
} );

$app->get ( "/forgot/reset", function () {

	$user = User::validForgotDecrypt ( $_GET ["code"] );

	$page = new Page ();

	$page->setTpl ( "forgot-reset", array (

			"name" => $user ["desperson"],

			"code" => $_GET ["code"]
	) );
} );

$app->post ( "/forgot/reset", function () {

	$forgot = User::validForgotDecrypt ( $_POST ["code"] );

	User::setFogotUsed ( $forgot ["idrecovery"] );

	$user = new User ();

	$user->get ( ( int ) $forgot ["iduser"] );

	$password = User::getPasswordHash ( $_POST ["password"] );

	$user->setPassword ( $password );

	$page = new Page ();

	$page->setTpl ( "forgot-reset-success" );
} );
		
		