<?php

namespace App\Services;

class ClienteRuc
{
    public function buscarPorRuc(string $ruc): array
    {
        $ruc = trim($ruc);

        if ($ruc === '') {
            return ['success' => false, 'mensaje' => 'Debe ingresar un RUC.'];
        }

        $url = 'https://ruc.sun.com.py/api/ruc/' . urlencode($ruc);

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPGET => true,
        ]);

        $respuesta = curl_exec($curl);
        $httpCode = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($respuesta === false) {
            return [
                'success' => false,
                'mensaje' => 'No fue posible conectar con el servicio de consulta de RUC.',
                'error' => $error,
            ];
        }

        if ($httpCode === 404) {
            return ['success' => false, 'mensaje' => 'No se encontró información para el RUC ingresado.'];
        }

        if ($httpCode < 200 || $httpCode >= 300) {
            return ['success' => false, 'mensaje' => 'Error al consultar el servicio de RUC.'];
        }

        $datos = json_decode($respuesta, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['success' => false, 'mensaje' => 'La respuesta del servicio no tiene un formato válido.'];
        }

        return ['success' => true, 'datos' => $datos];
    }
}
