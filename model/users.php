<?php
// Objeto User Model

// Classe para capturar os erros:
class UserException extends Exception { }

class User {

  // Declaração de variáveis:
	private $_idusers;
	private $_email;
	private $_password;
	private $_name;

  // Construtor para criação do objeto User com as variáveis:
	public function __construct($idusers, $email, $password, $name) {
		$this->setIdUsers($idusers);
		$this->setEmail($email);
		$this->setPassword($password);
		$this->setName($name);
	}

  // Funções para retorno das variáveis:
	public function getIdUsers() {
		return $this->_idusers;
	}

	public function getEmail() {
		return $this->_email;
	}

	public function getPassword() {
		return $this->_password;
	}

	public function getName() {
		return $this->_name;
	}

	// Funções para verificação e transformação das variáveis:
	public function setIdUsers($idusers) {
		if(($idusers !== null) && (!is_numeric($idusers) || $idusers <= 0 || $idusers > 9223372036854775807 || $this->_idusers !== null)) {
			throw new UserException("Id Users error");
		}
		$this->_idusers = $idusers;
	}

	public function setEmail($email) {
		if(strlen($email) < 1 || strlen($email) > 255) {
			throw new UserException("Email error");
		}
		$this->_email = $email;
	}

	public function setPassword($password) {
		if(strlen($password) < 1 || strlen($password) > 255) {
			throw new UserException("Password error");
		}
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
		$this->_password = $hashed_password;
	}

	public function setName($name) {
		if(strlen($name) < 1 || strlen($name) > 255) {
			throw new UserException("Name error");
		}
		$this->_name = $name;
	}

  // Função para retorno do objeto User como um array para o formato JSON sem o password:
	public function returnUserAsArray() {
		$user = array();
		$user['idusers'] = $this->getIdUsers();
		$user['email'] = $this->getEmail();
		$user['name'] = $this->getName();
		return $user;
	}
}
