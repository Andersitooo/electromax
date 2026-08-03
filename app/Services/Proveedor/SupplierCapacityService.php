<?php
/**
 * Servicio de capacidad de proveedor.
 *
 * Responsabilidad:
 * Separar las reglas de producción, descuentos por volumen del proveedor
 * y estimaciones básicas de abastecimiento.
 *
 * Este servicio no dibuja HTML y no procesa formularios directamente.
 * Solo calcula y normaliza reglas de negocio.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

if (!class_exists('ElectroMaxSupplierCapacityService')) {
class ElectroMaxSupplierCapacityService
{
    /**
     * Normaliza los rangos de descuentos por volumen que envía el proveedor.
     *
     * Cada rango tiene:
     * - cantidad_min: desde cuántas unidades aplica.
     * - cantidad_max: hasta cuántas unidades aplica. Puede ser null para ilimitado.
     * - descuento: porcentaje de descuento.
     *
     * Este método evita guardar rangos vacíos o inválidos.
     */
    public static function normalizarRangosDescuento(array $rangosPost): array
    {
        $rangos = [];

        foreach ($rangosPost as $rango) {
            if (!is_array($rango)) {
                continue;
            }

            $cantidadMin = (int)($rango['cantidad_min'] ?? 0);
            $cantidadMaxRaw = $rango['cantidad_max'] ?? '';
            $descuento = (float)($rango['descuento'] ?? 0);

            if ($cantidadMin <= 0 || $descuento <= 0) {
                continue;
            }

            $cantidadMax = ($cantidadMaxRaw === '' || strtolower((string)$cantidadMaxRaw) === 'ilimitado')
                ? null
                : (int)$cantidadMaxRaw;

            $rangos[] = [
                'cantidad_min' => $cantidadMin,
                'cantidad_max' => $cantidadMax,
                'descuento' => $descuento,
            ];
        }

        return $rangos;
    }

    /**
     * Calcula qué descuento del proveedor aplica según cantidad.
     *
     * Ejemplo:
     * Rango: desde 50 unidades, descuento 8 por ciento.
     * Si la propuesta del proveedor ofrece 60 unidades, aplica 8 por ciento.
     *
     * Nota:
     * Este cálculo no cambia automáticamente el precio manual que escribe
     * el proveedor. Solo informa qué descuento configurado corresponde.
     */
    public static function calcularDescuentoPorRango($descuentosJson, int $cantidad): float
    {
        $cantidad = max(1, (int)$cantidad);
        $rangos = json_decode($descuentosJson ?: '[]', true);

        if (!is_array($rangos)) {
            return 0.0;
        }

        $mejor = 0.0;

        foreach ($rangos as $rango) {
            if (!is_array($rango)) {
                continue;
            }

            $min = (int)($rango['cantidad_min'] ?? 0);
            $maxRaw = $rango['cantidad_max'] ?? null;
            $max = ($maxRaw === null || $maxRaw === '' || strtolower((string)$maxRaw) === 'ilimitado')
                ? null
                : (int)$maxRaw;

            $descuento = (float)($rango['descuento'] ?? 0);

            if ($min <= 0 || $descuento <= 0) {
                continue;
            }

            $cantidadDentroDelRango = $cantidad >= $min && ($max === null || $cantidad <= $max);

            if ($cantidadDentroDelRango) {
                $mejor = max($mejor, $descuento);
            }
        }

        return $mejor;
    }

    /**
     * Calcula una estimación simple de días de producción.
     *
     * Regla:
     * Si el proveedor produce 20 unidades al día y se piden 45,
     * se necesitan 3 días de producción.
     *
     * Se usa ceil porque una fracción de día operativo cuenta como día adicional.
     */
    public static function calcularDiasProduccion(int $cantidadSolicitada, int $capacidadDiaria): int
    {
        $cantidadSolicitada = max(1, $cantidadSolicitada);
        $capacidadDiaria = max(1, $capacidadDiaria);

        return (int)ceil($cantidadSolicitada / $capacidadDiaria);
    }

    /**
     * Calcula tiempo estimado de transporte por distancia.
     *
     * Fórmula:
     * horas = distancia / velocidad
     *
     * Luego se redondea a días operativos.
     */
    public static function calcularDiasTransporte(float $distanciaKm, float $velocidadPromedioKmh): int
    {
        $distanciaKm = max(0.0, $distanciaKm);
        $velocidadPromedioKmh = max(1.0, $velocidadPromedioKmh);

        $horas = $distanciaKm / $velocidadPromedioKmh;

        return max(1, (int)ceil($horas / 24));
    }
}
}
?>
