<?php
if (!defined('EMX_ROOT')) {
    require_once dirname(__DIR__, 3) . '/bootstrap/app.php';
}

require_once EMX_MIDDLEWARE_PATH . '/security.php';
require_once EMX_CONFIG_PATH . '/database.php';
require_once EMX_CONFIG_PATH . '/company.php';

$id = trim($_GET['id'] ?? '');
if (!emxIsUuid($id)) { http_response_code(400); exit('Producto inválido'); }

$stmt = $pdo->prepare("\n    SELECT p.*, c.nombre AS categoria, m.nombre AS marca, m.pais_origen\n    FROM productos p\n    LEFT JOIN categorias c ON c.id = p.categoria_id\n    LEFT JOIN marcas m ON m.id = p.marca_id\n    WHERE p.id = ? AND p.deleted_at IS NULL AND p.is_active = TRUE\n");
$stmt->execute([$id]);
$p = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$p) { http_response_code(404); exit('Producto no encontrado'); }

$specs = json_decode($p['especificaciones_tecnicas'] ?? '{}', true);
$specs = is_array($specs) ? $specs : [];

function pdf_clean_text($s) {
    $s = (string)$s;
    $s = str_replace(["\r\n", "\r"], "\n", $s);
    $s = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F]/', '', $s);
    return trim($s);
}
function pdf_escape($s){
    $s = pdf_clean_text($s);
    $converted = @iconv('UTF-8','ISO-8859-1//TRANSLIT',$s);
    if ($converted === false) $converted = $s;
    return str_replace(['\\','(',')'], ['\\\\','\\(','\\)'], $converted);
}
function pdf_text($x,$y,$size,$text,$font='F1',$r=0.08,$g=0.11,$b=0.17){
    return sprintf("%.3f %.3f %.3f rg BT /%s %.2f Tf %.2f %.2f Td (%s) Tj ET\n", $r,$g,$b,$font,$size,$x,$y,pdf_escape($text));
}
function pdf_rect($x,$y,$w,$h,$r,$g,$b){ return sprintf("%.3f %.3f %.3f rg %.2f %.2f %.2f %.2f re f\n",$r,$g,$b,$x,$y,$w,$h); }
function wrap_pdf($text,$max=42){ return explode("\n", wordwrap(pdf_clean_text((string)$text),$max,"\n",true)); }

