<?php

session_start();

include_once("connection.php");
include_once("url.php");

$data = $_POST;

//Funções 
function mascara($valor, $formato){
    $retorno = '';
    $posicao_valor = 0;
    $formato = "(##) #####-####";

    for ($i = 0; $i <= strlen($formato) - 1; $i++) {
        if ($formato[$i] == '#') {
            if (isset($valor[$posicao_valor])) {

                $retorno .= $valor[$posicao_valor++];
            }
        } else {

            $retorno .= $formato[$i];
        }
    }

    return $retorno;
}

//MODIFICAÇÕES NO BANCO
if (!empty($data)) {


    //Criar contato
    if ($data["type"] === "create") {

        $name = $data["name"];
        $phone = $data["phone"];
        $phonefor = mascara($phone,$formato);
        $observations = $data["observations"];

        $query = "INSERT INTO contacts (name,phone, observations) VALUES (:name,:phone,:observations)";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":phone", $phonefor);
        $stmt->bindParam(":observations", $observations);

        try {
            $stmt->execute();
            $_SESSION["msg"] = "Contato criado com sucesso!";
        } catch (PDOException $e) {
            $error = $e->getMessage();
            echo "ERRO: $error";
        }
        //Editar contato
    } else if ($data["type"] === "edit") {
        $name = $data["name"];
        $phone = $data["phone"];
        $observations = $data["observations"];
        $id = $data["id"];

        $query = "UPDATE contacts 
                     SET name = :name , phone = :phone, observations = :observations 
                     WHERE id = :id";

        $stmt = $conn->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":phone", $phone);
        $stmt->bindParam(":observations", $observations);
        $stmt->bindParam(":id", $id);

        try {
            $stmt->execute();
            $_SESSION["msg"] = "Contato atualizado com sucesso!";
        } catch (PDOException $e) {
            $error = $e->getMessage();
            echo "ERRO: $error";
        }
        //Deletar contato    
    } else if ($data["type"] === "delete") {
        $id = $data["id"];

        $query = "DELETE FROM contacts WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":id", $id);

        try {
            $stmt->execute();
            $_SESSION["msg"] = "Contato deletado com sucesso!";
        } catch (PDOException $e) {
            $error = $e->getMessage();
            echo "ERRO: $error";
        }
    }

    //Redirect Home 
    header("location:" . $BASE_URL . "../index.php");
    //SELEÇÃO DE DADOS
} else {
    $id;

    if (!empty($_GET)) {
        $id = $_GET["id"];
    }

    //Retorna o dado de um contato
    if (!empty($id)) {
        $query = "SELECT * FROM contacts WHERE id = :id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        $contact = $stmt->fetch();
    } else {
        //Retorna todos os contatos 
        $contacts = [];

        $query = "SELECT * FROM contacts";

        $stmt = $conn->prepare($query);

        $stmt->execute();

        $contacts = $stmt->fetchAll();
    }
}

// FECHAR CONEXÃO
$conn = null;
