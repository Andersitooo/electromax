<?php
/**
 * Servicio de SKU y números de serie.
 *
 * Responsabilidad:
 * Separar la generación y validación de códigos de producto de las páginas.
 *
 * Conceptos:
 *
 * SKU:
 * Código interno del producto en catálogo. Sirve para identificar el modelo.
 *
 * Número de serie:
 * Código de una unidad física específica. Dos televisores del mismo modelo
 * tienen el mismo SKU, pero cada unidad debe tener una serie distinta.
 *
 * Por qué importa:
 * En ventas, devoluciones, garantías y reemplazos se debe saber exactamente
 * qué unidad física fue vendida, devuelta o enviada como reemplazo.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

if (!class_exists('ElectroMaxSerialNumberService')) {
class ElectroMaxSerialNumberService
{
    /**
     * Genera un SKU profesional con base en la categoría.
     *
     * Ejemplo:
     * Categoría: televisores
     * Resultado posible: EMX-TEL-8F3A2B
     *
     * Este código identifica al producto o modelo dentro del catálogo.
     */
    public static function generarSKUProfesional($categoria_slug): string
    {
        $texto = preg_replace('/[^A-Za-z0-9]/', '', (string)$categoria_slug);
        $prefix = strtoupper(substr($texto, 0, 3));

        if ($prefix === '') {
            $prefix = 'GEN';
        }

        $random = strtoupper(substr(md5(uniqid('', true)), 0, 6));

        return "EMX-{$prefix}-{$random}";
    }

    /**
     * Genera una serie única para una unidad física.
     *
     * Ejemplo:
     * Marca: Samsung
     * Resultado posible: SAM-2026-X9F2A1C4
     *
     * Este código no representa el modelo completo. Representa una unidad física.
     * Por eso se usa en ventas, devoluciones y reemplazos.
     */
    public static function generarSerieUnica($marca): string
    {
        $year = date('Y');
        $brandCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', (string)$marca), 0, 3));

        if ($brandCode === '') {
            $brandCode = 'EMX';
        }

        $hash = strtoupper(substr(md5(uniqid((string)random_int(1000, 9999), true)), 0, 8));

        return "{$brandCode}-{$year}-{$hash}";
    }

    /**
     * Convierte el campo numero_serie_vendido a una lista comparable.
     *
     * El sistema puede guardar una serie como texto simple o como JSON.
     * Este método unifica ambos formatos.
     */
    public static function extraerSeries($valor): array
    {
        $valor = trim((string)$valor);

        if ($valor === '') {
            return [];
        }

        $json = json_decode($valor, true);
        $lista = is_array($json) ? $json : [$valor];

        $salida = [];

        foreach ($lista as $serie) {
            $serie = trim((string)$serie);

            if ($serie !== '') {
                $salida[] = $serie;
            }
        }

        return $salida;
    }

    /**
     * Valida que la serie devuelta por el cliente pertenezca al pedido.
     *
     * Regla:
     * El sistema busca las series vendidas en detalle_pedidos.
     * Si la serie física devuelta aparece en la venta original, se acepta.
     * Si no aparece, se considera alerta de posible fraude o error de recepción.
     */
    public static function validarSerieDevolucion($pdo, $pedido_id, $producto_id, $serie_devuelta): bool
    {
        $serieBuscada = trim(strtoupper((string)$serie_devuelta));

        if ($serieBuscada === '') {
            return false;
        }

        $stmt = $pdo->prepare("
            SELECT numero_serie_vendido
            FROM detalle_pedidos
            WHERE pedido_id = ? AND producto_id = ?
        ");
        $stmt->execute([$pedido_id, $producto_id]);

        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $valorSeries) {
            foreach (self::extraerSeries($valorSeries) as $serieVendida) {
                if (trim(strtoupper($serieVendida)) === $serieBuscada) {
                    return true;
                }
            }
        }

        return false;
    }
}
}
?>
