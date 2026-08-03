<?php
/**
 * Servicio de precios y descuentos de ElectroMax.
 *
 * Responsabilidad de este archivo:
 * Centralizar las reglas que calculan el precio final de un producto
 * cuando el cliente compra desde carrito o checkout.
 *
 * Por qué se separó:
 * Antes estos cálculos estaban dentro de funciones auxiliares. Eso funciona,
 * pero se vuelve difícil de explicar y mantener. En esta fase se mueven a un
 * servicio para que la regla de negocio quede aislada de la vista.
 *
 * Regla principal de cálculo:
 *
 * 1. Se toma el precio base sin IVA.
 * 2. Se suma el IVA para obtener el precio visible al cliente.
 * 3. Se aplica el descuento normal del producto si está activo por fecha.
 * 4. Se aplica el descuento por volumen si la cantidad cae dentro de un rango.
 * 5. Se aplica el descuento de membresía o plan si existen funciones de planes.
 * 6. Se devuelve un arreglo con el detalle del cálculo para que el frontend
 *    pueda mostrar el precio, el ahorro y el descuento aplicado.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

if (!class_exists('ElectroMaxPricingService')) {
class ElectroMaxPricingService
{
    /**
     * Normaliza porcentajes guardados en formatos distintos.
     *
     * Motivo:
     * En algunos sistemas el 10 por ciento puede guardarse como 10.
     * En otros casos puede guardarse como 0.10.
     *
     * Ejemplos:
     * - 10   se interpreta como 10 por ciento.
     * - 0.10 se interpreta como 10 por ciento.
     * - 0.5  se interpreta como 50 por ciento.
     */
    public static function normalizarPorcentaje($valor): float
    {
        $valor = (float)$valor;

        if ($valor > 0 && $valor <= 1) {
            return round($valor * 100, 2);
        }

        return round($valor, 2);
    }

    /**
     * Determina si el descuento normal del producto está vigente.
     *
     * Reglas:
     * - Si no hay descuento, devuelve 0.
     * - Si el descuento tiene fecha de inicio futura, todavía no aplica.
     * - Si el descuento tiene fecha de fin vencida, ya no aplica.
     * - Si está dentro del rango de fechas, devuelve el porcentaje.
     */
    public static function descuentoProductoActivoPct(array $producto): float
    {
        $pct = self::normalizarPorcentaje($producto['descuento_porcentaje'] ?? 0);

        if ($pct <= 0) {
            return 0.0;
        }

        $hoy = date('Y-m-d');
        $desde = $producto['descuento_desde'] ?? null;
        $hasta = $producto['descuento_hasta'] ?? null;

        if ($desde && $hoy < $desde) {
            return 0.0;
        }

        if ($hasta && $hoy > $hasta) {
            return 0.0;
        }

        return min(100.0, max(0.0, $pct));
    }

    /**
     * Busca el mejor descuento por volumen para una cantidad.
     *
     * Entrada esperada:
     * productos.descuentos_volumen_rangos en JSON.
     *
     * Ejemplo de rangos:
     * [
     *   {"cantidad_min": 5, "cantidad_max": 10, "descuento": 10},
     *   {"cantidad_min": 11, "cantidad_max": null, "descuento": 15}
     * ]
     *
     * Regla:
     * Si la cantidad comprada cae dentro de un rango, se aplica ese descuento.
     * Si hay más de un rango compatible, se usa el mayor descuento encontrado.
     */
    public static function descuentoVolumenProducto($rangosJson, int $cantidad): array
    {
        $cantidad = max(1, (int)$cantidad);
        $rangos = json_decode($rangosJson ?: '[]', true);

        if (!is_array($rangos) || empty($rangos)) {
            return [
                'descuento' => 0.0,
                'etiqueta' => '',
                'rango' => null
            ];
        }

        $mejor = [
            'descuento' => 0.0,
            'etiqueta' => '',
            'rango' => null
        ];

        foreach ($rangos as $rango) {
            if (!is_array($rango)) {
                continue;
            }

            $min = (int)($rango['cantidad_min'] ?? 0);
            $maxRaw = $rango['cantidad_max'] ?? null;
            $max = ($maxRaw === null || $maxRaw === '' || strtolower((string)$maxRaw) === 'ilimitado')
                ? null
                : (int)$maxRaw;

            $descuento = self::normalizarPorcentaje($rango['descuento'] ?? 0);

            if ($min <= 0 || $descuento <= 0) {
                continue;
            }

            $cantidadDentroDelRango = $cantidad >= $min && ($max === null || $cantidad <= $max);

            if ($cantidadDentroDelRango && $descuento >= $mejor['descuento']) {
                $mejor = [
                    'descuento' => min(100.0, $descuento),
                    'etiqueta' => trim((string)($rango['etiqueta'] ?? '')),
                    'rango' => [
                        'cantidad_min' => $min,
                        'cantidad_max' => $max,
                        'descuento' => min(100.0, $descuento),
                    ],
                ];
            }
        }

        return $mejor;
    }

    /**
     * Calcula el precio final de un producto para carrito o checkout.
     *
     * Este método es la regla central de precios para el cliente.
     *
     * Fórmula general:
     *
     * precio_con_iva = precio_base + IVA
     *
     * Luego se aplican descuentos secuenciales:
     *
     * precio = precio_con_iva
     * precio = precio - descuento normal del producto
     * precio = precio - descuento por volumen
     * precio = precio - descuento por plan o membresía
     *
     * Importante:
     * Los descuentos son secuenciales, no se suman directamente.
     *
     * Ejemplo:
     * Precio con IVA: 100
     * Descuento producto: 10 por ciento
     * Precio queda: 90
     * Descuento volumen: 5 por ciento
     * Precio queda: 85.50
     *
     * No sería correcto restar 15 por ciento directo al precio inicial,
     * porque eso daría 85.00. Por eso aquí se calcula paso por paso.
     */
    public static function calcularPrecioProductoCarrito($pdo, array $producto, int $cantidad, $usuario_id = null): array
    {
        $cantidad = max(1, (int)$cantidad);

        $iva = (float)($producto['iva_porcentaje'] ?? 15);
        $precioBase = (float)($producto['precio_base'] ?? 0);

        // Precio visible antes de descuentos. El cliente normalmente ve precios con IVA.
        $precioConIva = $precioBase * (1 + ($iva / 100));

        // Descuento general del producto, por ejemplo una oferta temporal.
        $descuentoProducto = self::descuentoProductoActivoPct($producto);

        // Descuento por cantidad comprada, por ejemplo desde 5 unidades.
        $volumen = self::descuentoVolumenProducto($producto['descuentos_volumen_rangos'] ?? '[]', $cantidad);
        $descuentoVolumen = (float)($volumen['descuento'] ?? 0);

        // Se inicia desde el precio con IVA.
        $precio = $precioConIva;

        // Primer descuento: oferta normal del producto.
        if ($descuentoProducto > 0) {
            $precio *= (1 - ($descuentoProducto / 100));
        }

        // Segundo descuento: descuento por volumen o cantidad.
        if ($descuentoVolumen > 0) {
            $precio *= (1 - ($descuentoVolumen / 100));
        }

        // Se guarda el precio antes del plan para calcular cuánto descontó la membresía.
        $precioAntesPlan = $precio;

        // Tercer descuento: beneficios por plan o membresía.
        // Se llama solo si esas funciones existen, para no romper páginas donde no se cargaron planes.
        if ($usuario_id && function_exists('obtenerBeneficiosUsuario') && function_exists('aplicarDescuentoPlan')) {
            try {
                $beneficios = obtenerBeneficiosUsuario($pdo, $usuario_id);
                if (!empty($beneficios)) {
                    $precio = aplicarDescuentoPlan($precio, $beneficios);
                }
            } catch (Throwable $e) {
                // La compra no debe bloquearse si falla el cálculo de beneficios.
                // En ese caso se conserva el precio calculado antes del plan.
            }
        }

        // Se evita precio cero por seguridad de cálculo y facturación.
        $precioFinal = max(0.01, (float)$precio);

        $descuentoTotalPct = $precioConIva > 0
            ? round((($precioConIva - $precioFinal) / $precioConIva) * 100, 2)
            : 0;

        $descuentoPlanPct = $precioAntesPlan > 0 && $precioFinal < $precioAntesPlan
            ? round((($precioAntesPlan - $precioFinal) / $precioAntesPlan) * 100, 2)
            : 0;

        return [
            'iva' => $iva,
            'precio_base' => round($precioBase, 2),
            'precio_con_iva' => round($precioConIva, 2),
            'precio_final' => round($precioFinal, 2),
            'descuento_producto' => round($descuentoProducto, 2),
            'descuento_volumen' => round($descuentoVolumen, 2),
            'descuento_plan' => round($descuentoPlanPct, 2),
            'descuento_total_porcentaje' => round($descuentoTotalPct, 2),
            'rango_volumen' => $volumen['rango'] ?? null,
            'rango_volumen_label' => $volumen['etiqueta'] ?? '',
            'tiene_descuento_volumen' => $descuentoVolumen > 0,
        ];
    }
}
}
?>