function ficha_label($k){
    $labels = [
        'pulgadas' =>'Tamaño de pantalla', 'resolucion' =>'Resolución', 'hdr' =>'HDR compatible',
        'smart_tv' =>'Smart TV', 'sistema_operativo' =>'Sistema operativo', 'puertos_hdmi' =>'Puertos HDMI',
        'puertos_usb' =>'Puertos USB', 'wifi' =>'WiFi', 'bluetooth' =>'Bluetooth',
        'capacidad_litros' =>'Capacidad', 'capacidad_kg' =>'Capacidad de lavado', 'eficiencia_energetica' =>'Eficiencia energética',
        'tecnologia' =>'Tecnología', 'tipo_carga' =>'Tipo de carga', 'velocidad_rpm' =>'Velocidad de centrifugado',
        'capacidad_btu' =>'Capacidad de enfriamiento', 'cobertura_m2' =>'Cobertura aproximada', 'gas_refrigerante' =>'Gas refrigerante',
        'dispensador_agua' =>'Dispensador de agua', 'dispensador_hielo' =>'Dispensador de hielo', 'tipo_panel' =>'Tipo de panel',
        'frecuencia_hz' =>'Frecuencia', 'potencia_audio' =>'Potencia de audio', 'control_voz' =>'Control por voz',
        'asistente' =>'Asistente compatible', 'color' =>'Color', 'tipo' =>'Tipo', 'configuracion' =>'Configuración',
        'compresor' =>'Compresor', 'programas' =>'Programas', 'vapor' =>'Función vapor', 'funciones' =>'Funciones'
    ];
    $key = strtolower((string)$k);
    return $labels[$key] ?? ucwords(str_replace('_',' ',(string)$k));
}
function ficha_unit($k){
    $units = [
        'pulgadas' =>'"', 'capacidad_litros' =>'L', 'capacidad_kg' =>'kg', 'velocidad_rpm' =>'RPM',
        'capacidad_btu' =>'BTU', 'cobertura_m2' =>'m2', 'frecuencia_hz' =>'Hz', 'potencia_audio' =>'W'
    ];
    return $units[strtolower((string)$k)] ?? '';
}
function ficha_group($k){
    $key = strtolower((string)$k);
    $map = [
        'Pantalla e imagen' =>['pulgadas','resolucion','hdr','tipo_panel','frecuencia_hz','brillo','contraste','formato_pantalla','smart_tv'],
        'Audio y video' =>['audio','potencia_audio','dolby','altavoces','video','formatos_video'],
        'Conectividad' =>['wifi','bluetooth','puertos_hdmi','puertos_usb','ethernet','conexion','conectividad','hdmi','usb'],
        'Sistema y funciones' =>['sistema_operativo','procesador','memoria','almacenamiento','funciones','apps','control_voz','asistente'],
        'Capacidad y rendimiento' =>['capacidad_litros','capacidad_kg','capacidad_btu','velocidad_rpm','cobertura_m2','programas','compresor','tecnologia'],
        'Energía y eficiencia' =>['eficiencia','eficiencia_energetica','consumo','voltaje','potencia','clase_energetica'],
        'Diseño y características' =>['color','tipo','configuracion','material','acabado','dispensador_agua','dispensador_hielo','vapor','gas_refrigerante'],
    ];
    foreach($map as $g=>$keys){ foreach($keys as $needle){ if($key === $needle || str_contains($key,$needle)) return $g; } }
    return 'Otras especificaciones';
}
function ficha_order($group, $key) {
    $orders = [
        'Pantalla e imagen' =>['pulgadas','resolucion','tipo_panel','frecuencia_hz','hdr','smart_tv'],
        'Audio y video' =>['potencia_audio','audio','dolby','altavoces','video','formatos_video'],
        'Conectividad' =>['wifi','bluetooth','puertos_hdmi','puertos_usb','ethernet','conexion','conectividad'],
        'Sistema y funciones' =>['sistema_operativo','procesador','memoria','almacenamiento','apps','funciones','control_voz','asistente'],
        'Capacidad y rendimiento' =>['capacidad_litros','capacidad_kg','capacidad_btu','velocidad_rpm','programas','compresor','tecnologia','cobertura_m2'],
        'Energía y eficiencia' =>['eficiencia_energetica','clase_energetica','consumo','voltaje','potencia'],
        'Diseño y características' =>['tipo','configuracion','color','material','acabado','dispensador_agua','dispensador_hielo','vapor','gas_refrigerante'],
    ];
    $list = $orders[$group] ?? [];
    $idx = array_search(strtolower((string)$key), $list, true);
    return $idx === false ? 999 : $idx;
}
function ficha_value($v,$key=''){
    if(is_bool($v)) return $v ? 'Sí' : 'No';
    if($v === null || $v === '') return 'No registrado';
    if(is_array($v)){
        $assoc = array_keys($v) !== range(0, count($v)-1);
        if(!$assoc) return implode(', ', array_map(fn($x)=>ficha_value($x,$key),$v));
        $parts=[];
        foreach($v as $k=>$vv){ $parts[] = ficha_label($k).': '.ficha_value($vv,$k); }
        return implode(' | ', $parts);
    }
    $txt = trim((string)$v);
    $unit = ficha_unit($key);
    if($unit !== '' && is_numeric($txt) && !str_contains($txt,$unit)) $txt .= ' '.$unit;
    return $txt;
}

$groups = [];
foreach ($specs as $k =>$v) {
    if ($v === null || $v === '' || $v === []) continue;
    $groups[ficha_group($k)][$k] = $v;
}
foreach ($groups as $g =>&$items) {
    uksort($items, fn($a,$b)=>[ficha_order($g,$a),ficha_label($a)] <=>[ficha_order($g,$b),ficha_label($b)]);
}
unset($items);

$datos = [];
foreach ([
    'SKU' =>$p['sku'] ?? '', 'Modelo' =>$p['modelo'] ?? '', 'Marca' =>$p['marca'] ?? '', 'Categoría' =>$p['categoria'] ?? ''
] as $label =>$value) { if (trim((string)$value) !== '') $datos[] = [$label,$value]; }

$logoPath = defined('EMX_EMPRESA_LOGO_PDF') ? EMX_EMPRESA_LOGO_PDF : 'assets/electromax_logo_pdf.jpg';
$logoExists = is_file($logoPath);
$logoInfo = $logoExists ? @getimagesize($logoPath) : null;

