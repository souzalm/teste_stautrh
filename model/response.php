<?php
  // Objeto de resposta:
  class Response {

    //Declaração de variáveis globais:
    private $_success;
    private $_httpStatusCode;
    private $_messages = array();
    private $_data;
    private $_toCache = false;
    private $_responseData;

    //Funções para inicialização das variáveis globais:
    public function setSuccess($success) {
      $this->_success = $success;
    }

    public function setHttpStatusCode($httpStatusCode) {
      $this->_httpStatusCode = $httpStatusCode;
    }

    public function addMessage($message) {
      $this->_messages[] = $message;
    }

    public function setData($data) {
      $this->_data = $data;
    }

    public function toCache($toCache) {
      $this->_toCache = $toCache;
    }

    //Função para envio do objeto de resposta no formato JSON:
    public function send() {

      header('Content-type: application/json;charset=uft-8');

    //Verificação se há ou não a solicitação de Cache dos dados:
      if($this->_toCache == true) {
        header('Cache-control: max-age=60');
      } else {
        header('Cache-control: no-cache, no-store');
      }

    // Verificação da variável $_httpStatusCode e da variável $_success:
		if(!is_numeric($this->_httpStatusCode) || ($this->_success !== false && $this->_success !== true )) {
			$this->_responseData['statusCode'] = 500;
			$this->_responseData['success'] = false;
			$this->addMessage("Erro na criação da resposta");
			$this->_responseData['messages'] = $this->_messages;
		}
    elseif(!$this->_data) {
      http_response_code($this->_httpStatusCode);
      $this->_responseData['statusCode'] = $this->_httpStatusCode;
      $this->_responseData['success'] = $this->_success;
      $this->_responseData['messages'] = $this->_messages;
    }
		else {
			http_response_code($this->_httpStatusCode);
			$this->_responseData['statusCode'] = $this->_httpStatusCode;
			$this->_responseData['success'] = $this->_success;
			$this->_responseData['messages'] = $this->_messages;
			$this->_responseData['data'] = $this->_data;
		}

		// Transformação do array $_responseData em formato JSON:
		echo json_encode($this->_responseData);
    }
  }
