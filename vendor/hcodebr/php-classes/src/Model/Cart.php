<?php

namespace Hcode\Model;

use Hcode\Model;
use Hcode\DB\Sql;

class Cart extends Model {
	const SESSION = "Cart";
	const SESSION_ERROR = "CartError";
	// Metodo Sessão Aula 116
	public static function getFromSession() {
		$cart = new Cart ();

		if (isset ( $_SESSION [Cart::SESSION] ) && ( int ) $_SESSION [Cart::SESSION] ['idcart'] > 0) {

			$cart->get ( ( int ) $_SESSION [Cart::SESSION] ['idcart'] );
		} else {

			$cart->getFromSessionID ();
			if (! ( int ) $cart->getidcart () > 0) {
				$data = [ 

						'dessessionid' => session_id ()
				];

				if (User::checkLogin ( false )) {

					$user = User::getFromSession ();

					$data ['iduser'] = $user->getiduser ();
				}

				$cart->setData ( $data );

				$cart->save ();

				$cart->setToSession ();
			}
		}

		return $cart;
	}
	// Metodo setToSession aula 116 24:59
	public function setToSession() {
		$_SESSION [Cart::SESSION] = $this->getValues ();
	}

	// Metodo ID Sessão Aula 116 12:20
	public function getFromSessionID() {
		$sql = new Sql ();
		$results = $sql->select ( "SELECT * FROM tb_carts WHERE dessessionid = :dessessionid", [ 
				':dessessionid' => session_id ()
		] );

		if (count ( $results ) > 0) {

			$this->setData ( $results [0] );
		}
	}
	// Metodo para pegar id do carrinho Aula 116
	public function get(int $idcart) {
		$sql = new Sql ();
		$results = $sql->select ( "SELECT * FROM tb_carts WHERE idcart = :idcart", [ 
				':idcart' => $idcart
		] );
		if (count ( $results ) > 0) {
			$this->setData ( $results [0] );
		}
	}
	// Metodo Salvar Carrinho Aula 116
	public function save() {
		$sql = new Sql ();
		$results = $sql->select ( "CALL sp_carts_save(:idcart, :dessessionid, :iduser, :deszipcode, :vlfreight, :nrdays)", [ 
				':idcart' => $this->getidcart (),
				':dessessionid' => $this->getdessessionid (),
				':iduser' => $this->getiduser (),
				':deszipcode' => $this->getdeszipcode (),
				':vlfreight' => $this->getvlfreight (),
				':nrdays' => $this->getnrdays ()
		] );

		$this->setData ( $results [0] );
	}
	// Metodo adicionar produto ao carrrinho aula 117
	public function addProduct(Product $product) {
		$sql = new Sql ();

		$sql->query ( "INSERT INTO tb_cartsproducts (idcart, idproduct) VALUES(:idcart, :idproduct)", [ 

				':idcart' => $this->getidcart (),

				':idproduct' => $product->getidproduct ()
		] );
		$this->getCalculateTotal ();
	}
	// Removendo Produto do carrinho 1 ou todos os produtos aula 117
	public function removeProduct(Product $product, $all = false) {
		$sql = new Sql ();

		if ($all) {

			$sql->query ( "UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL", [ 

					':idcart' => $this->getidcart (),

					':idproduct' => $product->getidproduct ()
			] );
		} else {

			$sql->query ( "UPDATE tb_cartsproducts SET dtremoved = NOW() WHERE idcart = :idcart AND idproduct = :idproduct AND dtremoved IS NULL LIMIT 1", [ 

					':idcart' => $this->getidcart (),

					':idproduct' => $product->getidproduct ()
			] );
		}
	}
	// Metodo listando os produtos adicionados dentro do carrinho Aula 117
	public function getProducts() {
		$sql = new Sql ();

		$rows = $sql->select ( "
				
			SELECT b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl, COUNT(*) AS nrqtd, SUM(b.vlprice) AS vltotal
				
			FROM tb_cartsproducts a
				
			INNER JOIN tb_products b ON a.idproduct = b.idproduct
				
			WHERE a.idcart = :idcart AND a.dtremoved IS NULL
				
			GROUP BY b.idproduct, b.desproduct , b.vlprice, b.vlwidth, b.vlheight, b.vllength, b.vlweight, b.desurl
				
			ORDER BY b.desproduct
				
		", [ 

				':idcart' => $this->getidcart ()
		] );

