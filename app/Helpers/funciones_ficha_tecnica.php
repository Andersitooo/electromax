<?php
/**
 * Funciones de ficha técnica ElectroMax.
 *
 * La vista HTML y el PDF usan este mismo archivo para conservar colores,
 * orden, textos y estructura visual. La intención es que ficha_tecnica.php y
 * ficha_tecnica_pdf.php se vean lo más iguales posible.
 */

if (!function_exists('emxFichaLabel')) {
    function emxFichaLabel($key) {
        $labels = [
            'pulgadas' => 'Tamaño de pantalla',
            'resolucion' => 'Resolución',
            'hdr' => 'HDR compatible',
            'smart_tv' => 'Smart TV',
            'sistema_operativo' => 'Sistema operativo',
            'puertos_hdmi' => 'Puertos HDMI',
            'puertos_usb' => 'Puertos USB',
            'wifi' => 'WiFi',
            'bluetooth' => 'Bluetooth',
            'capacidad_litros' => 'Capacidad',
            'capacidad_kg' => 'Capacidad de lavado',
            'eficiencia_energetica' => 'Eficiencia energética',
            'clase_energetica' => 'Clase energética',
            'tecnologia' => 'Tecnología',
            'tipo_carga' => 'Tipo de carga',
            'velocidad_rpm' => 'Velocidad de centrifugado',
            'capacidad_btu' => 'Capacidad BTU',
            'cobertura_m2' => 'Cobertura recomendada',
            'color' => 'Color',
            'material' => 'Material',
            'tipo' => 'Tipo',
            'configuracion' => 'Configuración',
            'compresor' => 'Compresor',
            'programas' => 'Programas',
            'vapor' => 'Función vapor',
            'funciones' => 'Funciones',
            'modelo' => 'Modelo',
            'voltaje' => 'Voltaje',
            'potencia' => 'Potencia',
            'consumo' => 'Consumo',
            'dimensiones' => 'Dimensiones',
            'medidas' => 'Medidas',
            'peso' => 'Peso',
            'peso_kg' => 'Peso',
            'gas_refrigerante' => 'Gas refrigerante',
            'dispensador_agua' => 'Dispensador de agua',
            'dispensador_hielo' => 'Dispensador de hielo',
            'acabado' => 'Acabado',
            'garantia' => 'Garantía',
        ];
        $key = strtolower(trim((string)$key));
        return $labels[$key] ?? mb_convert_case(str_replace('_', ' ', (string)$key), MB_CASE_TITLE, 'UTF-8');
    }
}

if (!function_exists('emxFichaUnidad')) {
    function emxFichaUnidad($key) {
        $units = [
            'pulgadas' => '"',
            'capacidad_litros' => 'L',
            'capacidad_kg' => 'kg',
            'velocidad_rpm' => 'RPM',
            'capacidad_btu' => 'BTU',
            'cobertura_m2' => 'm²',
            'frecuencia_hz' => 'Hz',
            'potencia_audio' => 'W',
            'peso_kg' => 'kg',
        ];
        return $units[strtolower((string)$key)] ?? '';
    }
}

if (!function_exists('emxFichaGrupo')) {
    function emxFichaGrupo($k) {
        $key = strtolower((string)$k);
        $map = [
            'Pantalla e imagen' => ['pulgadas','resolucion','hdr','tipo_panel','frecuencia_hz','brillo','contraste','formato_pantalla','smart_tv'],
            'Audio y video' => ['audio','potencia_audio','dolby','altavoces','video','formatos_video'],
            'Conectividad' => ['wifi','bluetooth','puertos_hdmi','puertos_usb','ethernet','conexion','conectividad','hdmi','usb'],
            'Sistema y funciones' => ['sistema_operativo','procesador','memoria','almacenamiento','funciones','apps','control_voz','asistente','smart'],
            'Capacidad y rendimiento' => ['capacidad_litros','capacidad_kg','capacidad_btu','velocidad_rpm','cobertura_m2','programas','compresor','tecnologia'],
            'Energía y eficiencia' => ['eficiencia','eficiencia_energetica','consumo','voltaje','potencia','clase_energetica'],
            'Diseño y características' => ['color','tipo','configuracion','material','acabado','dispensador_agua','dispensador_hielo','vapor','gas_refrigerante','medidas','dimensiones','peso'],
        ];
        foreach ($map as $grupo => $keys) {
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
            'Pantalla e imagen' => 'fa-tv',
            'Audio y video' => 'fa-volume-high',
            'Conectividad' => 'fa-wifi',
            'Sistema y funciones' => 'fa-microchip',
            'Capacidad y rendimiento' => 'fa-gauge-high',
            'Energía y eficiencia' => 'fa-bolt',
            'Diseño y características' => 'fa-layer-group',
            'Otras especificaciones' => 'fa-circle-info',
        ][$grupo] ?? 'fa-circle-info';
    }
}

