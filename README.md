# teste_stautrh
Repositório do teste de uma aplicação para um Sistema de Cadastro de Usuários e acompanhamento de consumo de água para seleção da StautRH.

<b>* Endpoints:</b>

- controller/users.php + POST = Criar um novo Usuário;
- controller/login.php + POST = Gerar um Token que será utilizado para autenticação das Requisições;
- controller/users.php?iduser=i + GET = Buscar um Usuário com iduser = i (número inteiro referente ao índice criado na tabela tbusers);
- controller/users.php + GET = Buscar array de Usuários;
- controller/users.php?iduser=i + PUT = Atualizar um Usuário com iduser = i (número inteiro referente ao índice criado na tabela tbusers);
- controller/users.php?iduser=i + DELETE = Deletar um Usuário com iduser = i (número inteiro referente ao índice criado na tabela tbusers)
- controller/users.php?iduser=i + POST = Acrescentar drink_ml e incrementar o contador de consumo de água do Usuário com iduser = i (número inteiro referente ao índice criado na tabela tbusers);
- controller/users.php?page=i + GET = Paginação de array de Usuários (sendo i o número inteiro referente a página a ser listada com até 20 usuários).

<b>* Pasta Model:</b>
Contém os arquivos de modelagem dos usuários (users.php), da formatação das respostas de requisição HTTP (responses.php) e da modelagem dos dados da tabela do contador de consumo de água (drink.php).

<b>* Pasta Controller:</b>
Contém os arquivos de controle das rotas (método HTTP + endpoints) e de regras de negócios por meio dos arquivos users.php, login.php e db.php (este de conexão com o Banco de Dados).

<b>* Arquivo htaccess:</b> Deve ser renomeado para .htaccess (com um ponto no início). Esse arquivo possui um ajuste para o header de Requisição HTTP a ser operada pelo Apache (MAMP).

<b>* Arquivo usersdb.sql:</b> Deve ser utilizado para criação de Banco de Dados com três tabelas (tbusers, tbsessions, tbdrink) no MySQL.

*** Foi utilizado o Apache e o MAMP, assim como Postman para o desenvolvimento dessa aplicação.
