<?php
/**
 * Helper centralizado para ficha técnica ElectroMax.
 * Mantiene una sola fuente visual para ficha_tecnica.php y ficha_tecnica_pdf.php.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

if (!function_exists('emxFichaLabel')) {
    function emxFichaLabel($k) {
        $labels = [
            'pulgadas' =>'Tamaño de pantalla',
            'resolucion' =>'Resolución',
            'hdr' =>'HDR compatible',
            'smart_tv' =>'Smart TV',
            'sistema_operativo' =>'Sistema operativo',
            'puertos_hdmi' =>'Puertos HDMI',
            'puertos_usb' =>'Puertos USB',
            'wifi' =>'WiFi',
            'bluetooth' =>'Bluetooth',
            'capacidad_litros' =>'Capacidad',
            'capacidad_kg' =>'Capacidad de lavado',
            'eficiencia_energetica' =>'Eficiencia energética',
            'tecnologia' =>'Tecnología',
            'tipo_carga' =>'Tipo de carga',
            'velocidad_rpm' =>'Velocidad de centrifugado',
            'capacidad_btu' =>'Capacidad de enfriamiento',
            'cobertura_m2' =>'Cobertura aproximada',
            'gas_refrigerante' =>'Gas refrigerante',
            'dispensador_agua' =>'Dispensador de agua',
            'dispensador_hielo' =>'Dispensador de hielo',
            'tipo_panel' =>'Tipo de panel',
            'frecuencia_hz' =>'Frecuencia',
            'potencia_audio' =>'Potencia de audio',
            'control_voz' =>'Control por voz',
            'asistente' =>'Asistente compatible',
            'color' =>'Color',
            'tipo' =>'Tipo',
            'configuracion' =>'Configuración',
            'compresor' =>'Compresor',
            'programas' =>'Programas',
            'vapor' =>'Función vapor',
            'funciones' =>'Funciones',
            'imagen' =>'Imagen',
            'audio' =>'Audio',
            'video' =>'Video',
            'puertos' =>'Puertos',
            'conectividad' =>'Conectividad',
            'garantia' =>'Garantía',
            'modelo' =>'Modelo',
            'potencia' =>'Potencia',
            'voltaje' =>'Voltaje',
            'dimensiones' =>'Dimensiones',
            'peso' =>'Peso',
        ];
        $key = strtolower(trim((string)$k));
        return $labels[$key] ?? ucwords(str_replace(['_', '-'], ' ', (string)$k));
    }
}

if (!function_exists('emxFichaUnidad')) {
    function emxFichaUnidad($k) {
        $key = strtolower((string)$k);
        $units = [
            'pulgadas' =>'"',
            'capacidad_litros' =>'L',
            'capacidad_kg' =>'kg',
            'velocidad_rpm' =>'RPM',
            'capacidad_btu' =>'BTU',
            'cobertura_m2' =>'m²',
            'frecuencia_hz' =>'Hz',
            'potencia_audio' =>'W',
        ];
        return $units[$key] ?? '';
    }
}

if (!function_exists('emxFichaGrupo')) {
    function emxFichaGrupo($k) {
        $key = strtolower((string)$k);
        $map = [
            'Pantalla e imagen' =>['pulgadas','resolucion','hdr','tipo_panel','frecuencia_hz','brillo','contraste','formato_pantalla','smart_tv','imagen'],
            'Audio y video' =>['audio','potencia_audio','dolby','altavoces','video','formatos_video','sonido'],
            'Conectividad' =>['wifi','bluetooth','puertos_hdmi','puertos_usb','ethernet','conexion','conectividad','hdmi','usb','puertos'],
            'Sistema y funciones' =>['sistema_operativo','procesador','memoria','almacenamiento','funciones','apps','control_voz','asistente','smart'],
            'Capacidad y rendimiento' =>['capacidad_litros','capacidad_kg','capacidad_btu','velocidad_rpm','cobertura_m2','programas','compresor','tecnologia'],
            'Energía y eficiencia' =>['eficiencia','eficiencia_energetica','consumo','voltaje','potencia','clase_energetica'],
            'Diseño y características' =>['color','tipo','configuracion','material','acabado','dispensador_agua','dispensador_hielo','vapor','gas_refrigerante','medidas','dimensiones'],
        ];
        foreach ($map as $grupo =>$keys) {
            foreach ($keys as $needle) {
                if ($key === $needle || str_contains($key, $needle)) return $grupo;
            }
        }
        return 'Otras especificaciones';
    }
}

if (!function_exists('emxFichaIconoGrupo')) {
    function emxFichaIconoGrupo($grupo) {
        return [
            'Pantalla e imagen' =>'fa-tv',
            'Audio y video' =>'fa-volume-high',
            'Conectividad' =>'fa-wifi',
            'Sistema y funciones' =>'fa-microchip',
            'Capacidad y rendimiento' =>'fa-gauge-high',
            'Energía y eficiencia' =>'fa-bolt',
            'Diseño y características' =>'fa-layer-group',
            'Otras especificaciones' =>'fa-circle-info',
        ][$grupo] ?? 'fa-circle-info';
    }
}

if (!function_exists('emxFichaOrden')) {
    function emxFichaOrden($grupo, $key) {
        $orders = [
            'Pantalla e imagen' =>['pulgadas','resolucion','tipo_panel','frecuencia_hz','hdr','smart_tv','imagen'],
            'Audio y video' =>['potencia_audio','audio','dolby','altavoces','video','formatos_video'],
            'Conectividad' =>['wifi','bluetooth','puertos_hdmi','puertos_usb','ethernet','conexion','conectividad','puertos'],
            'Sistema y funciones' =>['sistema_operativo','procesador','memoria','almacenamiento','apps','funciones','control_voz','asistente'],
            'Capacidad y rendimiento' =>['capacidad_litros','capacidad_kg','capacidad_btu','velocidad_rpm','programas','compresor','tecnologia','cobertura_m2'],
            'Energía y eficiencia' =>['eficiencia_energetica','clase_energetica','consumo','voltaje','potencia'],
            'Diseño y características' =>['tipo','configuracion','color','material','acabado','dispensador_agua','dispensador_hielo','vapor','gas_refrigerante'],
            'Otras especificaciones' =>['modelo','garantia','peso','dimensiones'],
        ];
        $key = strtolower((string)$key);
        $list = $orders[$grupo] ?? [];
        $idx = array_search($key, $list, true);
        return $idx === false ? 999 : $idx;
    }
}

if (!function_exists('emxFichaValorTexto')) {
    function emxFichaValorTexto($v, $key = '') {
        if (is_bool($v)) return $v ? 'Sí' : 'No';
        if ($v === null || $v === '') return 'No registrado';
        if (is_array($v)) {
            $isAssoc = array_keys($v) !== range(0, count($v) - 1);
            if (!$isAssoc) return implode(', ', array_map(fn($x) =>emxFichaValorTexto($x, $key), $v));
            $parts = [];
            foreach ($v as $kk =>$vv) $parts[] = emxFichaLabel($kk) . ': ' . emxFichaValorTexto($vv, $kk);
            return implode(' · ', $parts);
        }
        $txt = trim((string)$v);
        $unit = emxFichaUnidad($key);
        if ($unit !== '' && is_numeric($txt) && !str_contains($txt, $unit)) $txt .= ' ' . $unit;
        return $txt;
    }
}

if (!function_exists('emxFichaNormalizarLista')) {
    function emxFichaNormalizarLista($v, $key = '') {
        if (is_array($v)) {
            $isAssoc = array_keys($v) !== range(0, count($v) - 1);
            if ($isAssoc) return null;
            return array_values(array_filter(array_map(fn($x) =>trim(emxFichaValorTexto($x, $key)), $v), fn($x) =>$x !== ''));
        }
        $txt = emxFichaValorTexto($v, $key);
        if (str_contains($txt, ',') && strlen($txt) <= 160) {
            $parts = array_values(array_filter(array_map('trim', explode(',', $txt)), fn($x) =>$x !== ''));
            return count($parts) >1 ? $parts : null;
        }
        return null;
    }
}

if (!function_exists('emxPrepararGruposFicha')) {
    function emxPrepararGruposFicha(array $specs) {
        $grupos = [];
        foreach ($specs as $k =>$v) {
            if ($v === null || $v === '' || $v === []) continue;
            $grupos[emxFichaGrupo($k)][$k] = $v;
        }
        foreach ($grupos as $grupo =>&$items) {
            uksort($items, fn($a, $b) =>[emxFichaOrden($grupo, $a), emxFichaLabel($a)] <=>[emxFichaOrden($grupo, $b), emxFichaLabel($b)]);
        }
        unset($items);
        return $grupos;
    }
}

if (!function_exists('emxFichaPrepararFilas')) {
    function emxFichaPrepararFilas(array $specs): array {
        $filas = [];
        foreach ($specs as $k => $v) {
            if ($v === null || $v === '' || $v === []) continue;
            $grupo = emxFichaGrupo($k);
            $filas[] = [
                'key' => (string)$k,
                'label' => emxFichaLabel($k),
                'value' => emxFichaValorTexto($v, $k),
                'grupo' => $grupo,
                'orden_grupo' => [
                    'Pantalla e imagen' => 10,
                    'Audio y video' => 20,
                    'Conectividad' => 30,
                    'Sistema y funciones' => 40,
                    'Capacidad y rendimiento' => 50,
                    'Energía y eficiencia' => 60,
                    'Diseño y características' => 70,
                    'Otras especificaciones' => 90,
                ][$grupo] ?? 90,
                'orden' => emxFichaOrden($grupo, $k),
            ];
        }
        usort($filas, fn($a, $b) =>[$a['orden_grupo'], $a['orden'], $a['label']] <=> [$b['orden_grupo'], $b['orden'], $b['label']]);
        return $filas;
    }
}

if (!function_exists('emxFichaEscape')) {
    function emxFichaEscape($valor): string {
        return htmlspecialchars((string)$valor, ENT_QUOTES, 'UTF-8');
    }
}

if (!function_exists('emxFichaAssetSrc')) {
    function emxFichaAssetSrc(?string $src, bool $pdf = false): string {
        $src = trim((string)$src);
        if ($src === '') return '';
        if (preg_match('~^https?://~i', $src)) return $src;
        if (str_starts_with($src, 'data:')) return $src;
        $src = ltrim($src, '/');
        $publicPath = defined('EMX_PUBLIC_PATH') ? EMX_PUBLIC_PATH : (defined('EMX_ROOT') ? EMX_ROOT . '/public' : dirname(__DIR__, 2) . '/public');
        $candidate = realpath($publicPath . '/' . $src);
        if ($pdf && $candidate && str_starts_with($candidate, realpath($publicPath))) {
            return 'file://' . str_replace(DIRECTORY_SEPARATOR, '/', $candidate);
        }
        return $src;
    }
}

if (!function_exists('emxRenderFichaValorPremium')) {
    function emxRenderFichaValorPremium($v, $key = '') {
        $txt = emxFichaValorTexto($v, $key);
        return '<span class="emx-spec-text">' . nl2br(emxFichaEscape($txt)) . '</span>';
    }
}

if (!function_exists('emxRenderFichaPremium')) {
    function emxRenderFichaPremium(array $specs) {
        $filas = emxFichaPrepararFilas($specs);
        if (!$filas) {
            return '<div class="emx-empty-spec"><i class="fas fa-circle-info"></i><p>No hay especificaciones técnicas registradas para este producto.</p></div>';
        }
        $html = '<div class="emx-spec-table-wrap"><table class="emx-spec-table"><thead><tr><th class="emx-col-num">#</th><th>Especificación</th><th>Detalle del producto</th></tr></thead><tbody>';
        $n = 1;
        foreach ($filas as $fila) {
            $html .= '<tr>';
            $html .= '<td class="emx-col-num"><span>' . str_pad((string)$n, 2, '0', STR_PAD_LEFT) . '</span></td>';
            $html .= '<td class="emx-spec-name"><strong>' . emxFichaEscape($fila['label']) . '</strong></td>';
            $html .= '<td class="emx-spec-value">' . nl2br(emxFichaEscape($fila['value'])) . '</td>';
            $html .= '</tr>';
            $n++;
        }
        return $html . '</tbody></table></div>';
    }
}

if (!function_exists('emxFichaDocumentoCss')) {
    function emxFichaDocumentoCss(bool $pdf = false): string {
        $base = <<<'CSS'
:root{--emx-blue:#0565d8;--emx-blue2:#0b4da2;--emx-navy:#06173a;--emx-soft:#eaf4ff;--emx-line:#d9e7f7;--emx-text:#0f1f3a;--emx-muted:#64748b;}
*{box-sizing:border-box}
html,body{margin:0;padding:0;font-family:'DejaVu Sans','Inter','Arial',sans-serif;color:var(--emx-text);background:#eaf4ff;}
.emx-ficha-page{width:100%;max-width:1120px;margin:0 auto;padding:24px;}
.emx-ficha-card{background:#fff;border:1px solid #dce8f7;border-radius:30px;overflow:hidden;box-shadow:0 28px 70px -42px rgba(6,23,58,.45);}
.emx-ficha-hero{background:linear-gradient(135deg,#dceeff 0%,#eef7ff 48%,#bfdcff 100%);border-bottom:8px solid #0875ef;padding:32px 36px;position:relative;}
.emx-hero-layout{display:grid;grid-template-columns:minmax(0,1fr) 340px;gap:30px;align-items:center;}
.emx-hero-info{min-width:0;}
.emx-ficha-logo{width:285px;max-width:100%;height:auto;display:block;margin-bottom:18px;}
.emx-kicker{font-size:12px;text-transform:uppercase;letter-spacing:.28em;font-weight:900;color:#075fca;margin:0 0 12px;}
.emx-ficha-title{font-size:34px;line-height:1.05;margin:0 0 18px;color:#071f4f;font-weight:900;letter-spacing:-.03em;}
.emx-meta-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px;margin-top:14px;}
.emx-meta-card{background:rgba(255,255,255,.72);border:1px solid #c8ddf4;border-radius:16px;padding:12px 14px;min-height:64px;}
.emx-meta-card small{display:block;text-transform:uppercase;letter-spacing:.15em;font-size:10px;color:#075fca;font-weight:900;margin-bottom:5px;}
.emx-meta-card strong{font-size:13px;color:#06173a;font-weight:900;line-height:1.25;display:block;}
.emx-product-image-box{background:rgba(255,255,255,.68);border:1px solid #c8ddf4;border-radius:26px;min-height:270px;padding:18px;display:flex;align-items:center;justify-content:center;box-shadow:0 18px 45px -32px rgba(6,23,58,.55);}
.emx-product-image-box img{max-width:100%;max-height:250px;object-fit:contain;display:block;}
.emx-product-image-empty{height:240px;width:100%;border-radius:20px;background:#f0f7ff;display:flex;align-items:center;justify-content:center;color:#9bbce7;font-size:58px;}
.emx-ficha-body{padding:34px 36px 38px;background:#fff;}
.emx-section{border:1px solid #dce8f7;border-radius:24px;background:#fff;margin-bottom:26px;overflow:hidden;}
.emx-section-pad{padding:24px 26px;}
.emx-section-label{font-size:11px;text-transform:uppercase;letter-spacing:.24em;color:#075fca;font-weight:900;margin:0 0 6px;}
.emx-section-title{font-size:26px;line-height:1.1;color:#06173a;font-weight:900;margin:0 0 8px;letter-spacing:-.02em;}
.emx-description{font-size:14px;line-height:1.75;color:#334155;margin:10px 0 0;white-space:pre-line;}
.emx-spec-head{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin-bottom:14px;}
.emx-spec-count{border-radius:999px;background:#eef6ff;border:1px solid #cfe4ff;color:#075fca;font-size:12px;font-weight:900;padding:9px 13px;white-space:nowrap;}
.emx-spec-table-wrap{overflow:hidden;border:1px solid #cdddf0;border-radius:18px;background:#fff;}
table.emx-spec-table{width:100%;border-collapse:collapse;table-layout:fixed;}
.emx-spec-table th{background:#071f4f;color:#fff;text-align:left;font-size:12px;text-transform:uppercase;letter-spacing:.12em;padding:14px 16px;border-right:1px solid rgba(255,255,255,.14);}
.emx-spec-table td{padding:15px 16px;border-top:1px solid #dce8f7;border-right:1px solid #e5edf7;vertical-align:top;font-size:13px;line-height:1.55;color:#1e293b;word-break:break-word;}
.emx-spec-table th:last-child,.emx-spec-table td:last-child{border-right:0;}
.emx-spec-table tr:nth-child(even) td{background:#f8fbff;}
.emx-spec-table tr:hover td{background:#eef6ff;}
.emx-col-num{width:68px;text-align:center!important;}
.emx-col-num span{display:inline-flex;width:34px;height:34px;border-radius:10px;align-items:center;justify-content:center;background:#0875ef;color:#fff;font-weight:900;font-size:12px;}
.emx-spec-name strong{display:block;color:#071f4f;font-weight:900;}
.emx-spec-value{font-weight:650;}
.emx-empty-spec{border:1px dashed #bdd4ee;background:#f8fbff;border-radius:18px;padding:28px;text-align:center;color:#64748b;font-weight:800;}
.emx-empty-spec i{display:block;color:#0875ef;font-size:28px;margin-bottom:8px;}
.emx-ficha-footer{padding:18px 36px;background:#06173a;color:#c7d7ea;font-size:11px;line-height:1.55;}
.emx-ficha-footer strong{color:#fff;}
.emx-actions{max-width:1120px;margin:0 auto 18px;padding:0 24px;display:flex;justify-content:space-between;gap:12px;flex-wrap:wrap;}
.emx-btn{display:inline-flex;align-items:center;gap:9px;border-radius:14px;text-decoration:none;font-weight:900;padding:12px 16px;border:1px solid #d7e4f3;background:#fff;color:#0f1f3a;}
.emx-btn-primary{background:#0875ef;border-color:#0875ef;color:#fff;}
@media(max-width:900px){.emx-ficha-page{padding:14px}.emx-ficha-hero,.emx-ficha-body{padding:24px 18px}.emx-hero-layout{grid-template-columns:1fr;gap:20px}.emx-meta-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.emx-ficha-title{font-size:28px}.emx-spec-head{display:block}.emx-spec-count{display:inline-block;margin-top:10px}.emx-spec-table th,.emx-spec-table td{padding:12px 10px;font-size:12px}.emx-col-num{width:54px}.emx-col-num span{width:30px;height:30px}.emx-product-image-box{min-height:210px}.emx-product-image-box img{max-height:200px}}
@media(max-width:560px){.emx-meta-grid{grid-template-columns:1fr}.emx-ficha-title{font-size:24px}.emx-ficha-logo{width:230px}.emx-product-image-box{min-height:180px}.emx-product-image-box img{max-height:170px}.emx-spec-table th:nth-child(1),.emx-spec-table td:nth-child(1){display:none}.emx-spec-table th,.emx-spec-table td{font-size:12px}.emx-section-title{font-size:22px}.emx-section-pad{padding:18px 14px}.emx-ficha-footer{padding:16px 18px}}
@media print{.no-print,.emx-actions{display:none!important}html,body{background:#fff}.emx-ficha-page{max-width:none;width:100%;padding:0}.emx-ficha-card{box-shadow:none;border-radius:0;border:0}.emx-ficha-hero{border-bottom:6px solid #0875ef;-webkit-print-color-adjust:exact;print-color-adjust:exact}.emx-spec-table th,.emx-col-num span{background:#071f4f!important;color:#fff!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}.emx-spec-table tr:nth-child(even) td{background:#f8fbff!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}.emx-ficha-footer{background:#06173a!important;color:#c7d7ea!important;-webkit-print-color-adjust:exact;print-color-adjust:exact}}
CSS;
        if ($pdf) {
            $base .= "\n@page{size:A4 portrait;margin:8mm;}html,body{background:#fff;font-size:11.5px}.emx-ficha-page{max-width:none;width:100%;padding:0}.emx-ficha-card{border-radius:0;box-shadow:none;border:0}.emx-ficha-hero{padding:22px 24px;border-bottom:6px solid #0875ef}.emx-hero-layout{grid-template-columns:minmax(0,1fr) 250px;gap:22px}.emx-ficha-logo{width:235px;margin-bottom:12px}.emx-kicker{font-size:9.5px;margin-bottom:8px}.emx-ficha-title{font-size:22px;margin-bottom:10px;line-height:1.08}.emx-meta-grid{grid-template-columns:repeat(2,1fr);gap:8px}.emx-meta-card{padding:8px 9px;border-radius:12px;min-height:46px}.emx-meta-card small{font-size:8px}.emx-meta-card strong{font-size:10.5px}.emx-product-image-box{min-height:205px;padding:12px;border-radius:18px}.emx-product-image-box img{max-height:190px}.emx-product-image-empty{height:185px;font-size:44px}.emx-ficha-body{padding:20px 24px}.emx-section{margin-bottom:16px;border-radius:16px}.emx-section-pad{padding:15px 17px}.emx-section-title{font-size:18px}.emx-description{font-size:10.5px;line-height:1.55}.emx-spec-head{margin-bottom:10px}.emx-spec-count{font-size:9.5px;padding:6px 9px}.emx-spec-table th{font-size:8.5px;padding:8px 9px}.emx-spec-table td{font-size:9.5px;padding:7px 9px;line-height:1.36}.emx-col-num{width:42px}.emx-col-num span{width:23px;height:23px;border-radius:7px;font-size:8px}.emx-ficha-footer{padding:12px 24px;font-size:8.8px;page-break-inside:avoid}.emx-section,.emx-product-image-box,.emx-meta-card{page-break-inside:avoid}.emx-spec-table tr{page-break-inside:avoid;page-break-after:auto}";
        }
        return $base;
    }
}

if (!function_exists('emxFichaRenderDocumento')) {
    function emxFichaRenderDocumento(array $producto, array $specs, array $options = []): string {
        $pdf = !empty($options['pdf']);
        $standalone = $options['standalone'] ?? true;
        $id = (string)($producto['id'] ?? '');
        $logo = $pdf && defined('EMX_EMPRESA_LOGO_PDF') ? EMX_EMPRESA_LOGO_PDF : (defined('EMX_EMPRESA_LOGO') ? EMX_EMPRESA_LOGO : 'assets/electromax_logo.png');
        $logoSrc = emxFichaAssetSrc($logo, $pdf);
        $imgSrc = emxFichaAssetSrc($producto['imagen'] ?? ($producto['imagen_principal'] ?? ''), $pdf);
        $filas = emxFichaPrepararFilas($specs);
        $datosProducto = [];
        foreach ([
            'Modelo' => $producto['modelo'] ?? '',
            'Marca' => $producto['marca'] ?? '',
            'Categoría' => $producto['categoria'] ?? '',
            'Precio base' => isset($producto['precio_base']) ? '$' . number_format((float)$producto['precio_base'], 2) : '',
        ] as $label => $value) {
            if (trim((string)$value) !== '') $datosProducto[] = [$label, $value];
        }
        $titulo = trim((string)($producto['nombre'] ?? 'Ficha técnica'));
        $descripcion = trim((string)($producto['descripcion_corta'] ?? ''));
        $footerNombre = defined('EMX_EMPRESA_NOMBRE') ? EMX_EMPRESA_NOMBRE : 'ElectroMax';
        $footerDireccion = defined('EMX_EMPRESA_DIRECCION') ? EMX_EMPRESA_DIRECCION : 'Matriz Babahoyo, Los Ríos, Ecuador';
        $footerEmail = defined('EMX_EMPRESA_EMAIL') ? EMX_EMPRESA_EMAIL : 'soporte@electromax.com';
        $footerTelefono = defined('EMX_EMPRESA_TELEFONO') ? EMX_EMPRESA_TELEFONO : '04-273-0000';
        $volver = $options['volver'] ?? ('producto.php?id=' . urlencode($id));
        $pdfUrl = 'ficha_tecnica_pdf.php?id=' . urlencode($id);

        ob_start();
        if ($standalone): ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ficha Técnica - <?= emxFichaEscape($titulo) ?></title>
    <?php if (!$pdf): ?>
        <script src="https://cdn.tailwindcss.com"></script>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <?php endif; ?>
    <style><?= emxFichaDocumentoCss($pdf) ?></style>
</head>
<body>
        <?php endif; ?>
        <?php if (!$pdf): ?>
        <div class="emx-actions no-print">
            <a href="<?= emxFichaEscape($volver) ?>" class="emx-btn"><i class="fas fa-arrow-left"></i>Volver</a>
            <a href="<?= emxFichaEscape($pdfUrl) ?>" class="emx-btn emx-btn-primary"><i class="fas fa-file-pdf"></i>Descargar / imprimir PDF</a>
        </div>
        <?php endif; ?>
        <div class="emx-ficha-page">
            <article class="emx-ficha-card">
                <header class="emx-ficha-hero">
                    <div class="emx-hero-layout">
                        <div class="emx-hero-info">
                            <img src="<?= emxFichaEscape($logoSrc) ?>" alt="ElectroMax" class="emx-ficha-logo">
                            <p class="emx-kicker">Ficha técnica oficial</p>
                            <h1 class="emx-ficha-title"><?= emxFichaEscape($titulo) ?></h1>
                            <?php if ($datosProducto): ?>
                            <div class="emx-meta-grid">
                                <?php foreach ($datosProducto as [$label, $value]): ?>
                                <div class="emx-meta-card">
                                    <small><?= emxFichaEscape($label) ?></small>
                                    <strong><?= emxFichaEscape($value) ?></strong>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <div class="emx-product-image-box">
                            <?php if ($imgSrc !== ''): ?>
                                <img src="<?= emxFichaEscape($imgSrc) ?>" alt="<?= emxFichaEscape($titulo) ?>">
                            <?php else: ?>
                                <div class="emx-product-image-empty"><i class="fas fa-box"></i></div>
                            <?php endif; ?>
                        </div>
                    </div>
                </header>
                <main class="emx-ficha-body">
                    <?php if ($descripcion !== ''): ?>
                    <section class="emx-section">
                        <div class="emx-section-pad">
                            <p class="emx-section-label">Resumen del producto</p>
                            <h2 class="emx-section-title">Descripción comercial registrada</h2>
                            <p class="emx-description"><?= nl2br(emxFichaEscape($descripcion)) ?></p>
                        </div>
                    </section>
                    <?php endif; ?>
                    <section class="emx-section">
                        <div class="emx-section-pad">
                            <div class="emx-spec-head">
                                <div>
                                    <p class="emx-section-label">Detalle técnico</p>
                                    <h2 class="emx-section-title">Especificaciones técnicas</h2>
                                    <p class="emx-description" style="margin-top:4px">Información organizada en una sola tabla cuadriculada para lectura, revisión e impresión.</p>
                                </div>
                                <span class="emx-spec-count"><?= count($filas) ?> especificación(es)</span>
                            </div>
                            <?= emxRenderFichaPremium($specs) ?>
                        </div>
                    </section>
                </main>
                <footer class="emx-ficha-footer">
                    <strong><?= emxFichaEscape($footerNombre) ?></strong> · <?= emxFichaEscape($footerDireccion) ?> · <?= emxFichaEscape($footerEmail) ?> · <?= emxFichaEscape($footerTelefono) ?><br>
                    Documento generado con la información registrada en el catálogo del sistema.
                </footer>
            </article>
        </div>
        <?php if ($standalone): ?>
</body>
</html>
        <?php endif;
        return ob_get_clean();
    }
}
?>