		return Product::checkList ( $rows );
	}
	// Metodo Soma de todos atributos dos produtos do carrinho aula 118
	public function getProductsTotals() {
		$sql = new Sql ();

		$results = $sql->select ( "
				
			SELECT SUM(vlprice) AS vlprice, SUM(vlwidth) AS vlwidth, SUM(vlheight) AS vlheight, SUM(vllength) AS vllength, SUM(vlweight) AS vlweight, COUNT(*) AS nrqtd
				
			FROM tb_products a
				
			INNER JOIN tb_cartsproducts b ON a.idproduct = b.idproduct
				
			WHERE b.idcart = :idcart AND dtremoved IS NULL;
				
		", [ 

				':idcart' => $this->getidcart ()
		] );

		if (count ( $results ) > 0) {

			return $results [0];
		} else {

			return [ ];
		}
	}
	// Metodo Calcular Frete Aula 118 9:21
	public function setFreight($nrzipcode) {
		$nrzipcode = str_replace ( '-', '', $nrzipcode );

		$totals = $this->getProductsTotals ();

		if ($totals ['nrqtd'] > 0) {

			if ($totals ['vlheight'] < 2)
				$totals ['vlheight'] = 2;

			if ($totals ['vllength'] < 16)
				$totals ['vllength'] = 16;

			$qs = http_build_query ( [ 

					'nCdEmpresa' => '',

					'sDsSenha' => '',

					'nCdServico' => '40010',

					'sCepOrigem' => '09853120',

					'sCepDestino' => $nrzipcode,

					'nVlPeso' => $totals ['vlweight'],

					'nCdFormato' => '1',

					'nVlComprimento' => $totals ['vllength'],

					'nVlAltura' => $totals ['vlheight'],

					'nVlLargura' => $totals ['vlwidth'],

					'nVlDiametro' => '0',

					'sCdMaoPropria' => 'S',

					'nVlValorDeclarado' => $totals ['vlprice'],

					'sCdAvisoRecebimento' => 'S'
			] );

			$xml = simplexml_load_file ( "http://ws.correios.com.br/calculador/CalcPrecoPrazo.asmx/CalcPrecoPrazo?" . $qs );

			$result = $xml->Servicos->cServico;

			if ($result->MsgErro != '') {

				Cart::setMsgError ( $result->MsgErro );
			} else {

				Cart::clearMsgError ();
			}

			$this->setnrdays ( $result->PrazoEntrega );

			$this->setvlfreight ( Cart::formatValueToDecimal ( $result->Valor ) );

			$this->setdeszipcode ( $nrzipcode );

			$this->save ();

			return $result;
		} else {
		}
	}
	// Metodo formatar valor Frete aula 118
	public static function formatValueToDecimal($value): float {
		$value = str_replace ( '.', '', $value );

		return str_replace ( ',', '.', $value );
	}
	// Mensagem de erro set Aula 118 26:30
	public static function setMsgError($msg) {
		$_SESSION [Cart::SESSION_ERROR] = $msg;
	}
	// Mensagem de erro get Aula 118 26:33
	public static function getMsgError() {
		$msg = (isset ( $_SESSION [Cart::SESSION_ERROR] )) ? $_SESSION [Cart::SESSION_ERROR] : "";

		Cart::clearMsgError ();

		return $msg;
	}
	// Mensagem de erro limpar Aula 118 26:37
	public static function clearMsgError() {
		$_SESSION [Cart::SESSION_ERROR] = NULL;
	}
	// Metodo atualizar frete ao adicionar mais produtos Aula 118
	public function updateFreight() {
		if ($this->getdeszipcode () != '') {

			$this->setFreight ( $this->getdeszipcode () );
		}
	}
	// Metodo sobrescrevendo getValues Aula 118 36:00
	public function getValues() {
		$this->getCalculateTotal ();
		return parent::getValues ();
	}
	// Metodo valores totais do carrinho Aula 118 37:00
	public function getCalculateTotal() {
		$this->updateFreight ();

		$totals = $this->getProductsTotals ();

		$this->setvlsubtotal ( $totals ['vlprice'] );

		$this->setvltotal ( $totals ['vlprice'] + ( float ) $this->getvlfreight () );
	}
}
	
	