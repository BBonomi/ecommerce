<?php

namespace Hcode\Model;

use Hcode\Model;
use Hcode\DB\Sql;

class OrderStatus extends Model {
	const EM_ABERTO = 1;
	const AGUARDANDO_PAGAMENTO = 2;
	const PAGO = 3;
	const ENTREGUE = 4;

	// Aula 127 Pedidos-Admin
	public static function listAll() 
	{
		$sql = new Sql ();

		return $sql->select ( "SELECT * FROM tb_ordersstatus ORDER BY desstatus" );
	}
}
?>