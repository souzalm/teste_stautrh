<?php

require_once('db.php');
require_once('../model/drink.php');
require_once('../model/response.php');

// Conexão com o Banco de Dados:
try {

  $writeDB = DB::connectWriteDB();
  $readDB = DB::connectReadDB();

}
catch(PDOException $ex) {
  // log connection error for troubleshooting and return a json error response
  error_log("Connection Error: ".$ex, 0);
  $response = new Response();
  $response->setHttpStatusCode(500);
  $response->setSuccess(false);
  $response->addMessage("Database connection error");
  $response->send();
  exit;
}

// Erro quando o Método HTTP não é POST:
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  $response = new Response();
  $response->setHttpStatusCode(405);
  $response->setSuccess(false);
  $response->addMessage("Método requisitado não permitido");
  $response->send();
  exit;
}
  // Início da criação da Sessão de Log in:
  elseif(empty($_GET)) {
    // Será criado pelo Método POST: /login
    if($_SERVER['REQUEST_METHOD'] !== 'POST') {
      $response = new Response();
      $response->setHttpStatusCode(405);
      $response->setSuccess(false);
      $response->addMessage("Método requisitado não é permitido");
      $response->send();
      exit;
    }

    sleep(1);

    if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("Content Type não está no formato JSON");
      $response->send();
      exit;
    }

    $rawPostData = file_get_contents('php://input');

    if(!$jsonData = json_decode($rawPostData)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("Request body não está no formato JSON");
      $response->send();
      exit;
    }

    if(!isset($jsonData->email) || !isset($jsonData->password)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      (!isset($jsonData->email) ? $response->addMessage("Email não fornecido") : false);
      (!isset($jsonData->password) ? $response->addMessage("Password não fornecido") : false);
      $response->send();
      exit;
    }

    if(strlen($jsonData->email) < 1 || strlen($jsonData->email) > 255 || strlen($jsonData->password) < 1 || strlen($jsonData->password) > 255) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      (strlen($jsonData->email) < 1 ? $response->addMessage("Email não pode ser em branco") : false);
      (strlen($jsonData->email) > 255 ? $response->addMessage("Email não pode ser maior que 255 caracteres") : false);
      (strlen($jsonData->password) < 1 ? $response->addMessage("Password não pode ser em branco") : false);
      (strlen($jsonData->password) > 255 ? $response->addMessage("Password não pode ser maior que 255 caracteres") : false);
      $response->send();
      exit;
    }

    try {
      $email = $jsonData->email;
      $password = $jsonData->password;

      $query = $writeDB->prepare('SELECT idusers, email, password, name FROM tbusers WHERE email = :email');
      $query->bindParam(':email', $email, PDO::PARAM_STR);
      $query->execute();

      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage("Email ou password estão incorretos");
        $response->send();
        exit;
      }

      $row = $query->fetch(PDO::FETCH_ASSOC);

      $returned_id = $row['idusers'];
      $returned_email = $row['email'];
      $returned_password = $row['password'];
      $returned_name = $row['name'];

      if(!password_verify($password, $returned_password)) {
        $response = new Response();
        $response->setHttpStatusCode(401);
        $response->setSuccess(false);
        $response->addMessage("Username ou password estão incorretos");
        $response->send();
        exit;
      }

      $token = base64_encode(bin2hex(openssl_random_pseudo_bytes(24)).time());

    }
    catch(PDOException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Não foi possui realizar o log in - tente novamente");
      $response->send();
      exit;
    }

    try {
      $writeDB->beginTransaction();
      $query = $writeDB->prepare('INSERT INTO tbsessions (userid, token) VALUES (:userid, :token)');
      $query->bindParam(':userid', $returned_id, PDO::PARAM_INT);
      $query->bindParam(':token', $token, PDO::PARAM_STR);
      $query->execute();

      $queryDrink = $readDB->prepare('SELECT iddrink, drink_counter, userid FROM tbdrink WHERE userid = :idusers ORDER BY iddrink DESC LIMIT 1');
      $queryDrink->bindParam(':idusers', $returned_id, PDO::PARAM_INT);
  		$queryDrink->execute();

      $rowCountDrink = $queryDrink->rowCount();

      $row = $queryDrink->fetch(PDO::FETCH_ASSOC);

      $drink = new Drink($row['iddrink'], $row['drink_counter'], $row['userid']);

      $drink_counter = $drink->returnDrinkCounter();

      $writeDB->commit();

      $returnData = array();
      $returnData['token'] = $token;
      $returnData['iduser'] = $returned_id;
      $returnData['name'] = $returned_name;
      $returnData['email'] = $returned_email;
      if($rowCountDrink === 0) {
      $returnData['drink_counter'] = 0;
      } else {
      $returnData['drink_counter'] = $drink_counter;
      }

      $response = new Response();
      $response->setHttpStatusCode(201);
      $response->setSuccess(true);
      $response->setData($returnData);
      $response->send();
      exit;
    }
    catch(PDOException $ex) {
      $writeDB->rollBack();
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Não foi possível realizar o log in - tente novamente");
      $response->send();
      exit;
    }
  }

    // Caso de um Endpoint não estar disponível:
    else {
      $response = new Response();
      $response->setHttpStatusCode(404);
      $response->setSuccess(false);
      $response->addMessage("Endpoint não encontrado");
      $response->send();
      exit;
    }
