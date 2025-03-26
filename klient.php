﻿<?php
session_start();

if (!isset($_SESSION['logged_in']) || $_SESSION['role'] !== 'Klient') {
    header("Location: index.php");
    exit();
}

$servername = "localhost";
$username = "root";
$password = "";
$database = "bank";

$connection = new mysqli($servername, $username, $password, $database);

if ($connection->connect_error) {
    die("Connection failed: " . $connection->connect_error);
}

$username = $_SESSION['username'];
$query = "SELECT imie, kredyt FROM klienty WHERE username = '$username'";
$result = $connection->query($query);
$imie = "Klient";
$kredyt = 0;

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $imie = $row['imie'];
    $kredyt = $row['kredyt'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['take_credit'])) {
    $credit_amount = floatval($_POST['credit_amount']);
    
    if ($credit_amount > 0) {
        $query = "UPDATE klienty SET kredyt = kredyt + $credit_amount WHERE username = '$username'";
        if ($connection->query($query)) {
            $kredyt += $credit_amount;
            $successMessage = "Przyznano kredyt w wysokości " . number_format($credit_amount, 2) . " PLN.";
        } else {
            $errorMessage = "Błąd przyznawania kredytu: " . $connection->error;
        }
    } else {
        $errorMessage = "Wprowadź poprawną kwotę kredytu!";
    }
}
?>

<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Klienta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="klient.css">
</head>
<body>
    <div class="container text-center mt-5">
        <h1>Panel Klienta</h1>
        <p>Witaj, <?= htmlspecialchars($imie); ?>!</p>
        <p>Twoje konto bankowe jest aktywne.</p>
        <p>Aktualny kredyt: <strong><?= number_format($kredyt, 2); ?> PLN</strong></p>

        <h2 class="mt-4">Weź Kredyt</h2>
        <?php if (isset($successMessage)) echo "<p class='text-success'>$successMessage</p>"; ?>
        <?php if (isset($errorMessage)) echo "<p class='text-danger'>$errorMessage</p>"; ?>
        <form method="post">
            <input type="number" step="0.01" name="credit_amount" class="credit-input mb-2" placeholder="Kwota kredytu" required>
            <button type="submit" name="take_credit" class="btn btn-primary">Weź Kredyt</button>
        </form>
        <a href="index.php?action=logout" class="btn btn-danger mt-3">Wyloguj się</a>
    </div>
</body>
</html>