function build_header($p, $pageNo, $logoExists, $logoInfo) {
    $c = '';
    $c .= pdf_rect(0,720,595,122,0.86,0.93,1.00);
    $c .= pdf_rect(0,720,595,8,0.00,0.45,0.95);
    $c .= pdf_rect(420,735,120,70,0.76,0.88,1.00);
    if ($logoExists && $logoInfo) {
        $w = 205; $h = $w * ($logoInfo[1] / max(1,$logoInfo[0]));
        if ($h >55) { $h = 55; $w = $h * ($logoInfo[0] / max(1,$logoInfo[1])); }
        $c .= sprintf("q %.2f 0 0 %.2f 38 %.2f cm /ImLogo Do Q\n", $w, $h, 783-$h);
    } else {
        $c .= pdf_text(42,770,22,EMX_EMPRESA_NOMBRE,'F2',0.05,0.12,0.22);
    }
    $c .= pdf_text(42,740,9,'FICHA TECNICA OFICIAL','F2',0.00,0.32,0.70);
    $c .= pdf_text(430,790,8,'Página '.$pageNo,'F2',0.13,0.23,0.38);
    $c .= pdf_text(430,775,7,'Generado: '.date('d/m/Y H:i'),'F1',0.27,0.36,0.50);
    $nameLines = wrap_pdf($p['nombre'], 52);
    $y = 694;
    foreach (array_slice($nameLines,0,2) as $line) { $c .= pdf_text(42,$y,18,$line,'F2',0.06,0.16,0.36); $y -= 22; }
    return $c;
}
function build_footer() {
    $c = '';
    $c .= pdf_rect(0,0,595,54,0.05,0.07,0.12);
    $c .= pdf_text(42,32,8,EMX_EMPRESA_DIRECCION.' · '.EMX_EMPRESA_EMAIL.' · '.EMX_EMPRESA_TELEFONO,'F1',1,1,1);
    $c .= pdf_text(42,18,7,'Documento generado solo con especificaciones registradas en el catálogo.','F1',0.78,0.84,0.92);
    return $c;
}
function ensure_space(&$c,&$pages,&$pageNo,$p,$logoExists,$logoInfo,&$y,$needed) {
    if ($y - $needed < 72) {
        $c .= build_footer();
        $pages[] = $c;
        $pageNo++;
        $c = build_header($p,$pageNo,$logoExists,$logoInfo);
        $y = 642;
    }
}
function draw_table_head($y) {
    $c = '';
    $c .= pdf_rect(42,$y-18,511,24,0.93,0.96,1.00);
    $c .= pdf_text(54,$y-4,7.5,'#','F2',0.00,0.32,0.70);
    $c .= pdf_text(86,$y-4,7.5,'ESPECIFICACION','F2',0.00,0.32,0.70);
    $c .= pdf_text(246,$y-4,7.5,'DETALLE DEL PRODUCTO','F2',0.00,0.32,0.70);
    return $c;
}
function draw_spec_row($y,$num,$label,$valueLines,$rowH) {
    $c = '';
    $c .= pdf_rect(42,$y-$rowH+8,511,$rowH,0.985,0.992,1.00);
    $c .= pdf_rect(42,$y-$rowH+8,511,0.7,0.86,0.90,0.96);
    $c .= pdf_rect(54,$y-19,22,22,0.00,0.32,0.70);
    $c .= pdf_text(60,$y-10,7.5,$num,'F2',1,1,1);
    foreach (wrap_pdf($label, 24) as $i=>$line) {
        if ($i > 2) break;
        $c .= pdf_text(88,$y-4-($i*10),8.2,$line,'F2',0.06,0.16,0.36);
    }
    $yy = $y - 4;
    foreach (array_slice($valueLines,0,6) as $line) {
        $c .= pdf_text(246,$yy,8.3,$line,'F1',0.08,0.11,0.17);
        $yy -= 10.5;
    }
    return $c;
}

$pages = [];
$pageNo = 1;
$c = build_header($p,$pageNo,$logoExists,$logoInfo);
$y = 635;

if ($datos) {
    $c .= pdf_text(42,$y,13,'Identificación del producto','F2',0.06,0.16,0.36); $y -= 22;
    $col = 0; $rowY = $y;
    foreach ($datos as $row) {
        $x = $col === 0 ? 42 : 306;
        $c .= pdf_rect($x,$rowY-20,247,26,0.97,0.99,1.00);
        $c .= pdf_text($x+10,$rowY-4,8,$row[0],'F2',0.00,0.32,0.70);
        $c .= pdf_text($x+82,$rowY-4,8,substr((string)$row[1],0,33),'F1',0.08,0.11,0.17);
        if ($col === 1) { $rowY -= 33; $col = 0; } else { $col = 1; }
    }
    $y = $rowY - 16;
}

