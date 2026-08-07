<?php
/**
 * Helper centralizado - Fase 3.
 *
 * Archivo original: `funciones_ficha_tecnica.php`.
 * La ruta antigua en raíz queda como adaptador para no romper `require_once`.
 */

if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 2) . '/bootstrap/app.php';
}

/**
 * Funciones compartidas para mostrar fichas técnicas premium.
 * Solo usa datos reales registrados en productos.especificaciones_tecnicas.
 */

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
        if (str_contains($txt, ',') && strlen($txt) <= 140) {
            $parts = array_values(array_filter(array_map('trim', explode(',', $txt)), fn($x) =>$x !== ''));
            return count($parts) >1 ? $parts : null;
        }
        return null;
    }
}

if (!function_exists('emxRenderFichaValorPremium')) {
    function emxRenderFichaValorPremium($v, $key = '') {
        if (is_bool($v)) {
            return '<span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-blue-50 text-blue-800 border border-blue-100 font-black text-sm"><i class="fas ' . ($v ? 'fa-check' : 'fa-xmark') . '"></i>' . ($v ? 'Sí' : 'No') . '</span>';
        }
        if ($v === null || $v === '') {
            return '<span class="text-slate-400 font-semibold">No registrado</span>';
        }
        if (is_array($v)) {
            $isAssoc = array_keys($v) !== range(0, count($v) - 1);
            if ($isAssoc) {
                $html = '<dl class="grid sm:grid-cols-2 gap-3">';
                foreach ($v as $kk =>$vv) {
                    $html .= '<div class="rounded-2xl bg-slate-50 border border-slate-200 p-4">';
                    $html .= '<dt class="text-[11px] uppercase tracking-[.16em] text-blue-700 font-black mb-2">' . htmlspecialchars(emxFichaLabel($kk)) . '</dt>';
                    $html .= '<dd class="text-slate-950 font-black leading-relaxed">' . emxRenderFichaValorPremium($vv, $kk) . '</dd>';
                    $html .= '</div>';
                }
                return $html . '</dl>';
            }
        }
        $lista = emxFichaNormalizarLista($v, $key);
        if ($lista && count($lista) >1) {
            $html = '<ul class="grid sm:grid-cols-2 gap-2.5">';
            foreach ($lista as $item) {
                $html .= '<li class="flex items-start gap-2.5 rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3 text-slate-950 font-extrabold leading-relaxed">';
                $html .= '<span class="mt-0.5 w-5 h-5 rounded-full bg-blue-600 text-white flex items-center justify-center shrink-0"><i class="fas fa-check text-[10px]"></i></span>';
                $html .= '<span>' . htmlspecialchars($item) . '</span></li>';
            }
            return $html . '</ul>';
        }
        $txt = emxFichaValorTexto($v, $key);
        $lines = array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $txt)), fn($x) =>$x !== ''));
        if (count($lines) >1) {
            $html = '<ul class="space-y-2.5">';
            foreach ($lines as $line) {
                $html .= '<li class="flex items-start gap-2.5 rounded-2xl bg-slate-50 border border-slate-200 px-4 py-3 text-slate-950 font-bold leading-relaxed"><span class="mt-2 w-2 h-2 rounded-full bg-blue-600 shrink-0"></span><span>' . htmlspecialchars($line) . '</span></li>';
            }
            return $html . '</ul>';
        }
        return '<span class="block text-slate-950 font-black leading-relaxed text-lg break-words">' . htmlspecialchars($txt) . '</span>';
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

if (!function_exists('emxRenderFichaPremium')) {
    function emxRenderFichaPremium(array $specs) {
        $grupos = emxPrepararGruposFicha($specs);
        if (!$grupos) {
            return '<div class="rounded-3xl border-2 border-dashed border-slate-300 p-10 text-center text-slate-500 bg-slate-50"><i class="fas fa-circle-info text-3xl mb-3 text-blue-300"></i><p class="font-bold">No hay especificaciones técnicas registradas para este producto.</p></div>';
        }

        $html = '<div class="space-y-8 emx-spec-table-wrap">';
        foreach ($grupos as $grupo => $items) {
            $html .= '<section class="rounded-[1.6rem] border border-slate-200 bg-white overflow-hidden shadow-sm emx-spec-table-section">';
            $html .= '<header class="bg-gradient-to-r from-slate-950 via-blue-950 to-slate-900 text-white px-5 md:px-7 py-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">';
            $html .= '<div class="flex items-center gap-3"><span class="w-11 h-11 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center"><i class="fas ' . htmlspecialchars(emxFichaIconoGrupo($grupo)) . ' text-blue-200"></i></span><div><p class="text-[11px] uppercase tracking-[.22em] text-blue-200 font-black">Tabla técnica</p><h3 class="font-black text-xl leading-tight">' . htmlspecialchars($grupo) . '</h3></div></div>';
            $html .= '<span class="text-xs font-black text-blue-100 bg-white/10 rounded-full px-3 py-1">' . count($items) . ' dato(s)</span>';
            $html .= '</header>';
            $html .= '<div class="overflow-x-auto">';
            $html .= '<table class="w-full text-left border-collapse emx-spec-table">';
            $html .= '<thead><tr class="bg-blue-50/80 border-b border-blue-100"><th class="w-16 px-5 py-3 text-[11px] uppercase tracking-[.16em] text-blue-700 font-black">#</th><th class="w-[32%] px-5 py-3 text-[11px] uppercase tracking-[.16em] text-blue-700 font-black">Especificación</th><th class="px-5 py-3 text-[11px] uppercase tracking-[.16em] text-blue-700 font-black">Detalle del producto</th></tr></thead><tbody class="divide-y divide-slate-100">';
            $n = 1;
            foreach ($items as $k => $v) {
                $num = str_pad((string)$n, 2, '0', STR_PAD_LEFT);
                $html .= '<tr class="hover:bg-blue-50/35 transition align-top">';
                $html .= '<td class="px-5 py-4"><span class="w-9 h-9 rounded-xl bg-blue-700 text-white flex items-center justify-center text-xs font-black shadow-sm">' . $num . '</span></td>';
                $html .= '<th class="px-5 py-4 text-slate-950 font-black leading-snug"><span class="block">' . htmlspecialchars(emxFichaLabel($k)) . '</span><span class="block text-[11px] text-slate-400 font-bold mt-1 uppercase tracking-wide">Campo técnico</span></th>';
                $html .= '<td class="px-5 py-4 text-slate-800 font-semibold leading-relaxed">' . emxRenderFichaValorPremium($v, $k) . '</td>';
                $html .= '</tr>';
                $n++;
            }
            $html .= '</tbody></table></div></section>';
        }
        return $html . '</div>';
    }
}

?>