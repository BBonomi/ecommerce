<?php

namespace Hcode\Model;

use Hcode\Mailer;
use Hcode\Model;
use Hcode\DB\Sql;

// Classe Usuario
class User extends Model {
	// constante sessão
	const SESSION = "User";
	// constante criptografia aula 108 linha 131
	const SECRET = "HcodePhp7_Secret";
	// constante criptografia aula 108 linha 131
	const SECRET_IV = "HcodePhp7_Secret_IV";

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
	// Metodo para salvar novo usuario no banco de dados aula 107
	public function save() {
		$sql = new Sql ();
		// Chamando a procedure BD
		$results = $sql->select ( "CALL sp_users_save(:desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array (
				":desperson" => $this->getdesperson (),
				":deslogin" => $this->getdeslogin (),
				// ":despassword" => $this->getdespassword (),
				// ":despassword" => password_hash ( $this->getdespassword (), PASSWORD_DEFAULT, ['cont' => 12] ),
				":despassword" => User::getPasswordHash ( $this->getdespassword () ),
				":desemail" => $this->getdesemail (),
				":nrphone" => $this->getnrphone (),
				":inadmin" => $this->getinadmin ()
		) );
		$this->setData ( $results [0] );
	}

	// Metodo Update usuario aula 107
	public function get($iduser) {
		$sql = new Sql ();
		$results = $sql->select ( "SELECT *FROM tb_users a INNER JOIN tb_persons b USING(idperson) WHERE a.iduser = :iduser", array (
				":iduser" => $iduser
		) );
		$this->setData ( $results [0] );
	}

	// Função Update aula 107
	public function update() {
		$sql = new Sql ();
		// Chamando a procedure BD
		$results = $sql->select ( "CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array (
				":iduser" => $this->getiduser (),
				":desperson" => $this->getdesperson (),
				":deslogin" => $this->getdeslogin (),
				":despassword" => User::getPasswordHash ( $this->getdespassword () ),
				":desemail" => $this->getdesemail (),
				":nrphone" => $this->getnrphone (),
				":inadmin" => $this->getinadmin ()
		) );
		$this->setData ( $results [0] );
	}

	// Função DELETE aula 107
	public function delete() {
		$sql = new Sql ();
		$sql->query ( "CALL sp_users_delete(:iduser)", array (
				"iduser" => $this->getiduser ()
		) );
	}

	// Meto Esqueci a Senha aula 108
	public static function getForgot($email, $inadmin = true) {
		$sql = new Sql ();

		$results = $sql->select ( "
				
			SELECT *
				
			FROM tb_persons a
				
			INNER JOIN tb_users b USING(idperson)
				
			WHERE a.desemail = :email;
				
		", array (

				":email" => $email
		) );

		if (count ( $results ) === 0) {

			throw new \Exception ( "Não foi possível recuperar a senha." );
		} else {

			$data = $results [0];

			$results2 = $sql->select ( "CALL sp_userspasswordsrecoveries_create(:iduser, :desip)", array (

					":iduser" => $data ['iduser'],

					":desip" => $_SERVER ['REMOTE_ADDR']
			) );

			if (count ( $results2 ) === 0) {

				throw new \Exception ( "Não foi possível recuperar a senha." );
			} else {

				$dataRecovery = $results2 [0];

				$code = openssl_encrypt ( $dataRecovery ['idrecovery'], 'AES-128-CBC', pack ( "a16", User::SECRET ), 0, pack ( "a16", User::SECRET_IV ) );

				$code = base64_encode ( $code );

				if ($inadmin === true) {

					$link = "http://www.hcodecommerce.com.br/admin/forgot/reset?code=$code";
				} else {

					$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code";
				}

				$mailer = new Mailer ( $data ['desemail'], $data ['desperson'], "Redefinir senha da Hcode Store", "forgot", array (

						"name" => $data ['desperson'],

						"link" => $link
				) );

				$mailer->send ();

				return $link;
			}
		}
	}
	// Metodo de decodificação Esqueci a senha
	public static function validForgotDecrypt($code) {
		$code = base64_decode ( $code );

		$idrecovery = openssl_decrypt ( $code, 'AES-128-CBC', pack ( "a16", User::SECRET ), 0, pack ( "a16", User::SECRET_IV ) );

		$sql = new Sql ();

		$results = $sql->select ( "
				
			SELECT *
				
			FROM tb_userspasswordsrecoveries a
				
			INNER JOIN tb_users b USING(iduser)
				
			INNER JOIN tb_persons c USING(idperson)
				
			WHERE
				
				a.idrecovery = :idrecovery
				
				AND
				
				a.dtrecovery IS NULL
				
				AND
				
				DATE_ADD(a.dtregister, INTERVAL 1 HOUR) >= NOW(); 
				
		", array (

				":idrecovery" => $idrecovery
		) );

		if (count ( $results ) === 0) {

			throw new \Exception ( "Não foi possível recuperar a senha." );
		} else {

			return $results [0];
		}
	}
	// Metodo para dar update no banco de dados recuperar senha
	public static function setFogotUsed($idrecovery) {
		$sql = new Sql ();
		$sql->query ( "UPDATE tb_userspasswordsrecoveries SET dtrecovery = NOW() WHERE idrecovery = :idrecovery", array (
				":idrecover" => $idrecovery
		) );
	}
	// Metodo trocar senha recebida no formulário
	public function setPassword($password) {
		$sql = new Sql ();

		$sql->query ( "UPDATE tb_users SET despassword = :password WHERE iduser = :iduser", array (

				":password" => $password,

				":iduser" => $this->getiduser ()
		) );
	}
	// Metodo Hash Aula 108 $app->post ( "admin/forgot/reset", function ()
	public static function getPasswordHash($password) {
		return password_hash ( $password, PASSWORD_DEFAULT, [ 

				'cost' => 12
		] );
	}
}
?>

	