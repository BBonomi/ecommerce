<?php

namespace Hcode\Model;

use Hcode\Model;
use Hcode\DB\Sql;

// Classe Usuario
class User extends Model {
	// constante sessão
	const SESSION = "User";

	// Método estatico Login e Senha
	public static function login($login, $password) {
		// Conectando Banco de Dados
		$sql = new Sql ();
		// Consultando tabela usuarios
		$results = $sql->select ( "SELECT *FROM tb_users WHERE deslogin = :LOGIN", array (
				":LOGIN" => $login
		) );
		// Se não encontrar usuario gera uma exceção
		if (count ( $results ) === 0) {
			throw new \ErrorException ( "Usuário inexistente ou senha inválida." );
		}
		// Registro encontrado
		$data = $results [0];
		// Verficando a senha
		if (password_verify ( $password, $data ["despassword"] ) === true) {
			$user = new User ();
			$user->setData ( $data );
			// Criando sessão usuario
			$_SESSION [User::SESSION] = $user->getValues ();
			return $user;
			// var_dump ( $user );
			// exit ();
		} else {
			throw new \ErrorException ( "Usuário inexistente ou senha inválida." );
		}
	}

	// Metodo de verificação de Login
	public static function verifyLogin($inadmin = true) { // $inadmin usuario logado na administracao
		if (! isset ( $_SESSION [User::SESSION] ) || ! $_SESSION [User::SESSION] || ! ( int ) $_SESSION [User::SESSION] ["iduser"] > 0 || ( bool ) $_SESSION [User::SESSION] ["inadmin"] !== $inadmin) {
			header ( "Location: /admin/login " ); // Caso não seja definida volta para tela de login
			exit ();
		}
	}
	public static function logout() {
		$_SESSION [User::SESSION] = NULL;
	}
	// Metodo Listar todos os usuarios aula 107
	public static function listAll() {
		$sql = new Sql ();
		return $sql->select ( "SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) ORDER BY b.desperson" );
	}
	// Metodo para salvar novo usuario no banco de dados
	public function save() {
		$sql = new Sql ();
		// Chamando a procedure BD
		$results = $sql->select ( "CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array (
				":desperson" => $this->getdesperson (),
				":deslogin" => $this->getdeslogin (),
				":despassword" => $this->getdespassword (),
				":desemail" => $this->getdesemail (),
				":nrphone" => $this->getnrphone (),
				":inadmin" => $this->getinadmin ()
		) );
		$this->setData ( $results [0] );
	}
	public function get($iduser) {
		$sql = new Sql ();

		$results = $sql->select ( "SELECT * FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser;", array (
				":iduser" => $iduser
		) );

		$data = $results [0];

		$this->setData ( $data );
	}
}