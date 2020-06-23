<?php

require_once('db.php');
require_once('../model/users.php');
require_once('../model/drink.php');
require_once('../model/response.php');

// Conexão com o Banco de Dados:
try {

  $writeDB = DB::connectWriteDB();
  $readDB = DB::connectReadDB();

}
catch(PDOException $ex) {
  error_log("Connection Error: ".$ex, 0);
  $response = new Response();
  $response->setHttpStatusCode(500);
  $response->setSuccess(false);
  $response->addMessage("Erro de conexão com o Banco de Dados");
  $response->send();
  exit;
}

///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

// Confere se não existe parâmetro no endpoint e se o Método HTTP é igual a POST para realizar a criação de Usuário: /users/
if(empty($_GET) && $_SERVER['REQUEST_METHOD'] === 'POST') {

  //Confere se não é POST, se for false segue para criação de novo usuário:
  if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Método HTTP de requisição não permitido");
    $response->send();
    exit;
  }

  if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("Content Type header não segue o formato JSON");
    $response->send();
    exit;
  }

  $rawPostData = file_get_contents('php://input');

  if(!$jsonData = json_decode($rawPostData)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("Request body não segue o formato JSON");
    $response->send();
    exit;
  }

  if(!isset($jsonData->email) || !isset($jsonData->password) || !isset($jsonData->name)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    (!isset($jsonData->email) ? $response->addMessage("Email não fornecido") : false);
    (!isset($jsonData->password) ? $response->addMessage("Senha não fornecida") : false);
    (!isset($jsonData->name) ? $response->addMessage("Nome não fornecido") : false);
    $response->send();
    exit;
  }

  if(strlen($jsonData->email) < 1 || strlen($jsonData->email) > 255 || strlen($jsonData->password) < 1 || strlen($jsonData->password) > 255 || strlen($jsonData->name) < 1 || strlen($jsonData->name) > 255) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    (strlen($jsonData->email) < 1 ? $response->addMessage("Email não pode ficar em branco") : false);
    (strlen($jsonData->email) > 255 ? $response->addMessage("Email não pode ter mais de 255 caracteres") : false);
    (strlen($jsonData->password) < 1 ? $response->addMessage("Senha não pode ficar em branco") : false);
    (strlen($jsonData->password) > 255 ? $response->addMessage("Senha não pode ter mais de 255 caracteres") : false);
    (strlen($jsonData->name) < 1 ? $response->addMessage("Nome não pode ficar em branco") : false);
    (strlen($jsonData->name) > 255 ? $response->addMessage("Nome não pode ter mais de 255 caracteres") : false);
    $response->send();
    exit;
  }

  $email = trim($jsonData->email);
  $name = trim($jsonData->name);
  $password = $jsonData->password;

  try {
    $query = $writeDB->prepare('SELECT idusers from tbusers where email = :email');
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->execute();

    $rowCount = $query->rowCount();

    if($rowCount !== 0) {
      $response = new Response();
      $response->setHttpStatusCode(409);
      $response->setSuccess(false);
      $response->addMessage("Usuário já existe");
      $response->send();
      exit;
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $query = $writeDB->prepare('INSERT into tbusers (email, password, name) values (:email, :password, :name)');
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':name', $name, PDO::PARAM_STR);
    $query->bindParam(':password', $hashed_password, PDO::PARAM_STR);
    $query->execute();

    $rowCount = $query->rowCount();

    if($rowCount === 0) {
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Houve algum erro na criação do Usuário, tente novamente");
      $response->send();
      exit;
    }

    $response = new Response();
    $response->setHttpStatusCode(201);
    $response->setSuccess(true);
    $response->addMessage("Usuário criado");
    $response->send();
    exit;
  }
  catch(PDOException $ex) {
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Houve algum problema na criação do Usuário, tente novamente");
    $response->send();
    exit;
  }

}

