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
}
	
	