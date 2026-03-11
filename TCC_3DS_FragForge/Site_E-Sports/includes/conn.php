<?php

const HOST = "localhost";
const PORT = "3306";
const USER = "root";
const SENHA = "root";
const BANCO = "fragforge";

try{

$conn = new PDO(
    "mysql:host=".HOST.";port=".PORT.";dbname=".BANCO,
    USER,
    SENHA
);

$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

}catch(PDOException $e){

echo "ERRO: ".$e->getMessage();

}