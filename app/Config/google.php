<?php
/**
 * Configuración centralizada de Google Identity Services.
 *
 * No se almacena Client Secret aquí.
 * El Client ID es público, pero en producción debe estar restringido por dominio
 * desde Google Cloud Console.
 */

/**
 * Configuración de Google Identity Services para ElectroMax.
 *
 * Client ID público de OAuth 2.0.
 * No colocar aquí el Client Secret.
 */

if (!defined('EMX_GOOGLE_CLIENT_ID')) {
    $envGoogleClientId = trim((string)(getenv('EMX_GOOGLE_CLIENT_ID') ?: ''));
    define('EMX_GOOGLE_CLIENT_ID', $envGoogleClientId !== '' ? $envGoogleClientId : '974067815868-8qhd20n0p5aeo3paiqp63534qpqqikj7.apps.googleusercontent.com');
}

function emxGoogleClientId() {
    return trim((string)EMX_GOOGLE_CLIENT_ID);
}

function emxGoogleActivo() {
    $clientId = emxGoogleClientId();
    return $clientId !== ''
        && stripos($clientId, 'PEGA_AQUI') === false
        && stripos($clientId, 'TU_CLIENT_ID') === false
        && stripos($clientId, '.apps.googleusercontent.com') !== false;
}
?>