// Começo da Autenticação:
else {

  if(!isset($_SERVER['HTTP_AUTHORIZATION']) || strlen($_SERVER['HTTP_AUTHORIZATION']) < 1) {
    $response = new Response();
    $response->setHttpStatusCode(401);
    $response->setSuccess(false);
    (!isset($_SERVER['HTTP_AUTHORIZATION']) ? $response->addMessage("Token está faltando no header") : false);
    (strlen($_SERVER['HTTP_AUTHORIZATION']) < 1 ? $response->addMessage("Token não pode ser em branco") : false);
    $response->send();
    exit;
  }

  $token = $_SERVER['HTTP_AUTHORIZATION'];

  try {
    $query = $writeDB->prepare('SELECT userid FROM tbsessions WHERE token = :token');
    $query->bindParam(':token', $token, PDO::PARAM_STR);
    $query->execute();

    $rowCount = $query->rowCount();

    if($rowCount === 0) {
      $response = new Response();
      $response->setHttpStatusCode(401);
      $response->setSuccess(false);
      $response->addMessage("Token inválido");
      $response->send();
      exit;
    }

    $row = $query->fetch(PDO::FETCH_ASSOC);

    $returned_userid = $row['userid'];

  }
  catch(PDOException $ex) {
    $response = new Response();
    $response->setHttpStatusCode(500);
    $response->setSuccess(false);
    $response->addMessage("Houve um problema para logar - tente novamente");
    $response->send();
    exit;
  }
}

// Final da Autenticação

