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
	// Aula 119 - Login
	const ERROR = "UserError";
	// Aula 119 - Login
	const ERROR_REGISTER = "UserErrorRegister";
	// Aula 119 - Login
	const SUCCESS = "UserSucesss";

	// Metodo Sessão id usuario vinculado ao Carrinho Cart.php Aula 116 15:48
	public static function getFromSession() {
		$user = new User ();

		if (isset ( $_SESSION [User::SESSION] ) && ( int ) $_SESSION [User::SESSION] ['iduser'] > 0) {

			$user->setData ( $_SESSION [User::SESSION] );
		}
		return $user;
	}
	// Metodo Verificar se usuario está logado vinculado ao Carrinho Cart.php Aula 116 17:59
	public static function checkLogin($inadmin = true) {
		if (! isset ( $_SESSION [User::SESSION] ) || ! $_SESSION [User::SESSION] || ! ( int ) $_SESSION [User::SESSION] ["iduser"] > 0) {
			// Não está logado
			return false;
		} else {

			if ($inadmin === true && ( bool ) $_SESSION [User::SESSION] ['inadmin'] === true) {

				return true;
			} else if ($inadmin === false) {

				return true;
			} else {

				return false;
			}
		}
	}

	// Método estatico Login e Senha
	public static function login($login, $password) {
		// Conectando Banco de Dados
		$sql = new Sql ();
		// Consultando tabela usuarios
		$results = $sql->select ( "SELECT * FROM tb_users a 
		INNER JOIN tb_persons b 
		ON a.idperson = b.idperson 
		WHERE a.deslogin =:LOGIN", array (
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
			$data ['desperson'] = utf8_encode ( $data ['desperson'] ); // inserido aula 119 Login
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

	// Metodo de verificação de Login || Alerado Aula 116 vinculado ao getFromSession 23:06
	public static function verifyLogin($inadmin = true) { // $inadmin usuario logado na administracao
		if (! User::checkLogin ( $inadmin )) {
			if ($inadmin) {
				header ( "Location: /admin/login" );
			} else {
				header ( "Location: /login" );
			}
			exit ();
		}
	}
	// Metodo Logout
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
				":desperson" => utf8_decode ( $this->getdesperson () ), // alterado aula 119 Login
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

		$data = $results [0]; // inserido aula 119 Login

		$data ['desperson'] = utf8_encode ( $data ['desperson'] ); // inserido aula 119 Login

		$this->setData ( $results [0] );
	}

	// Função Update aula 107
	public function update() {
		$sql = new Sql ();
		// Chamando a procedure BD
		$results = $sql->select ( "CALL sp_usersupdate_save(:iduser, :desperson, :deslogin, :despassword, :desemail, :nrphone, :inadmin)", array (
				":iduser" => $this->getiduser (),
				":desperson" => utf8_decode ( $this->getdesperson () ), // alterado aula 119 Login
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

					$link = "http://www.hcodecommerce.com.br/forgot/reset?code=$code"; // Adicionado aula 121 5:20
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
				":idrecovery" => $idrecovery
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
	// Aula 119 Erro 12:13
	public static function setError($msg) {
		$_SESSION [User::ERROR] = $msg;
	}
	// Pegar o Erro da Sessão Aula 119
	public static function getError() {
		$msg = (isset ( $_SESSION [User::ERROR] ) && $_SESSION [User::ERROR]) ? $_SESSION [User::ERROR] : '';

		User::clearError ();

		return $msg;
	}
	public static function clearError() {
		$_SESSION [User::ERROR] = NULL;
	}

	// Aula 120 Erro de Registro Usuario
	public static function setErrorRegister($msg) {
		$_SESSION [User::ERROR_REGISTER] = $msg;
	}
	public static function getErrorRegister() {
		$msg = (isset ( $_SESSION [User::ERROR_REGISTER] ) && $_SESSION [User::ERROR_REGISTER]) ? $_SESSION [User::ERROR_REGISTER] : '';

		User::clearErrorRegister ();

		return $msg;
	}
	public static function clearErrorRegister() {
		$_SESSION [User::ERROR_REGISTER] = NULL;
	}
	// Checa se o login já existe aula 120 12:49
	public static function checkLoginExist($login) {
		$sql = new Sql ();

		$results = $sql->select ( "SELECT * FROM tb_users WHERE deslogin = :deslogin", [ 

				':deslogin' => $login
		] );

		return (count ( $results ) > 0);
	}
	// Metodo Success Aula 122 8:15
	public static function setSuccess($msg) {
		$_SESSION [User::SUCCESS] = $msg;
	}
	public static function getSuccess() {
		$msg = (isset ( $_SESSION [User::SUCCESS] ) && $_SESSION [User::SUCCESS]) ? $_SESSION [User::SUCCESS] : '';

		User::clearSuccess ();

		return $msg;
	}
	public static function clearSuccess() {
		$_SESSION [User::SUCCESS] = NULL;
	}
}

?>

	