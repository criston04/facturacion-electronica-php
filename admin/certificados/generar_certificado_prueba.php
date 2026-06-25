<?php
/**
 * Genera un CERTIFICADO DE PRUEBA autofirmado para firmar XML en el entorno BETA de SUNAT.
 * NO sirve para producción: en producción usa tu certificado real (SUNAT o entidad certificadora).
 * Uso:  php admin/certificados/generar_certificado_prueba.php
 */
$dir = __DIR__;

// En Windows, PHP suele no encontrar openssl.cnf; creamos uno mínimo y lo pasamos explícito.
$cnf = "$dir/_openssl_min.cnf";
file_put_contents($cnf, "[ req ]\ndistinguished_name = dn\nprompt = no\n[ dn ]\n");

$config = [
    'config'           => $cnf,
    'digest_alg'       => 'sha256',
    'private_key_bits' => 2048,
    'private_key_type' => OPENSSL_KEYTYPE_RSA,
];

$dn = [
    "countryName"         => "PE",
    "stateOrProvinceName" => "Lima",
    "localityName"        => "Lima",
    "organizationName"    => "EMPRESA DE PRUEBAS SUNAT",
    "commonName"          => "20000000001",
];

$privkey = openssl_pkey_new($config);
if (!$privkey) { exit("Error pkey: " . openssl_error_string() . "\n"); }

$csr = openssl_csr_new($dn, $privkey, $config);
if (!$csr) { exit("Error csr: " . openssl_error_string() . "\n"); }

$x509 = openssl_csr_sign($csr, null, $privkey, 730, $config);
if (!$x509) { exit("Error sign: " . openssl_error_string() . "\n"); }

openssl_x509_export($x509, $certPem);
openssl_pkey_export($privkey, $keyPem, null, $config);

$pem = $keyPem . $certPem;
file_put_contents("$dir/certificado_prueba.pem", $pem);
@unlink($cnf);

echo "OK: certificado de prueba generado (" . strlen($pem) . " bytes)\n";
