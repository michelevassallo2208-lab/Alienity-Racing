<?php
session_start();
require 'db.php';

if (!isset($_SESSION["user_id"]) || $_SESSION["ruolo"] != "Owner") {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = $_POST["id"];
    $action = $_POST["action"];

    if ($action == "accetta") {
        $sql = "UPDATE candidature SET stato='Accettata' WHERE id=?";
    } elseif ($action == "rifiuta") {
        $sql = "UPDATE candidature SET stato='Rifiutata' WHERE id=?";
    }

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();

    header("Location: dashboard_admin.php");
    exit();
}
?>