if (!empty(trim((string)($p['descripcion_corta'] ?? '')))) {
    ensure_space($c,$pages,$pageNo,$p,$logoExists,$logoInfo,$y,85);
    $c .= pdf_text(42,$y,13,'Resumen del producto','F2',0.06,0.16,0.36); $y -= 20;
    $lines = wrap_pdf($p['descripcion_corta'], 95);
    $boxH = min(86, 20 + count(array_slice($lines,0,5))*11);
    $c .= pdf_rect(42,$y-$boxH+8,511,$boxH,0.985,0.993,1.00);
    $yy = $y - 9;
    foreach (array_slice($lines,0,5) as $line) { $c .= pdf_text(54,$yy,8.5,$line,'F1',0.25,0.33,0.45); $yy -= 11; }
    $y -= ($boxH + 18);
}

$c .= pdf_text(42,$y,14,'Especificaciones técnicas','F2',0.06,0.16,0.36); $y -= 22;
if (!$groups) {
    $c .= pdf_rect(42,$y-30,511,34,0.98,0.98,0.99);
    $c .= pdf_text(54,$y-12,9,'No hay especificaciones técnicas registradas para este producto.','F1',0.35,0.42,0.52);
} else {
    foreach ($groups as $group =>$items) {
        ensure_space($c,$pages,$pageNo,$p,$logoExists,$logoInfo,$y,72);
        $c .= pdf_rect(42,$y-22,511,28,0.05,0.07,0.12);
        $c .= pdf_text(54,$y-5,10,$group,'F2',1,1,1);
        $c .= pdf_text(485,$y-5,7,count($items).' dato(s)','F2',0.75,0.86,1.00);
        $y -= 38;
        $c .= draw_table_head($y);
        $y -= 30;
        $n = 1;
        foreach ($items as $k=>$v) {
            $label = ficha_label($k);
            $valueLines = wrap_pdf(ficha_value($v,$k), 52);
            $labelLines = wrap_pdf($label, 24);
            $rowH = max(40, 22 + max(count(array_slice($valueLines,0,6))*10.5, count(array_slice($labelLines,0,3))*10));
            ensure_space($c,$pages,$pageNo,$p,$logoExists,$logoInfo,$y,$rowH+18);
            $c .= draw_spec_row($y,str_pad((string)$n,2,'0',STR_PAD_LEFT),$label,$valueLines,$rowH);
            $y -= ($rowH + 7);
            $n++;
        }
        $y -= 10;
    }
}
$c .= build_footer();
$pages[] = $c;

$objects=[];
$objects[] = null;
$objects[] = null;
$objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>";
$objects[] = "<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>";
$logoObjNum = null;
if ($logoExists && $logoInfo) {
    $logoObjNum = count($objects)+1;
    $imgData = file_get_contents($logoPath);
    $objects[] = "<< /Type /XObject /Subtype /Image /Width {$logoInfo[0]} /Height {$logoInfo[1]} /ColorSpace /DeviceRGB /BitsPerComponent 8 /Filter /DCTDecode /Length ".strlen($imgData)." >>\nstream\n".$imgData."\nendstream";
}
$pageObjNums = [];
$contentObjNums = [];
foreach ($pages as $pc) {
    $pageObjNums[] = count($objects)+1; $objects[] = null;
    $contentObjNums[] = count($objects)+1; $objects[] = "<< /Length ".strlen($pc)." >>\nstream\n$pc\nendstream";
}
$kids = implode(' ', array_map(fn($n)=>$n.' 0 R', $pageObjNums));
$objects[0] = "<< /Type /Catalog /Pages 2 0 R >>";
$objects[1] = "<< /Type /Pages /Kids [{$kids}] /Count ".count($pageObjNums)." >>";
$resource = "<< /Font << /F1 3 0 R /F2 4 0 R >>";
if ($logoObjNum) $resource .= " /XObject << /ImLogo {$logoObjNum} 0 R >>";
$resource .= " >>";
foreach ($pageObjNums as $i=>$po) {
    $objects[$po-1] = "<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources {$resource} /Contents {$contentObjNums[$i]} 0 R >>";
}

$pdf="%PDF-1.4\n"; $offsets=[0];
foreach($objects as $i=>$obj){ $offsets[] = strlen($pdf); $pdf .= ($i+1)." 0 obj\n$obj\nendobj\n"; }
$xref = strlen($pdf); $pdf .= "xref\n0 ".(count($objects)+1)."\n0000000000 65535 f \n";
for($i=1;$i<=count($objects);$i++){ $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]); }
$pdf .= "trailer << /Size ".(count($objects)+1)." /Root 1 0 R >>\nstartxref\n$xref\n%%EOF";
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="ficha-tecnica-'.preg_replace('/[^a-z0-9]+/i','-',strtolower($p['sku'] ?: $p['nombre'])).'.pdf"');
header('Content-Length: '.strlen($pdf));
echo $pdf;
?>