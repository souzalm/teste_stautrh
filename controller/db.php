<?php

class DB {
	// Declaração de variáveis estáticas para leitura e escrita do Banco de Dados:
	private static $writeDBConnection;
	private static $readDBConnection;

	// Função para realizar a escrita no Banco de Dados:
	public static function connectWriteDB() {
		if(self::$writeDBConnection === null) {
				self::$writeDBConnection = new PDO('mysql:host=localhost;dbname=usersdb;charset=utf8', 'root', 'root');
				self::$writeDBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				self::$writeDBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		}

		return self::$writeDBConnection;
	}

	// Função para realizar a leitura do Banco de Dados:
	public static function connectReadDB() {
		if(self::$readDBConnection === null) {
				self::$readDBConnection = new PDO('mysql:host=localhost;dbname=usersdb;charset=utf8', 'root', 'root');
				self::$readDBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				self::$readDBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
		}

		return self::$readDBConnection;
	}

}
