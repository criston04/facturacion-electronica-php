<?php

/**
 * EmisorFactory: decide QUÉ emisor usar según config_emision.php.
 *
 *   modo 'tercero'     -> Facturalaya (API de un proveedor OSE/PSE)
 *   modo 'certificado' -> EmisorSunat (emite directo a SUNAT con tu certificado, vía Greenter)
 *
 * Ambas clases exponen la misma interfaz (enviar, enviarNota, actualizarVenta...),
 * así que el resto del sistema funciona igual sin importar cuál se use.
 */
class EmisorFactory
{
    public static function crear()
    {
        $cfg = require __DIR__ . '/config_emision.php';

        if (($cfg['modo'] ?? 'tercero') === 'certificado') {
            require_once __DIR__ . '/EmisorSunat.php';
            return new EmisorSunat();
        }

        require_once __DIR__ . '/Facturalaya.php';
        return new Facturalaya();
    }

    /** Devuelve el modo actual ('tercero' | 'certificado'). */
    public static function modo(): string
    {
        $cfg = require __DIR__ . '/config_emision.php';
        return $cfg['modo'] ?? 'tercero';
    }
}