//Início das rotas de acordo com o Método de Requisição HTTP e se existe algum parâmetro (iduser ou page ou nenhum):
if (array_key_exists("iduser",$_GET)) {
  $userid = $_GET['iduser'];

  if($userid == '' || !is_numeric($userid)) {
    $response = new Response();
    $response->setHttpStatusCode(400);
    $response->setSuccess(false);
    $response->addMessage("User Id não pode ser em branco e precisa ser inteiro");
    $response->send();
    exit;
  }

  // Caso do Método GET: /users/:idusers
  if($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
      if (intval($userid) === $returned_userid) {
        $definitive_userid = $userid;
      } else {
        $response = new Response();
        $response->setHttpStatusCode(409);
        $response->setSuccess(false);
        $response->addMessage("User Id do token não é o mesmo do fornecido na URL");
        $response->send();
        exit;
      }

      $query = $readDB->prepare('SELECT idusers, email, password, name FROM tbusers WHERE idusers = :idusers');
      $query->bindParam(':idusers', $definitive_userid, PDO::PARAM_INT);
  		$query->execute();

      $queryDrink = $readDB->prepare('SELECT iddrink, drink_counter, userid FROM tbdrink WHERE userid = :idusers ORDER BY iddrink DESC LIMIT 1');
      $queryDrink->bindParam(':idusers', $definitive_userid, PDO::PARAM_INT);
  		$queryDrink->execute();

      $rowCount = $query->rowCount();
      $rowCountDrink = $queryDrink->rowCount();

      if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("Usuário não encontrado");
        $response->send();
        exit;
        }

      $row = $query->fetch(PDO::FETCH_ASSOC);

      $user = new User($row['idusers'], $row['email'], $row['password'], $row['name']);

      $returned_email = $row['email'];
      $returned_name = $row['name'];

      if($rowCountDrink === 0) {
        $returned_drink_counter = 0;
      } else {
        $row = $queryDrink->fetch(PDO::FETCH_ASSOC);

        $drink = new Drink($row['iddrink'], $row['drink_counter'], $row['userid']);

        $returned_drink_counter = $row['drink_counter'];
      }

      $returnData['iduser'] = $definitive_userid;
      $returnData['name'] = $returned_name;
      $returnData['email'] = $returned_email;
      $returnData['drink_counter'] = $returned_drink_counter;

      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->toCache(true);
      $response->setData($returnData);
      $response->send();
      exit;
    }
    catch(UserException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage($ex->getMessage());
      $response->send();
      exit;
    }
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Erro em buscar Usuário");
      $response->send();
      exit;
    }
  }

  // Caso do Método POST: /users/:idusers/drink *Nesse caso, não foi possível implementar /drink, deve-se acessar esse Endpoint
  // por meio de /users/:idusers apenas.
  elseif(($_SERVER['REQUEST_METHOD'] === 'POST')) {

    if($_SERVER['CONTENT_TYPE'] !== 'application/json') {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("Content Type header não está no formato JSON");
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

    if(!isset($jsonData->drink_ml)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("Drink_ml não fornecido");
      $response->send();
      exit;
    }

    if(!is_numeric($jsonData->drink_ml) || $jsonData->drink_ml <= 0 || $jsonData->drink_ml > 9223372036854775807) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      (!is_numeric($jsonData->drink_ml) ? $response->addMessage("Drink_ml deve ser um número inteiro") : false);
      ($jsonData->drink_ml <= 0 ? $response->addMessage("Drink_ml não pode ser menor que ou igual a 0") : false);
      ($jsonData->drink_ml > 9223372036854775807 ? $response->addMessage("Drink_ml não pode ser maior que 9223372036854775807") : false);
      $response->send();
      exit;
    }

    try {

      $drink_ml = $jsonData->drink_ml;

      if (intval($userid) === $returned_userid) {
        $definitive_userid = $userid;
      } else {
        $response = new Response();
        $response->setHttpStatusCode(409);
        $response->setSuccess(false);
        $response->addMessage("User Id do token não é o mesmo do fornecido na URL");
        $response->send();
        exit;
      }

      $query = $readDB->prepare('SELECT idusers, email, password, name FROM tbusers WHERE idusers = :idusers');
      $query->bindParam(':idusers', $definitive_userid, PDO::PARAM_INT);
  		$query->execute();

      $queryDrink = $readDB->prepare('SELECT iddrink, drink_counter, drink_ml, userid FROM tbdrink WHERE userid = :idusers ORDER BY iddrink DESC LIMIT 1');
      $queryDrink->bindParam(':idusers', $definitive_userid, PDO::PARAM_INT);
  		$queryDrink->execute();

      $rowCount = $query->rowCount();
      $rowCountDrink = $queryDrink->rowCount();

      if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("Usuário não encontrado");
        $response->send();
        exit;
        }

      $row = $query->fetch(PDO::FETCH_ASSOC);

      $user = new User($row['idusers'], $row['email'], $row['password'], $row['name']);

      $returned_email = $row['email'];
      $returned_name = $row['name'];

      $drink_counter_starter = 1;

      if($rowCountDrink === 0) {
        $queryDrink = $writeDB->prepare('INSERT INTO tbdrink (drink_counter, drink_ml, userid) VALUES (:drink_counter, :drink_ml, :idusers)');
        $queryDrink->bindParam(':idusers', $definitive_userid, PDO::PARAM_INT);
        $queryDrink->bindParam(':drink_ml', $drink_ml, PDO::PARAM_INT);
        $queryDrink->bindParam(':drink_counter', $drink_counter_starter, PDO::PARAM_INT);
        $queryDrink->execute();

        $drink_counter_added = 1;
      }

      else {
        $queryDrink = $readDB->prepare('SELECT iddrink, drink_counter, userid FROM tbdrink WHERE userid = :idusers ORDER BY iddrink DESC LIMIT 1');
        $queryDrink->bindParam(':idusers', $definitive_userid, PDO::PARAM_INT);
        $queryDrink->execute();

        $row = $queryDrink->fetch(PDO::FETCH_ASSOC);

        $drink = new Drink($row['iddrink'], $row['drink_counter'], $row['userid']);

        $drink_counter_added = $row['drink_counter'] + 1;

        $queryDrink = $writeDB->prepare('INSERT INTO tbdrink (drink_counter, drink_ml, userid) VALUES (:drink_counter, :drink_ml, :idusers)');
        $queryDrink->bindParam(':idusers', $definitive_userid, PDO::PARAM_INT);
        $queryDrink->bindParam(':drink_ml', $drink_ml, PDO::PARAM_INT);
        $queryDrink->bindParam(':drink_counter', $drink_counter_added, PDO::PARAM_INT);
        $queryDrink->execute();
     }

        $returnData = array();
        $returnData['iduser'] = $definitive_userid;
        $returnData['name'] = $returned_name;
        $returnData['email'] = $returned_email;
        $returnData['drink_counter'] = $drink_counter_added;

      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->toCache(true);
      $response->setData($returnData);
      $response->send();
      exit;
    }

    catch(UserException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage($ex->getMessage());
      $response->send();
      exit;
    }
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Erro ao buscar o Usuário");
      $response->send();
      exit;
    }
  }

  // Caso do Método DELETE:  /users/:idusers
  elseif($_SERVER['REQUEST_METHOD'] === 'DELETE') {

    try {

      $query = $writeDB->prepare('DELETE FROM tbusers WHERE idusers = :idusers');
      $query->bindParam(':idusers', $returned_userid, PDO::PARAM_INT);
      $query->execute();

      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("Usuário não encontrado");
        $response->send();
        exit;
      }
      else {
        $response = new Response();
        $response->setHttpStatusCode(200);
        $response->setSuccess(true);
        $response->addMessage("Usuário deletado");
        $response->send();
        exit;
      }
    }
    catch(PDOException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Erro ao deletar Usuário");
      $response->send();
      exit;
    }
  }

  // Caso do Método PUT:  /users/:idusers
  elseif($_SERVER['REQUEST_METHOD'] === 'PUT') {

    try {

      if($_SERVER['CONTENT_TYPE'] !== 'application/json') {

        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Content Type não está no formato JSON");
        $response->send();
        exit;
      }

      $rawPutData = file_get_contents('php://input');

      if(!$jsonData = json_decode($rawPutData)) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Request body não está no formato JSON");
        $response->send();
        exit;
      }

      $email_updated = false;
      $password_updated = false;
      $name_updated = false;

      $queryFields = "";

      if(isset($jsonData->email)) {
        $email_updated = true;
        $queryFields .= "email = :email, ";
      }

      if(isset($jsonData->password)) {
        $password_updated = true;
        $queryFields .= "password = :password, ";
      }

      if(isset($jsonData->name)) {
        $name_updated = true;
        $queryFields .= "name = :name, ";
      }

      $queryFields = rtrim($queryFields, ", ");

      if($email_updated === false && $password_updated === false && $name_updated === false) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Nenhum campo para atualização foi fornecido");
        $response->send();
        exit;
      }

      $query = $writeDB->prepare('SELECT idusers, email, password, name FROM tbusers WHERE idusers = :idusers');
      $query->bindParam(':idusers', $returned_userid, PDO::PARAM_INT);
      $query->execute();

      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("Nenhum Usuário para atualizar foi encontrado");
        $response->send();
        exit;
      }

      $row = $query->fetch(PDO::FETCH_ASSOC);
      $user = new User($row['idusers'], $row['email'], $row['password'], $row['name']);

      $queryString = "UPDATE tbusers SET ".$queryFields." WHERE idusers = :idusers";

      $query = $writeDB->prepare($queryString);

      if($email_updated === true) {
        $user->setEmail($jsonData->email);
        $up_email = $user->getEmail();
        $query->bindParam(':email', $up_email, PDO::PARAM_STR);
      }

      if($password_updated === true) {
        $user->setPassword($jsonData->password);
        $up_password = $user->getPassword();
        $query->bindParam(':password', $up_password, PDO::PARAM_STR);
      }

      if($name_updated === true) {
        $user->setName($jsonData->name);
        $up_name = $user->getName();
        $query->bindParam(':name', $up_name, PDO::PARAM_STR);
      }

      $query->bindParam(':idusers', $returned_userid, PDO::PARAM_INT);
    	$query->execute();

      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(400);
        $response->setSuccess(false);
        $response->addMessage("Usuário não atualizado - talvez os mesmos dados que já existiam foram fornecidos");
        $response->send();
        exit;
      }

      $query = $writeDB->prepare('SELECT idusers, email, password, name FROM tbusers WHERE idusers = :idusers');
      $query->bindParam(':idusers', $returned_userid, PDO::PARAM_INT);
      $query->execute();

      $rowCount = $query->rowCount();

      if($rowCount === 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("Usuário não encontrado");
        $response->send();
        exit;
      }

      $row = $query->fetch(PDO::FETCH_ASSOC);

      $user = new User($row['idusers'], $row['email'], $row['password'], $row['name']);

      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->addMessage("Usuário atualizado");
      $response->send();
      exit;
    }
    catch(UserException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage($ex->getMessage());
      $response->send();
      exit;
    }
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Erro ao atualizar Usuário - conferir os dados");
      $response->send();
      exit;
    }
  }

  // Caso de não ser um Método previsto:
  else {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Método requisitado não permitido");
    $response->send();
    exit;
  }
}