if (!function_exists('emxFichaOrden')) {
    function emxFichaOrden($grupo, $key) {
        $orders = [
            'Pantalla e imagen' => ['pulgadas','resolucion','tipo_panel','frecuencia_hz','hdr','smart_tv','imagen'],
            'Audio y video' => ['potencia_audio','audio','dolby','altavoces','video','formatos_video'],
            'Conectividad' => ['wifi','bluetooth','puertos_hdmi','puertos_usb','ethernet','conexion','conectividad','puertos'],
            'Sistema y funciones' => ['sistema_operativo','procesador','memoria','almacenamiento','apps','funciones','control_voz','asistente'],
            'Capacidad y rendimiento' => ['capacidad_litros','capacidad_kg','capacidad_btu','velocidad_rpm','programas','compresor','tecnologia','cobertura_m2'],
            'Energía y eficiencia' => ['eficiencia_energetica','clase_energetica','consumo','voltaje','potencia'],
            'Diseño y características' => ['tipo','configuracion','color','material','acabado','dispensador_agua','dispensador_hielo','vapor','gas_refrigerante','dimensiones','medidas','peso'],
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
            if (!$isAssoc) return implode(', ', array_map(fn($x) => emxFichaValorTexto($x, $key), $v));
            $parts = [];
            foreach ($v as $kk => $vv) $parts[] = emxFichaLabel($kk) . ': ' . emxFichaValorTexto($vv, $kk);
            return implode(' · ', $parts);
        }
        $txt = trim((string)$v);
        $unit = emxFichaUnidad($key);
        if ($unit !== '' && is_numeric($txt) && !str_contains($txt, $unit)) $txt .= ' ' . $unit;
        return $txt;
    }
}

if (!function_exists('emxPrepararGruposFicha')) {
    function emxPrepararGruposFicha(array $specs) {
        $grupos = [];
        foreach ($specs as $k => $v) {
            if ($v === null || $v === '' || $v === []) continue;
            $grupos[emxFichaGrupo($k)][$k] = $v;
        }
        foreach ($grupos as $grupo => &$items) {
            uksort($items, fn($a, $b) => [emxFichaOrden($grupo, $a), emxFichaLabel($a)] <=> [emxFichaOrden($grupo, $b), emxFichaLabel($b)]);
        }
        unset($items);
        return $grupos;
    }
}

if (!function_exists('emxFichaContarSpecs')) {
    function emxFichaContarSpecs(array $grupos): int {
        $n = 0;
        foreach ($grupos as $items) $n += count($items);
        return $n;
    }
}

if (!function_exists('emxFichaPublicPath')) {
    function emxFichaPublicPath(string $url): string {
        $url = trim($url);
        if ($url === '') return '';
        if (preg_match('/^https?:\/\//i', $url)) return $url;
        $url = ltrim($url, '/');
        if (defined('EMX_PUBLIC_PATH') && is_file(EMX_PUBLIC_PATH . '/' . $url)) return EMX_PUBLIC_PATH . '/' . $url;
        if (defined('EMX_ROOT') && is_file(EMX_ROOT . '/' . $url)) return EMX_ROOT . '/' . $url;
        if (defined('EMX_PUBLIC_PATH') && is_file(EMX_PUBLIC_PATH . '/' . basename($url))) return EMX_PUBLIC_PATH . '/' . basename($url);
        return $url;
    }
}

if (!function_exists('emxFichaDataUri')) {
    function emxFichaDataUri(string $path): string {
        $path = emxFichaPublicPath($path);
        if ($path === '' || preg_match('/^https?:\/\//i', $path) || !is_file($path)) return $path;
        $mime = mime_content_type($path) ?: 'image/png';
        return 'data:' . $mime . ';base64,' . base64_encode(file_get_contents($path));
    }
}

