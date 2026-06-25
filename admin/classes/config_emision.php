<?php
/**
 * Configuración del modo de emisión (efectiva).
 *
 *   modo = 'tercero'      -> usa la API de facturalahoy (clase Facturalaya)
 *   modo = 'certificado'  -> emite directo a SUNAT con TU certificado (clase EmisorSunat, vía Greenter)
 *
 * El valor real se gestiona desde la pantalla "Configuración → Modo de Emisión"
 * y se guarda en config_emision.json. Aquí solo lo devolvemos ya resuelto.
 */
require_once __DIR__ . '/ConfigEmision.php';
return ConfigEmision::get();