// Caso da Paginação: /users/page/:page
elseif(array_key_exists("page",$_GET)) {

  if($_SERVER['REQUEST_METHOD'] === 'GET') {

    $page = $_GET['page'];

    if($page == '' || !is_numeric($page)) {
      $response = new Response();
      $response->setHttpStatusCode(400);
      $response->setSuccess(false);
      $response->addMessage("Page number cannot be blank and must be numeric");
      $response->send();
      exit;
    }

    $limitPerPage = 20;

    try {
      $query = $readDB->prepare('SELECT count(idusers) AS totalNoOfUsers FROM tbusers');
      $query->execute();

      $row = $query->fetch(PDO::FETCH_ASSOC);

      $usersCount = intval($row['totalNoOfUsers']);

      $numOfPages = ceil($usersCount/$limitPerPage);

      if($numOfPages == 0){
        $numOfPages = 1;
      }

      if($page > $numOfPages || $page == 0) {
        $response = new Response();
        $response->setHttpStatusCode(404);
        $response->setSuccess(false);
        $response->addMessage("Page not found");
        $response->send();
        exit;
      }

      $offset = ($page == 1 ?  0 : (20*($page-1)));

      $query = $readDB->prepare('SELECT idusers, email, password, name FROM tbusers LIMIT :pglimit OFFSET :offset');
      $query->bindParam(':pglimit', $limitPerPage, PDO::PARAM_INT);
      $query->bindParam(':offset', $offset, PDO::PARAM_INT);
      $query->execute();

      $rowCount = $query->rowCount();

      $userArray = array();

      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $user = new User($row['idusers'], $row['email'], $row['password'], $row['name']);

        $userArray[] = $user->returnUserAsArray();
      }

      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['total_rows'] = $usersCount;
      $returnData['total_pages'] = $numOfPages;
      ($page < $numOfPages ? $returnData['has_next_page'] = true : $returnData['has_next_page'] = false);
      ($page > 1 ? $returnData['has_previous_page'] = true : $returnData['has_previous_page'] = false);
      $returnData['users'] = $userArray;

      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->toCache(true);
      $response->setData($returnData);
      $response->send();
      exit;
    }
      catch(UserException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage($ex->getMessage());
      $response->send();
      exit;
    }
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Failed to get tasks");
      $response->send();
      exit;
    }
  }
  // Caso de não utilizar o Método GET:
  else {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Método requisitado não permitido");
    $response->send();
    exit;
  }
}
// Caso de não haver parâmetro: /users/
elseif(empty($_GET)) {
  // Caso do Método GET:
  if($_SERVER['REQUEST_METHOD'] === 'GET') {

    try {

      $query = $readDB->prepare('SELECT idusers, email, password, name FROM tbusers');
      $query->execute();

      $rowCount = $query->rowCount();

      $userArray = array();

      while($row = $query->fetch(PDO::FETCH_ASSOC)) {
        $user = new User($row['idusers'], $row['email'], $row['password'], $row['name']);

        $userArray[] = $user->returnUserAsArray();
      }

      $returnData = array();
      $returnData['rows_returned'] = $rowCount;
      $returnData['users'] = $userArray;

      $response = new Response();
      $response->setHttpStatusCode(200);
      $response->setSuccess(true);
      $response->toCache(true);
      $response->setData($returnData);
      $response->send();
      exit;
    }
    catch(UserException $ex) {
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage($ex->getMessage());
      $response->send();
      exit;
    }
    catch(PDOException $ex) {
      error_log("Database Query Error: ".$ex, 0);
      $response = new Response();
      $response->setHttpStatusCode(500);
      $response->setSuccess(false);
      $response->addMessage("Erro ao buscar Usuários");
      $response->send();
      exit;
    }
  }
  // Caso de não ser o Método POST:
  elseif($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response = new Response();
    $response->setHttpStatusCode(405);
    $response->setSuccess(false);
    $response->addMessage("Método requisitado não permitido");
    $response->send();
    exit;
  }
}
// Caso do endpoint não estar disponível:
else {
  $response = new Response();
  $response->setHttpStatusCode(404);
  $response->setSuccess(false);
  $response->addMessage("Endpoint não encontrado");
  $response->send();
  exit;
}
