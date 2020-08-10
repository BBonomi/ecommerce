<?php
// Carregando Template Site
use Hcode\Page;
use Hcode\Model\Address;
use Hcode\Model\Cart;
use Hcode\Model\Category;
use Hcode\Model\Order;
use Hcode\Model\OrderStatus;
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
// Rota Finalizar Compra Aula 119 (Alterado Aula 123)
$app->get ( "/checkout", function () {

	User::verifyLogin ( false );

	$address = new Address ();

	$cart = Cart::getFromSession ();

	if (isset ( $_GET ['zipcode'] )) {

		$_GET ['zipcode'] = $cart->getdeszipcode ();
	}

	if (isset ( $_GET ['zipcode'] )) {

		$address->loadFromCEP ( $_GET ['zipcode'] );

		$cart->setdeszipcode ( $_GET ['zipcode'] );

		$cart->save ();

		$cart->getCalculateTotal ();
	}

	if (! $address->getdesaddress ())
		$address->setdesaddress ( '' );

	if (! $address->getdesnumber ())
		$address->setdesnumber ( '' );

	if (! $address->getdescomplement ())
		$address->setdescomplement ( '' );

	if (! $address->getdesdistrict ())
		$address->setdesdistrict ( '' );

	if (! $address->getdescity ())
		$address->setdescity ( '' );

	if (! $address->getdesstate ())
		$address->setdesstate ( '' );

	if (! $address->getdescountry ())
		$address->setdescountry ( '' );

	if (! $address->getdeszipcode ())
		$address->setdeszipcode ( '' );

	$page = new Page ();
	$page->setTpl ( "checkout", [ 
			'cart' => $cart->getValues (),
			'address' => $address->getValues (),
			'products' => $cart->getProducts (),
			'error' => Address::getMsgError ()
	] );
} );
// Rota Post Aula 123 WebService de CEP
$app->post ( "/checkout", function () {

	User::verifyLogin ( false );

	if (! isset ( $_POST ['zipcode'] ) || $_POST ['zipcode'] === '') {

		Address::setMsgError ( "Informe o CEP." );

		header ( 'Location: /checkout' );

		exit ();
	}

	if (! isset ( $_POST ['desaddress'] ) || $_POST ['desaddress'] === '') {

		Address::setMsgError ( "Informe o endereço." );

		header ( 'Location: /checkout' );

		exit ();
	}

	if (! isset ( $_POST ['desdistrict'] ) || $_POST ['desdistrict'] === '') {

		Address::setMsgError ( "Informe o bairro." );

		header ( 'Location: /checkout' );

		exit ();
	}

	if (! isset ( $_POST ['descity'] ) || $_POST ['descity'] === '') {

		Address::setMsgError ( "Informe a cidade." );

		header ( 'Location: /checkout' );

		exit ();
	}

	if (! isset ( $_POST ['desstate'] ) || $_POST ['desstate'] === '') {

		Address::setMsgError ( "Informe o estado." );

		header ( 'Location: /checkout' );

		exit ();
	}

	if (! isset ( $_POST ['descountry'] ) || $_POST ['descountry'] === '') {

		Address::setMsgError ( "Informe o país." );

		header ( 'Location: /checkout' );

		exit ();
	}

	$user = User::getFromSession ();

	$address = new Address ();

	$_POST ['deszipcode'] = $_POST ['zipcode'];

	$_POST ['idperson'] = $user->getidperson ();

	$address->setData ( $_POST );

	$address->save ();

	$cart = Cart::getFromSession ();

	$cart->getCalculateTotal (); // Corrigido Aula 125

	$order = new Order ();

	$order->setData ( [ 

			'idcart' => $cart->getidcart (),

			'idaddress' => $address->getidaddress (),

			'iduser' => $user->getiduser (),

			'idstatus' => OrderStatus::EM_ABERTO,

			'vltotal' => $cart->getvltotal () // Corrigido Total do Carrinho Aula 125 6:49
	] );

	$order->save ();

	header ( "Location: /order/" . $order->getidorder () );

	exit ();
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

// Rota Profile Usuario Aula 122
$app->get ( "/profile", function () {
	User::verifyLogin ( false );
	$user = User::getFromSession ();
	$page = new Page ();
	$page->setTpl ( "profile", [ 
			'user' => $user->getValues (),
			'profileMsg' => User::getSuccess (),
			'profileError' => User::getError ()
	] );
} );
// Rota Post Profile Usuario Aula 122
$app->post ( "/profile", function () {
	User::verifyLogin ( false );
	if (! isset ( $_POST ['desperson'] ) || $_POST ['desperson'] === '') {

		User::setError ( "Preencha o seu nome." );

		header ( 'Location: /profile' );

		exit ();
	}

	if (! isset ( $_POST ['desemail'] ) || $_POST ['desemail'] === '') {

		User::setError ( "Preencha o seu e-mail." );

		header ( 'Location: /profile' );

		exit ();
	}

	$user = User::getFromSession ();

	if ($_POST ['desemail'] !== $user->getdesemail ()) {

		if (User::checkLoginExists ( $_POST ['desemail'] ) === true) {

			User::setError ( "Este endereço de e-mail já está cadastrado." );

			header ( 'Location: /profile' );

			exit ();
		}
	}

	$_POST ['iduser'] = $user->getiduser ();
	$_POST ['inadmin'] = $user->getinadmin ();
	$_POST ['despassword'] = $user->getdespassword ();
	$_POST ['deslogin'] = $_POST ['desemail'];

	$user->setData ( $_POST );

	$user->update ();

	User::setSuccess ( "Dados alterados com sucesso!" );

	header ( 'Location: /profile' );
	exit ();
} );
// Rota Pagamento Aula 124
$app->get ( "/order/:idorder", function ($idorder) {

	User::verifyLogin ( false );

	$order = new Order ();

	$order->get ( ( int ) $idorder );
	// var_dump ( $order->getValues () );
	// exit ();

	$page = new Page ();

	$page->setTpl ( "payment", [ 

			'order' => $order->getValues ()
	] );
} );
// Rota Boleto Itau Aula 124 23:00
$app->get ( "/boleto/:idorder", function ($idorder) {

	User::verifyLogin ( false );

	$order = new Order ();

	$order->get ( ( int ) $idorder );

	// DADOS DO BOLETO PARA O SEU CLIENTE

	$dias_de_prazo_para_pagamento = 10;

	$taxa_boleto = 5.00;

	$data_venc = date ( "d/m/Y", time () + ($dias_de_prazo_para_pagamento * 86400) ); // Prazo de X dias OU informe data: "13/04/2006";

	$valor_cobrado = formatPrice ( $order->getvltotal () ); // Valor - REGRA: Sem pontos na milhar e tanto faz com "." ou "," ou com 1 ou 2 ou sem casa decimal

	$valor_cobrado = str_replace ( ".", "", $valor_cobrado );
	$valor_cobrado = str_replace ( ",", ".", $valor_cobrado );

	$valor_boleto = number_format ( $valor_cobrado + $taxa_boleto, 2, ',', '' );

	$dadosboleto ["nosso_numero"] = $order->getidorder (); // Nosso numero - REGRA: Máximo de 8 caracteres!

	$dadosboleto ["numero_documento"] = $order->getidorder (); // Num do pedido ou nosso numero

	$dadosboleto ["data_vencimento"] = $data_venc; // Data de Vencimento do Boleto - REGRA: Formato DD/MM/AAAA

	$dadosboleto ["data_documento"] = date ( "d/m/Y" ); // Data de emissão do Boleto

	$dadosboleto ["data_processamento"] = date ( "d/m/Y" ); // Data de processamento do boleto (opcional)

	$dadosboleto ["valor_boleto"] = $valor_boleto; // Valor do Boleto - REGRA: Com vírgula e sempre com duas casas depois da virgula

	// DADOS DO SEU CLIENTE

	$dadosboleto ["sacado"] = $order->getdesperson ();

	$dadosboleto ["endereco1"] = $order->getdesaddress () . " " . $order->getdesdistrict ();

	$dadosboleto ["endereco2"] = $order->getdescity () . " - " . $order->getdesstate () . " - " . $order->getdescountry () . " -  CEP: " . $order->getdeszipcode ();

	// INFORMACOES PARA O CLIENTE

	$dadosboleto ["demonstrativo1"] = "Pagamento de Compra na Loja Hcode E-commerce";

	$dadosboleto ["demonstrativo2"] = "Taxa bancária - R$ 0,00";

	$dadosboleto ["demonstrativo3"] = "";

	$dadosboleto ["instrucoes1"] = "- Sr. Caixa, cobrar multa de 2% após o vencimento";

	$dadosboleto ["instrucoes2"] = "- Receber até 10 dias após o vencimento";

	$dadosboleto ["instrucoes3"] = "- Em caso de dúvidas entre em contato conosco: suporte@hcode.com.br";

	$dadosboleto ["instrucoes4"] = "&nbsp; Emitido pelo sistema Projeto Loja Hcode E-commerce - www.hcode.com.br";

	// DADOS OPCIONAIS DE ACORDO COM O BANCO OU CLIENTE

	$dadosboleto ["quantidade"] = "";

	$dadosboleto ["valor_unitario"] = "";

	$dadosboleto ["aceite"] = "";

	$dadosboleto ["especie"] = "R$";

	$dadosboleto ["especie_doc"] = "";

	// ---------------------- DADOS FIXOS DE CONFIGURAÇÃO DO SEU BOLETO --------------- //

	// DADOS DA SUA CONTA - ITAÚ

	$dadosboleto ["agencia"] = "1690"; // Num da agencia, sem digito

	$dadosboleto ["conta"] = "48781"; // Num da conta, sem digito

	$dadosboleto ["conta_dv"] = "2"; // Digito do Num da conta

	// DADOS PERSONALIZADOS - ITAÚ

	$dadosboleto ["carteira"] = "175"; // Código da Carteira: pode ser 175, 174, 104, 109, 178, ou 157

	// SEUS DADOS

	$dadosboleto ["identificacao"] = "Hcode Treinamentos";

	$dadosboleto ["cpf_cnpj"] = "24.700.731/0001-08";

	$dadosboleto ["endereco"] = "Rua Ademar Saraiva Leão, 234 - Alvarenga, 09853-120";

	$dadosboleto ["cidade_uf"] = "São Bernardo do Campo - SP";

	$dadosboleto ["cedente"] = "HCODE TREINAMENTOS LTDA - ME";

	// NÃO ALTERAR!

	$path = $_SERVER ['DOCUMENT_ROOT'] . DIRECTORY_SEPARATOR . "res" . DIRECTORY_SEPARATOR . "boletophp" . DIRECTORY_SEPARATOR . "include" . DIRECTORY_SEPARATOR;

	require_once ($path . "funcoes_itau.php");

	require_once ($path . "layout_itau.php");
} );

// Rota Meus Pedidos Aula 125
$app->get ( "/profile/orders", function () {

	User::verifyLogin ( false );

	$user = User::getFromSession ();

	$page = new Page ();

	$page->setTpl ( "profile-orders", [ 

			'orders' => $user->getOrders ()
	] );
} );
$app->get ( "/profile/orders/:idorder", function ($idorder) {

	User::verifyLogin ( false );

	$order = new Order ();

	$order->get ( ( int ) $idorder );

	$cart = new Cart ();

	$cart->get ( ( int ) $order->getidcart () );

	$cart->getCalculateTotal ();

	$page = new Page ();

	$page->setTpl ( "profile-orders-detail", [ 

			'order' => $order->getValues (),

			'cart' => $cart->getValues (),

			'products' => $cart->getProducts ()
	] );
} );
// Rota Alterar Senha(Usuario) Aula 126
$app->get ( "/profile/change-password", function () {

	User::verifyLogin ( false );

	$page = new Page ();

	$page->setTpl ( "profile-change-password", [ 

			'changePassError' => User::getError (),

			'changePassSuccess' => User::getSuccess ()
	] );
} );
// Rota Formulario Senha Atual, Nova Senha Aula 126 3:37
$app->post ( "/profile/change-password", function () {

	User::verifyLogin ( false );

	if (! isset ( $_POST ['current_pass'] ) || $_POST ['current_pass'] === '') {

		User::setError ( "Digite a senha atual." );

		header ( "Location: /profile/change-password" );

		exit ();
	}

	if (! isset ( $_POST ['new_pass'] ) || $_POST ['new_pass'] === '') {

		User::setError ( "Digite a nova senha." );

		header ( "Location: /profile/change-password" );

		exit ();
	}

	if (! isset ( $_POST ['new_pass_confirm'] ) || $_POST ['new_pass_confirm'] === '') {

		User::setError ( "Confirme a nova senha." );

		header ( "Location: /profile/change-password" );

		exit ();
	}

	if ($_POST ['current_pass'] === $_POST ['new_pass']) {

		User::setError ( "A sua nova senha deve ser diferente da atual." );

		header ( "Location: /profile/change-password" );

		exit ();
	}

	$user = User::getFromSession ();

	if (! password_verify ( $_POST ['current_pass'], $user->getdespassword () )) {

		User::setError ( "A senha está inválida." );

		header ( "Location: /profile/change-password" );

		exit ();
	}

	$user->setdespassword ( $_POST ['new_pass'] );

	$user->update ();

	User::setSuccess ( "Senha alterada com sucesso." );

	header ( "Location: /profile/change-password" );

	exit ();
} );
		