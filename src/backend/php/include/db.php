<?php
phpinfo()
?>
<?php
$host = "db.vtnzklatibttoxqysqeq.supabase.co";
$port = "5432";
$dbname = "postgres";
$user = "postgres";
$password = "CarlosHenriqueIMM";

try {
    $conn = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Conectado com sucesso!";
} catch (PDOException $e) {
    echo "ERROR: " . $e->getMessage();
    die();
}
?>