if (!function_exists('emxFichaCss')) {
    function emxFichaCss(bool $pdf = false): string {
        $pageCss = $pdf ? '@page{margin:22px 22px 26px 22px;} body{margin:0;}' : 'body{margin:0;padding:28px 16px;}';
        return <<<CSS
*{box-sizing:border-box}
{$pageCss}
body{font-family:'DejaVu Sans','Inter',Arial,sans-serif;background:#eef5ff;color:#0f172a;font-size:13px;line-height:1.55}
.no-print{display:flex;justify-content:space-between;align-items:center;gap:14px;max-width:1080px;margin:0 auto 18px auto;flex-wrap:wrap}
.no-print a,.no-print button{font:700 14px Arial,sans-serif;text-decoration:none;border-radius:12px;padding:10px 15px;border:1px solid #dbe7f7;background:#fff;color:#334155;cursor:pointer}
.no-print .btn-pdf{background:#0b63ce;color:#fff;border-color:#0b63ce}
.ficha-page{max-width:1080px;margin:0 auto;background:#fff;border:1px solid #dbe7f7;border-radius:26px;overflow:hidden;box-shadow:0 28px 70px -42px rgba(15,23,42,.45)}
.ficha-header{background:linear-gradient(135deg,#dceeff 0%,#eef7ff 55%,#c4dcff 100%);border-bottom:8px solid #0b72e7;padding:30px 34px 28px 34px;position:relative}
.ficha-brand-row{display:table;width:100%;table-layout:fixed}
.ficha-brand-main{display:table-cell;vertical-align:middle;width:68%;padding-right:24px}
.ficha-product-media{display:table-cell;vertical-align:middle;width:32%;text-align:center}
.ficha-logo{display:block;max-width:280px;max-height:82px;margin:0 0 12px 0;object-fit:contain}
.ficha-kicker{font-size:11px;letter-spacing:.23em;text-transform:uppercase;font-weight:900;color:#0759b8;margin:0 0 8px 0}
.ficha-title{font-size:32px;line-height:1.08;margin:0 0 18px 0;color:#082355;font-weight:900;letter-spacing:-.04em;text-transform:uppercase}
.product-box{height:190px;border-radius:22px;background:rgba(255,255,255,.62);border:1px solid rgba(13,99,206,.18);box-shadow:0 18px 38px -25px rgba(15,23,42,.55);display:flex;align-items:center;justify-content:center;padding:16px}
.product-box img{max-width:100%;max-height:158px;object-fit:contain}
.product-box .placeholder{font-size:54px;color:#9cc3ef}
.ficha-meta{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:10px}
.meta-item{background:rgba(255,255,255,.68);border:1px solid rgba(13,99,206,.15);border-radius:14px;padding:10px 12px;min-height:58px}
.meta-label{font-size:10px;text-transform:uppercase;letter-spacing:.12em;font-weight:900;color:#0759b8;margin-bottom:2px}
.meta-value{font-size:13px;font-weight:900;color:#0f172a;word-break:break-word}
.ficha-content{padding:30px 34px 34px 34px}
.section-card{border:1px solid #e2e8f0;background:#f8fbff;border-radius:22px;padding:22px 24px;margin:0 0 24px 0}
.section-eyebrow{font-size:11px;text-transform:uppercase;letter-spacing:.20em;font-weight:900;color:#0b63ce;margin:0 0 4px 0}
.section-title{font-size:22px;line-height:1.18;margin:0 0 8px 0;color:#0f172a;font-weight:900}
.section-desc{margin:0;color:#475569;font-size:13px;line-height:1.75}
.spec-heading{display:flex;align-items:flex-end;justify-content:space-between;gap:16px;margin:4px 0 18px 0}
.spec-heading h2{font-size:26px;line-height:1.14;margin:0;color:#0f172a;font-weight:900;letter-spacing:-.03em}
.spec-count{white-space:nowrap;background:#eaf3ff;border:1px solid #cde2ff;color:#0759b8;border-radius:999px;padding:8px 13px;font-size:12px;font-weight:900}
.spec-group{border:1px solid #dce8f7;border-radius:18px;overflow:hidden;margin:0 0 18px 0;background:#fff;break-inside:avoid;page-break-inside:avoid}
.spec-group-header{background:#0b1f3f;color:#fff;padding:14px 18px;display:flex;align-items:center;justify-content:space-between;gap:12px}
.spec-group-title{font-size:15px;font-weight:900;margin:0;display:flex;align-items:center;gap:10px}
.spec-group-icon{width:30px;height:30px;border-radius:10px;background:#0b72e7;display:inline-flex;align-items:center;justify-content:center;color:#fff;flex:0 0 auto;text-align:center}
.spec-group-count{font-size:11px;color:#bfdbfe;font-weight:900}
.spec-table{width:100%;border-collapse:collapse;table-layout:fixed}
.spec-table th{background:#eef6ff;color:#0759b8;text-align:left;font-size:11px;text-transform:uppercase;letter-spacing:.12em;padding:11px 14px;border-bottom:1px solid #dce8f7}
.spec-table th:first-child{width:34%}
.spec-table th:nth-child(2){width:66%}
.spec-table td{padding:13px 14px;border-bottom:1px solid #eef2f7;vertical-align:top;color:#1e293b;word-break:break-word}
.spec-table tr:last-child td{border-bottom:0}
.spec-table tr:nth-child(even) td{background:#fbfdff}
.spec-name{font-weight:900;color:#0f274d}
.spec-value{font-weight:600;color:#334155;line-height:1.65}
.empty-specs{border:2px dashed #cbd5e1;border-radius:18px;background:#f8fafc;padding:30px;text-align:center;color:#64748b;font-weight:800}
.ficha-footer{background:#08101f;color:#e2e8f0;padding:18px 34px;font-size:11px;display:flex;justify-content:space-between;gap:16px;flex-wrap:wrap}
.ficha-footer strong{color:#fff}
@media(max-width:760px){body{padding:14px 10px}.ficha-page{border-radius:20px}.ficha-header{padding:22px 18px}.ficha-brand-row,.ficha-brand-main,.ficha-product-media{display:block;width:100%;padding-right:0}.ficha-logo{max-width:230px}.ficha-title{font-size:24px}.product-box{height:160px;margin-top:16px}.ficha-meta{grid-template-columns:repeat(2,minmax(0,1fr))}.ficha-content{padding:22px 18px}.spec-heading{display:block}.spec-count{display:inline-block;margin-top:10px}.spec-table th:first-child,.spec-table th:nth-child(2){width:auto}.spec-table,.spec-table thead,.spec-table tbody,.spec-table tr,.spec-table th,.spec-table td{display:block}.spec-table thead{display:none}.spec-table td{border-bottom:0;padding:10px 14px}.spec-table tr{border-bottom:1px solid #eef2f7}.spec-table tr:last-child{border-bottom:0}.spec-table td:first-child{background:#f2f7ff;color:#0759b8}.spec-table td:last-child{background:#fff}.no-print{align-items:stretch}.no-print a,.no-print button{width:100%;text-align:center}.ficha-footer{display:block}.ficha-footer div{margin-bottom:6px}}
@media print{.no-print{display:none!important}body{background:#fff;padding:0}.ficha-page{box-shadow:none;border-radius:0;border:0}.ficha-header{-webkit-print-color-adjust:exact;print-color-adjust:exact}.spec-group,.section-card{break-inside:avoid;page-break-inside:avoid}.ficha-footer{position:fixed;bottom:0;left:0;right:0}}
CSS;
    }
}

if (!function_exists('emxFichaRenderSpecsTabla')) {
    function emxFichaRenderSpecsTabla(array $specs, bool $pdf = false): string {
        $grupos = emxPrepararGruposFicha($specs);
        if (!$grupos) {
            return '<div class="empty-specs">No hay especificaciones técnicas registradas para este producto.</div>';
        }
        $html = '';
        foreach ($grupos as $grupo => $items) {
            $icon = htmlspecialchars(emxFichaIconoGrupo($grupo));
            $html .= '<section class="spec-group">';
            $html .= '<header class="spec-group-header">';
            $html .= '<h3 class="spec-group-title"><span class="spec-group-icon"><i class="fas ' . $icon . '"></i></span>' . htmlspecialchars($grupo) . '</h3>';
            $html .= '<span class="spec-group-count">' . count($items) . ' dato(s)</span>';
            $html .= '</header>';
            $html .= '<table class="spec-table"><thead><tr><th>Especificación</th><th>Detalle del producto</th></tr></thead><tbody>';
            foreach ($items as $k => $v) {
                $html .= '<tr>';
                $html .= '<td class="spec-name">' . htmlspecialchars(emxFichaLabel($k)) . '</td>';
                $html .= '<td class="spec-value">' . nl2br(htmlspecialchars(emxFichaValorTexto($v, $k))) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table></section>';
        }
        return $html;
    }
}

if (!function_exists('emxFichaRenderDocumento')) {
    function emxFichaRenderDocumento(array $producto, array $specs, array $datosProducto, array $opts = []): string {
        $pdf = !empty($opts['pdf']);
        $logo = $opts['logo'] ?? (defined('EMX_EMPRESA_LOGO') ? EMX_EMPRESA_LOGO : 'assets/electromax_logo.png');
        $volver = $opts['volver'] ?? ('producto.php?id=' . urlencode((string)($producto['id'] ?? '')));
        $logoSrc = $pdf ? emxFichaDataUri($logo) : $logo;
        $img = (string)($producto['imagen'] ?? '');
        $imgSrc = $pdf ? emxFichaDataUri($img) : $img;
        $grupos = emxPrepararGruposFicha($specs);
        $specCount = emxFichaContarSpecs($grupos);
        $nombre = htmlspecialchars((string)($producto['nombre'] ?? 'Producto'));
        $descripcion = trim((string)($producto['descripcion_corta'] ?? ''));
        $css = emxFichaCss($pdf);
        $printControls = $pdf ? '' : '<div class="no-print"><a href="' . htmlspecialchars($volver) . '">← Volver</a><div><button type="button" onclick="window.print()">Imprimir vista</button> <a class="btn-pdf" href="ficha_tecnica_pdf.php?id=' . urlencode((string)($producto['id'] ?? '')) . '">Descargar PDF</a></div></div>';
        $meta = '';
        foreach ($datosProducto as $row) {
            $meta .= '<div class="meta-item"><div class="meta-label">' . htmlspecialchars((string)$row[0]) . '</div><div class="meta-value">' . htmlspecialchars((string)$row[1]) . '</div></div>';
        }
        $imageBox = $imgSrc !== '' ? '<img src="' . htmlspecialchars($imgSrc) . '" alt="Producto">' : '<div class="placeholder">■</div>';
        $descHtml = $descripcion !== '' ? '<section class="section-card"><p class="section-eyebrow">Resumen del producto</p><h2 class="section-title">Descripción comercial registrada</h2><p class="section-desc">' . nl2br(htmlspecialchars($descripcion)) . '</p></section>' : '';
        $footerEmpresa = defined('EMX_EMPRESA_DIRECCION') ? EMX_EMPRESA_DIRECCION . ' · ' . EMX_EMPRESA_EMAIL . ' · ' . EMX_EMPRESA_TELEFONO : 'ElectroMax';
        $fontAwesome = $pdf ? '' : '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">';
        return '<!DOCTYPE html><html lang="es"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Ficha Técnica - ' . $nombre . '</title>' . $fontAwesome . '<style>' . $css . '</style></head><body>' . $printControls . '<article class="ficha-page"><header class="ficha-header"><div class="ficha-brand-row"><div class="ficha-brand-main"><img src="' . htmlspecialchars($logoSrc) . '" class="ficha-logo" alt="ElectroMax"><p class="ficha-kicker">Ficha técnica oficial</p><h1 class="ficha-title">' . $nombre . '</h1>' . ($meta ? '<div class="ficha-meta">' . $meta . '</div>' : '') . '</div><div class="ficha-product-media"><div class="product-box">' . $imageBox . '</div></div></div></header><main class="ficha-content">' . $descHtml . '<section><div class="spec-heading"><div><p class="section-eyebrow">Detalle técnico</p><h2>Especificaciones técnicas</h2><p class="section-desc">Información organizada en tabla para facilitar lectura, impresión y revisión técnica.</p></div><span class="spec-count">' . $specCount . ' especificación(es)</span></div>' . emxFichaRenderSpecsTabla($specs, $pdf) . '</section></main><footer class="ficha-footer"><div><strong>ElectroMax</strong> · ' . htmlspecialchars($footerEmpresa) . '</div><div>Documento generado con información registrada en el catálogo.</div></footer></article></body></html>';
    }
}

/* Compatibilidad con nombres usados en parches anteriores. */
if (!function_exists('emxRenderFichaPremium')) {
    function emxRenderFichaPremium(array $specs) {
        return emxFichaRenderSpecsTabla($specs, false);
    }
}
?>
