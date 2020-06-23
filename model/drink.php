<?php
// Objeto Drink Model

// Classe para capturar os erros:
class DrinkException extends Exception { }

class Drink {

  //Declaração de variáveis:
	private $_iddrink;
	private $_drink_counter;
	private $_userid;

  // Construtor para criação do objeto Drink com as variáveis:
	public function __construct($iddrink, $drink_counter, $userid) {
		$this->setIdDrink($iddrink);
		$this->setDrinkCounter($drink_counter);
		$this->setUserId($userid);
	}

  // Funções para retorno das variáveis:
	public function getIdDrink() {
		return $this->_iddrink;
	}

	public function getDrinkCounter() {
		return $this->_drink_counter;
	}

	public function getUserId() {
		return $this->_userid;
	}

	// Funções para verificação das variáveis:
	public function setIdDrink($iddrink) {
		if(($iddrink !== null) && (!is_numeric($iddrink) || $iddrink <= 0 || $iddrink > 9223372036854775807 || $this->_iddrink !== null)) {
			throw new DrinkException("Id Drink error");
		}
		$this->_iddrink = $iddrink;
	}

	public function setDrinkCounter($drink_counter) {
		if(($drink_counter !== null) && (!is_numeric($drink_counter) || $drink_counter <= 0 || $drink_counter > 9223372036854775807 || $this->_drink_counter !== null)) {
			throw new DrinkException("Drink Counter error");
		}
		$this->_drink_counter = $drink_counter;
	}

	public function setUserId($userid) {
			if(($userid !== null) && (!is_numeric($userid) || $userid <= 0 || $userid > 9223372036854775807 || $this->_userid !== null)) {
			throw new DrinkException("User Id error");
		}
		$this->_userid = $userid;
	}

  // Função para retorno do valor de $drink_counter:
	public function returnDrinkCounter() {
		$drink_counter = $this->getDrinkCounter();
		return $drink_counter;
	}

}
