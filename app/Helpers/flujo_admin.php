<?php
require_once __DIR__ . '/funciones_facturacion.php';
/**
 * Flujo guiado de pedidos, devoluciones e incidencias - ElectroMax
 *
 * Este archivo centraliza la máquina de estados para que el admin no elija
 * estados sueltos. El admin ejecuta acciones y el sistema decide el estado.
 */

if (!function_exists('emxTextoEstado')) {
function emxTextoEstado($estado) {
    $mapa = [
        'pendiente_revision' =>'Pendiente de revisión',
        'pendiente_revision_fraude' =>'Pendiente revisión / alerta fraude',
        'requiere_mas_evidencia' =>'Requiere más evidencia',
        'autorizada_retorno' =>'Retorno autorizado',
        'en_camino_retorno' =>'Retorno en camino',
        'recibido_almacen' =>'Recibido en almacén',
        'en_inspeccion' =>'En inspección técnica',
        'esperando_decision_cliente' =>'Esperando decisión del cliente',
        'cliente_eligio_reembolso' =>'Cliente eligió reembolso',
        'cliente_eligio_cambio' =>'Cliente eligió cambio igual',
        'aprobado_reembolso' =>'Reembolso aprobado',
        'reembolsado' =>'Reembolsado',
        'aprobado_cambio' =>'Cambio aprobado',
        'cambio_despachado' =>'Reemplazo generado / despachado',
        'reemplazo_en_transito' =>'Reemplazo en tránsito',
        'reemplazo_entregado' =>'Reemplazo entregado',
        'cerrada' =>'Cerrada',
        'rechazada' =>'Rechazada',
        'investigacion_courier' =>'Investigación courier',
        'reclamo_courier' =>'Reclamo courier',
        'garantia_proveedor' =>'Garantía proveedor'
    ];
    return $mapa[$estado] ?? trim(ucfirst(str_replace('_', ' ', (string)$estado)));
}
}


if (!function_exists('emxTextoMotivoDevolucion')) {
function emxTextoMotivoDevolucion($motivo) {
    $mapa = [
        'defectuoso' => 'Producto defectuoso / no funciona',
        'producto_incorrecto' => 'Producto incorrecto',
        'faltan_piezas' => 'Faltan piezas o accesorios',
        'caja_abierta' => 'Caja abierta / sello roto',
        'danado_envio' => 'Dañado durante el envío',
        'no_me_gusta' => 'No me gusta / arrepentimiento',
        'talla_color' => 'Talla, color o variante no esperada',
        'mejor_precio' => 'Encontró mejor precio',
        'no_necesito' => 'Ya no lo necesita',
        'otro_culpa_tienda' => 'Otro motivo por responsabilidad de ElectroMax',
        'otro_decision_cliente' => 'Otro motivo por decisión del cliente',
        'otro_sin_clasificar' => 'Otro motivo sin clasificar',
        'no_recibido' => 'Pedido no recibido',
        'extravio_courier' => 'Extravío courier',
        'incidencia_stock' => 'Incidencia de stock'
    ];
    return $mapa[$motivo] ?? trim(ucfirst(str_replace('_', ' ', (string)$motivo)));
}
}

if (!function_exists('emxCategoriaMotivoDevolucion')) {
function emxCategoriaMotivoDevolucion($motivo) {
    $culpaTienda = ['defectuoso','producto_incorrecto','faltan_piezas','caja_abierta','danado_envio','otro_culpa_tienda'];
    $decisionCliente = ['no_me_gusta','talla_color','mejor_precio','no_necesito','otro_decision_cliente'];
    $courier = ['no_recibido','extravio_courier'];
    if (in_array($motivo, $culpaTienda, true)) return 'responsabilidad_tienda';
    if (in_array($motivo, $decisionCliente, true)) return 'decision_cliente';
    if (in_array($motivo, $courier, true)) return 'courier';
    return 'sin_clasificar';
}
}


if (!function_exists('emxFlujoCasoDevolucion')) {
function emxFlujoCasoDevolucion($motivo, $tipo_caso = '') {
    $motivo = (string)$motivo;
    $categoria = emxCategoriaMotivoDevolucion($motivo);

    if (in_array($motivo, ['no_recibido','extravio_courier'], true) || $tipo_caso === 'incidencia_courier') {
        return [
            'clave' => 'courier',
            'titulo' => 'Incidencia con courier',
            'resumen' => 'No requiere retorno inicial. El admin investiga con el courier y luego resuelve con reembolso o reenvío.',
            'soluciones' => ['opcion_reembolso', 'opcion_cambio']
        ];
    }

    if ($motivo === 'danado_envio' || $tipo_caso === 'incidencia_entrega') {
        return [
            'clave' => 'entrega_danada',
            'titulo' => 'Daño o incidencia de entrega',
            'resumen' => 'Requiere evidencia, retorno, recepción, inspección y luego el admin puede ofrecer reembolso, cambio o ambas opciones.',
            'soluciones' => ['opcion_reembolso', 'opcion_cambio', 'opcion_reembolso_cambio']
        ];
    }

    if ($categoria === 'responsabilidad_tienda') {
        return [
            'clave' => 'responsabilidad_tienda',
            'titulo' => 'Responsabilidad de ElectroMax',
            'resumen' => 'Requiere evidencia y retorno. Después de la inspección el admin puede ofrecer reembolso, cambio igual o ambas opciones.',
            'soluciones' => ['opcion_reembolso', 'opcion_cambio', 'opcion_reembolso_cambio']
        ];
    }

    if ($categoria === 'decision_cliente') {
        $soluciones = ['opcion_reembolso'];
        $resumen = 'Requiere retorno e inspección para confirmar que el producto volvió completo y en buen estado. La solución normal es reembolso.';
        if ($motivo === 'talla_color') {
            $soluciones = ['opcion_reembolso', 'opcion_cambio', 'opcion_reembolso_cambio'];
            $resumen = 'Requiere retorno e inspección. Por variante, talla o color, el admin puede ofrecer reembolso, cambio por otro igual/equivalente o ambas opciones.';
        }
        return [
            'clave' => 'decision_cliente',
            'titulo' => 'Decisión del cliente',
            'resumen' => $resumen,
            'soluciones' => $soluciones
        ];
    }

    return [
        'clave' => 'revision_manual',
        'titulo' => 'Revisión manual',
        'resumen' => 'El motivo no queda completamente clasificado. El admin pide evidencia o inspecciona antes de ofrecer una solución.',
        'soluciones' => ['opcion_reembolso', 'opcion_cambio', 'opcion_reembolso_cambio']
    ];
}
}

if (!function_exists('emxSolucionesPermitidasDevolucion')) {
function emxSolucionesPermitidasDevolucion($motivo, $tipo_caso = '') {
    $flujo = emxFlujoCasoDevolucion($motivo, $tipo_caso);
    return $flujo['soluciones'] ?? ['opcion_reembolso'];
}
}

if (!function_exists('emxTextoSolucionPropuesta')) {
function emxTextoSolucionPropuesta($valor) {
    $mapa = [
        'opcion_reembolso' => 'Solo reembolso',
        'opcion_cambio' => 'Solo cambio por otro igual',
        'opcion_reembolso_cambio' => 'Reembolso o cambio, cliente elige'
    ];
    return $mapa[$valor] ?? 'Solución pendiente';
}
}

if (!function_exists('emxEstadoDevolucionNormalizado')) {
function emxEstadoDevolucionNormalizado($estado) {
    $mapa = [
        'aprobada' =>'autorizada_retorno',
        'en_proceso' =>'en_camino_retorno'
    ];
    return $mapa[$estado] ?? $estado;
}
}

if (!function_exists('emxMotivoEsCourier')) {
function emxMotivoEsCourier($motivo) {
    return in_array($motivo, ['no_recibido', 'extravio_courier', 'danado_envio'], true);
}
}

if (!function_exists('emxMotivoRequiereRetorno')) {
function emxMotivoRequiereRetorno($motivo) {
    return !in_array($motivo, ['no_recibido', 'extravio_courier', 'incidencia_stock'], true);
}
}

if (!function_exists('emxAccionesDevolucion')) {
function emxAccionesDevolucion($estado, $motivo = '') {
    $estado = emxEstadoDevolucionNormalizado($estado);
    $motivo = (string)$motivo;

    $baseRevision = [
        'solicitar_evidencia' =>[
            'label' =>'Solicitar evidencia',
            'estado' =>'requiere_mas_evidencia',
            'icon' =>'fa-camera',
            'class' =>'bg-amber-600 hover:bg-amber-700',
            'comentario_obligatorio' =>true,
            'ayuda' =>'Pide fotos, serie o explicación antes de decidir.'
        ],
        'rechazar' =>[
            'label' =>'Rechazar solicitud',
            'estado' =>'rechazada',
            'icon' =>'fa-times-circle',
            'class' =>'bg-red-600 hover:bg-red-700',
            'comentario_obligatorio' =>true,
            'ayuda' =>'Cierra el caso como rechazado con motivo visible.'
        ]
    ];

    if (in_array($estado, ['pendiente_revision', 'pendiente_revision_fraude', 'reabierta'], true)) {
        if (in_array($motivo, ['no_recibido', 'extravio_courier'], true)) {
            return array_merge([
                'investigar_courier' =>[
                    'label' =>'Investigar courier',
                    'estado' =>'investigacion_courier',
                    'icon' =>'fa-truck',
                    'class' =>'bg-orange-600 hover:bg-orange-700',
                    'comentario_obligatorio' =>true,
                    'ayuda' =>'Usar cuando el cliente no recibió o el courier reportó pérdida.'
                ]
            ], $baseRevision);
        }
        return array_merge([
            'autorizar_retorno' =>[
                'label' =>'Autorizar retorno',
                'estado' =>'autorizada_retorno',
                'icon' =>'fa-clipboard-check',
                'class' =>'bg-blue-600 hover:bg-blue-700',
                'comentario_obligatorio' =>false,
                'ayuda' =>'Permite que el cliente devuelva el producto para revisión.'
            ]
        ], $baseRevision, [
            'investigar_courier' =>[
                'label' =>'Investigar courier',
                'estado' =>'investigacion_courier',
                'icon' =>'fa-truck',
                'class' =>'bg-orange-600 hover:bg-orange-700',
                'comentario_obligatorio' =>true,
                'ayuda' =>'Usar si el daño parece venir del transporte.'
            ]
        ]);
    }

    $acciones = [
        'requiere_mas_evidencia' =>[
            'evidencia_recibida' =>[
                'label' =>'Evidencia recibida',
                'estado' =>'pendiente_revision',
                'icon' =>'fa-rotate-left',
                'class' =>'bg-blue-600 hover:bg-blue-700',
                'comentario_obligatorio' =>false,
                'ayuda' =>'Devuelve el caso a revisión porque ya hay información.'
            ],
            'rechazar' =>[
                'label' =>'Rechazar',
                'estado' =>'rechazada',
                'icon' =>'fa-times-circle',
                'class' =>'bg-red-600 hover:bg-red-700',
                'comentario_obligatorio' =>true,
                'ayuda' =>'Rechaza por falta de evidencia o motivo no válido.'
            ]
        ],
        'autorizada_retorno' =>[
            'marcar_en_camino' =>[
                'label' =>'Marcar en camino',
                'estado' =>'en_camino_retorno',
                'icon' =>'fa-shipping-fast',
                'class' =>'bg-indigo-600 hover:bg-indigo-700',
                'comentario_obligatorio' =>false,
                'ayuda' =>'Genera guía si no existe y marca el retorno como iniciado.'
            ],
            'rechazar' =>[
                'label' =>'Anular / rechazar',
                'estado' =>'rechazada',
                'icon' =>'fa-ban',
                'class' =>'bg-red-600 hover:bg-red-700',
                'comentario_obligatorio' =>true,
                'ayuda' =>'Anula la autorización con justificación.'
            ]
        ],
        'en_camino_retorno' =>[
            'recibir_almacen' =>[
                'label' =>'Recibir en almacén',
                'estado' =>'recibido_almacen',
                'icon' =>'fa-warehouse',
                'class' =>'bg-purple-600 hover:bg-purple-700',
                'comentario_obligatorio' =>false,
                'ayuda' =>'Usar cuando el producto ya llegó físicamente.'
            ]
        ],
        'recibido_almacen' =>[
            'iniciar_inspeccion' =>[
                'label' =>'Iniciar inspección',
                'estado' =>'en_inspeccion',
                'icon' =>'fa-microscope',
                'class' =>'bg-violet-600 hover:bg-violet-700',
                'comentario_obligatorio' =>false,
                'ayuda' =>'Pasa al técnico para validar serie y causa del daño.'
            ]
        ],
        'en_inspeccion' =>[
            'ofrecer_solucion' =>[
                'label' =>'Ofrecer solución al cliente',
                'estado' =>'esperando_decision_cliente',
                'icon' =>'fa-handshake',
                'class' =>'bg-emerald-600 hover:bg-emerald-700',
                'comentario_obligatorio' =>true,
                'requiere_diagnostico' =>true,
                'requiere_solucion' =>true,
                'ayuda' =>'Después de inspección, habilita reembolso, cambio por el mismo producto o ambas opciones. El cliente verá solo lo autorizado.'
            ],
            'reclamo_courier' =>[
                'label' =>'Culpa courier',
                'estado' =>'reclamo_courier',
                'icon' =>'fa-truck-ramp-box',
                'class' =>'bg-orange-600 hover:bg-orange-700',
                'comentario_obligatorio' =>true,
                'requiere_diagnostico' =>true,
                'ayuda' =>'Marca responsabilidad de transporte.'
            ],
            'garantia_proveedor' =>[
                'label' =>'Garantía proveedor',
                'estado' =>'garantia_proveedor',
                'icon' =>'fa-industry',
                'class' =>'bg-yellow-600 hover:bg-yellow-700',
                'comentario_obligatorio' =>true,
                'requiere_diagnostico' =>true,
                'ayuda' =>'Usar solo cuando realmente parece defecto de fábrica.'
            ],
            'rechazar' =>[
                'label' =>'Rechazar',
                'estado' =>'rechazada',
                'icon' =>'fa-times-circle',
                'class' =>'bg-red-600 hover:bg-red-700',
                'comentario_obligatorio' =>true,
                'requiere_diagnostico' =>true,
                'ayuda' =>'Rechaza por daño del cliente, serie inválida o sin defecto.'
            ]
        ],
        'investigacion_courier' =>[
            'courier_reembolso' =>[
                'label' =>'Courier: reembolso',
                'estado' =>'aprobado_reembolso',
                'icon' =>'fa-money-bill-wave',
                'class' =>'bg-cyan-600 hover:bg-cyan-700',
                'comentario_obligatorio' =>true,
                'ayuda' =>'Courier confirma pérdida o problema y se reembolsa.'
            ],
            'courier_reenvio' =>[
                'label' =>'Courier: reenvío',
                'estado' =>'aprobado_cambio',
                'icon' =>'fa-exchange-alt',
                'class' =>'bg-teal-600 hover:bg-teal-700',
                'comentario_obligatorio' =>true,
                'ayuda' =>'Courier confirma pérdida y se enviará reemplazo.'
            ],
            'rechazar' =>[
                'label' =>'Rechazar reclamo',
                'estado' =>'rechazada',
                'icon' =>'fa-times-circle',
                'class' =>'bg-red-600 hover:bg-red-700',
                'comentario_obligatorio' =>true,
                'ayuda' =>'Usar si hay evidencia válida de entrega.'
            ]
        ],
        'reclamo_courier' =>[
            'courier_reembolso' =>[
                'label' =>'Resolver con reembolso',
                'estado' =>'aprobado_reembolso',
                'icon' =>'fa-money-bill-wave',
                'class' =>'bg-cyan-600 hover:bg-cyan-700',
                'comentario_obligatorio' =>true,
                'ayuda' =>'La tienda responde al cliente y luego reclama al courier.'
            ],
            'courier_reenvio' =>[
                'label' =>'Resolver con cambio',
                'estado' =>'aprobado_cambio',
                'icon' =>'fa-exchange-alt',
                'class' =>'bg-teal-600 hover:bg-teal-700',
                'comentario_obligatorio' =>true,
                'ayuda' =>'La tienda envía reemplazo y luego reclama al courier.'
            ],
            'rechazar' =>[
                'label' =>'Rechazar',
                'estado' =>'rechazada',
                'icon' =>'fa-times-circle',
                'class' =>'bg-red-600 hover:bg-red-700',
                'comentario_obligatorio' =>true,
                'ayuda' =>'No procede el reclamo.'
            ]
        ],
        'garantia_proveedor' =>[
            'proveedor_reembolso' =>[
                'label' =>'Proveedor: reembolso',
                'estado' =>'aprobado_reembolso',
                'icon' =>'fa-money-bill-wave',
                'class' =>'bg-cyan-600 hover:bg-cyan-700',
                'comentario_obligatorio' =>true,
                'ayuda' =>'Proveedor aprueba crédito/reembolso.'
            ],
            'proveedor_cambio' =>[
                'label' =>'Proveedor: cambio',
                'estado' =>'aprobado_cambio',
                'icon' =>'fa-exchange-alt',
                'class' =>'bg-teal-600 hover:bg-teal-700',
                'comentario_obligatorio' =>true,
                'ayuda' =>'Proveedor aprueba reemplazo.'
            ],
            'rechazar' =>[
                'label' =>'Proveedor rechaza',
                'estado' =>'rechazada',
                'icon' =>'fa-times-circle',
                'class' =>'bg-red-600 hover:bg-red-700',
                'comentario_obligatorio' =>true,
                'ayuda' =>'Se rechaza con explicación del proveedor.'
            ]
        ],
        'esperando_decision_cliente' =>[
            // El caso queda detenido hasta que el cliente elija una opción desde Mi cuenta.
        ],
        'cliente_eligio_reembolso' =>[
            'ejecutar_reembolso' =>[
                'label' =>'Ejecutar reembolso',
                'estado' =>'reembolsado',
                'icon' =>'fa-check-double',
                'class' =>'bg-emerald-600 hover:bg-emerald-700',
                'comentario_obligatorio' =>false,
                'ayuda' =>'Ejecuta el reembolso elegido por el cliente y genera nota de crédito.'
            ]
        ],
        'cliente_eligio_cambio' =>[
            'crear_reemplazo' =>[
                'label' =>'Crear reemplazo igual',
                'estado' =>'cambio_despachado',
                'icon' =>'fa-truck-fast',
                'class' =>'bg-emerald-600 hover:bg-emerald-700',
                'comentario_obligatorio' =>false,
                'ayuda' =>'Crea el pedido de reemplazo por el mismo producto elegido por el cliente.'
            ]
        ],
        'aprobado_reembolso' =>[
            'ejecutar_reembolso' =>[
                'label' =>'Ejecutar reembolso',
                'estado' =>'reembolsado',
                'icon' =>'fa-check-double',
                'class' =>'bg-emerald-600 hover:bg-emerald-700',
                'comentario_obligatorio' =>false,
                'ayuda' =>'Marca que el reembolso ya fue procesado.'
            ]
        ],
        'aprobado_cambio' =>[
            'crear_reemplazo' =>[
                'label' =>'Crear reemplazo',
                'estado' =>'cambio_despachado',
                'icon' =>'fa-truck-fast',
                'class' =>'bg-emerald-600 hover:bg-emerald-700',
                'comentario_obligatorio' =>false,
                'ayuda' =>'Crea o despacha pedido de reemplazo.'
            ]
        ],
        'rechazada' =>[
            'reabrir' =>[
                'label' =>'Reabrir con justificación',
                'estado' =>'pendiente_revision',
                'icon' =>'fa-unlock',
                'class' =>'bg-slate-700 hover:bg-slate-800',
                'comentario_obligatorio' =>true,
                'ayuda' =>'Solo para apelación, error del admin o nueva evidencia.'
            ],
            'cerrar' =>[
                'label' =>'Cerrar caso',
                'estado' =>'cerrada',
                'icon' =>'fa-folder-closed',
                'class' =>'bg-slate-500 hover:bg-slate-600',
                'comentario_obligatorio' =>false,
                'ayuda' =>'Cierra definitivamente el caso.'
            ]
        ],
        'reembolsado' =>[
            'cerrar' =>[
                'label' =>'Cerrar caso',
                'estado' =>'cerrada',
                'icon' =>'fa-folder-closed',
                'class' =>'bg-slate-500 hover:bg-slate-600',
                'comentario_obligatorio' =>false,
                'ayuda' =>'Cierra el expediente reembolsado.'
            ]
        ],
        'cambio_despachado' =>[
            'marcar_reemplazo_transito' =>[
                'label' =>'Marcar reemplazo en tránsito',
                'estado' =>'reemplazo_en_transito',
                'icon' =>'fa-truck-moving',
                'class' =>'bg-violet-600 hover:bg-violet-700',
                'comentario_obligatorio' =>false,
                'ayuda' =>'El pedido de reemplazo ya salió hacia el cliente.'
            ]
        ],
        'reemplazo_en_transito' =>[
            'confirmar_reemplazo_entregado' =>[
                'label' =>'Confirmar reemplazo entregado',
                'estado' =>'reemplazo_entregado',
                'icon' =>'fa-box-check',
                'class' =>'bg-emerald-600 hover:bg-emerald-700',
                'comentario_obligatorio' =>false,
                'ayuda' =>'El cliente recibió el producto de reemplazo.'
            ]
        ],
        'reemplazo_entregado' =>[
            'cerrar' =>[
                'label' =>'Cerrar caso',
                'estado' =>'cerrada',
                'icon' =>'fa-folder-closed',
                'class' =>'bg-slate-500 hover:bg-slate-600',
                'comentario_obligatorio' =>false,
                'ayuda' =>'Cierra el expediente después de entregar el reemplazo.'
            ]
        ],
        'cerrada' =>[]
    ];

    return $acciones[$estado] ?? [];
}
}

if (!function_exists('emxAccionesPedido')) {
function emxAccionesPedido($estado) {
    $acciones = [
        'Pendiente' =>[
            'confirmar_pago' =>['label' =>'Confirmar pago', 'estado' =>'Pago confirmado', 'icon' =>'fa-credit-card', 'class' =>'bg-blue-600 hover:bg-blue-700'],
            'cancelar' =>['label' =>'Cancelar', 'estado' =>'Cancelado', 'icon' =>'fa-ban', 'class' =>'bg-red-600 hover:bg-red-700', 'comentario_obligatorio' =>true]
        ],
        'Pago confirmado' =>[
            'preparar' =>['label' =>'Preparar pedido', 'estado' =>'En Preparación', 'icon' =>'fa-box-open', 'class' =>'bg-indigo-600 hover:bg-indigo-700']
        ],
        'En Preparación' =>[
            'despachar' =>['label' =>'Listo / despachar', 'estado' =>'Despachado', 'icon' =>'fa-truck-loading', 'class' =>'bg-purple-600 hover:bg-purple-700'],
            'problema_stock' =>['label' =>'Problema stock', 'estado' =>'En Revisión', 'icon' =>'fa-triangle-exclamation', 'class' =>'bg-orange-600 hover:bg-orange-700', 'crea_incidencia' =>true, 'comentario_obligatorio' =>true]
        ],
        'Despachado' =>[
            'en_transito' =>['label' =>'En tránsito', 'estado' =>'En Tránsito', 'icon' =>'fa-truck-moving', 'class' =>'bg-violet-600 hover:bg-violet-700']
        ],
        'En Tránsito' =>[
            'en_reparto' =>['label' =>'En reparto', 'estado' =>'En Reparto', 'icon' =>'fa-motorcycle', 'class' =>'bg-pink-600 hover:bg-pink-700'],
            'extravio_courier' =>['label' =>'Extravío courier', 'estado' =>'En Revisión', 'icon' =>'fa-route', 'class' =>'bg-orange-600 hover:bg-orange-700', 'crea_incidencia' =>true, 'comentario_obligatorio' =>true]
        ],
        'En Reparto' =>[
            'entregado' =>['label' =>'Marcar entregado', 'estado' =>'Entregado', 'icon' =>'fa-check-circle', 'class' =>'bg-emerald-600 hover:bg-emerald-700'],
            'extravio_courier' =>['label' =>'Extravío courier', 'estado' =>'En Revisión', 'icon' =>'fa-route', 'class' =>'bg-orange-600 hover:bg-orange-700', 'crea_incidencia' =>true, 'comentario_obligatorio' =>true]
        ],
        'Entregado' =>[
            'cerrar' =>['label' =>'Cerrar pedido', 'estado' =>'Cerrado', 'icon' =>'fa-folder-closed', 'class' =>'bg-slate-600 hover:bg-slate-700'],
            'no_recibido' =>['label' =>'No recibido', 'estado' =>'En Revisión', 'icon' =>'fa-question-circle', 'class' =>'bg-orange-600 hover:bg-orange-700', 'crea_incidencia' =>true, 'comentario_obligatorio' =>true],
            'danado_entrega' =>['label' =>'Llegó dañado', 'estado' =>'En Revisión', 'icon' =>'fa-camera', 'class' =>'bg-red-600 hover:bg-red-700', 'crea_incidencia' =>true, 'comentario_obligatorio' =>true]
        ],
        'En Revisión' =>[
            'resolver_reembolso' =>['label' =>'Resolver: reembolso', 'estado' =>'Reembolsado', 'icon' =>'fa-money-bill-wave', 'class' =>'bg-cyan-600 hover:bg-cyan-700', 'comentario_obligatorio' =>true],
            'resolver_reemplazo' =>['label' =>'Resolver: reemplazo', 'estado' =>'Reemplazo generado', 'icon' =>'fa-exchange-alt', 'class' =>'bg-teal-600 hover:bg-teal-700', 'comentario_obligatorio' =>true],
            'cerrar_revision' =>['label' =>'Cerrar revisión', 'estado' =>'Cerrado', 'icon' =>'fa-folder-closed', 'class' =>'bg-slate-600 hover:bg-slate-700', 'comentario_obligatorio' =>true]
        ],
        'Cancelado' =>[],
        'Reembolsado' =>[
            'cerrar' =>['label' =>'Cerrar pedido', 'estado' =>'Cerrado', 'icon' =>'fa-folder-closed', 'class' =>'bg-slate-600 hover:bg-slate-700']
        ],
        'Reemplazo generado' =>[
            'cerrar' =>['label' =>'Cerrar pedido', 'estado' =>'Cerrado', 'icon' =>'fa-folder-closed', 'class' =>'bg-slate-600 hover:bg-slate-700']
        ],
        'Cerrado' =>[]
    ];
    return $acciones[$estado] ?? [];
}
}

if (!function_exists('emxAgregarHistorial')) {
function emxAgregarHistorial($historialJson, $estado, $descripcion, $icono = 'fa-info-circle', $extra = []) {
    $historial = json_decode($historialJson ?: '[]', true);
    if (!is_array($historial)) $historial = [];
    $entrada = array_merge([
        'estado' =>$estado,
        'descripcion' =>$descripcion,
        'fecha' =>date('Y-m-d H:i:s'),
        'icono' =>$icono
    ], $extra);
    $historial[] = $entrada;
    return json_encode($historial, JSON_UNESCAPED_UNICODE);
}
}

if (!function_exists('emxNotificar')) {
function emxNotificar($pdo, $usuario_id, $tipo, $titulo, $mensaje, $enlace = '#', $tipo_enlace = 'ninguno') {
    if (!$usuario_id) return false;
    if (function_exists('enviarNotificacionCliente')) {
        return enviarNotificacionCliente($pdo, $usuario_id, $tipo, $titulo, $mensaje, $enlace, $tipo_enlace);
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, enlace_accion, tipo_enlace, leida, creado_en) VALUES (?, ?, ?, ?, ?, ?, FALSE, NOW())");
        return $stmt->execute([$usuario_id, $tipo, $titulo, $mensaje, $enlace, $tipo_enlace]);
    } catch (Exception $e) {
        error_log('Error notificación: ' . $e->getMessage());
        return false;
    }
}
}


if (!function_exists('emxGarantizarColumnasDevoluciones')) {
function emxGarantizarColumnasDevoluciones($pdo) {
    static $done = false;
    if ($done) return;
    try {
        try {
            $stTipo = $pdo->prepare("SELECT data_type FROM information_schema.columns WHERE table_schema='public' AND table_name='devoluciones' AND column_name='pedido_reemplazo_id' LIMIT 1");
            $stTipo->execute();
            $tipoPedidoReemplazo = $stTipo->fetchColumn();
            if ($tipoPedidoReemplazo && strtolower((string)$tipoPedidoReemplazo) !== 'uuid') {
                $pdo->exec("ALTER TABLE devoluciones DROP COLUMN pedido_reemplazo_id");
            }
        } catch (Throwable $eTipo) {
            // Si no se puede validar el tipo, continuamos con la migración simple.
        }

        $pdo->exec("
            ALTER TABLE devoluciones
                ADD COLUMN IF NOT EXISTS historial_estados JSONB DEFAULT '[]'::jsonb,
                ADD COLUMN IF NOT EXISTS tipo_caso VARCHAR(50) DEFAULT 'devolucion',
                ADD COLUMN IF NOT EXISTS evidencia_tecnico JSONB DEFAULT '[]'::jsonb,
                ADD COLUMN IF NOT EXISTS numero_serie_validado VARCHAR(100),
                ADD COLUMN IF NOT EXISTS numero_serie_devuelto VARCHAR(100),
                ADD COLUMN IF NOT EXISTS tipo_dano VARCHAR(50),
                ADD COLUMN IF NOT EXISTS comentario_tecnico TEXT,
                ADD COLUMN IF NOT EXISTS fecha_inspeccion_tecnica TIMESTAMP,
                ADD COLUMN IF NOT EXISTS tecnico_id UUID REFERENCES usuarios(id),
                ADD COLUMN IF NOT EXISTS motivo_rechazo TEXT,
                ADD COLUMN IF NOT EXISTS codigo_guia VARCHAR(50),
                ADD COLUMN IF NOT EXISTS codigo_etiqueta VARCHAR(50),
                ADD COLUMN IF NOT EXISTS metodo_devolucion VARCHAR(30),
                ADD COLUMN IF NOT EXISTS fecha_recepcion TIMESTAMP,
                ADD COLUMN IF NOT EXISTS solucion_propuesta VARCHAR(50),
                ADD COLUMN IF NOT EXISTS respuesta_usuario VARCHAR(50) DEFAULT 'pendiente',
                ADD COLUMN IF NOT EXISTS producto_id UUID,
                ADD COLUMN IF NOT EXISTS detalle_pedido_id UUID,
                ADD COLUMN IF NOT EXISTS pedido_reemplazo_id UUID REFERENCES pedidos(id),
                ADD COLUMN IF NOT EXISTS series_originales_json JSONB DEFAULT '[]'::jsonb,
                ADD COLUMN IF NOT EXISTS series_reemplazo_json JSONB DEFAULT '[]'::jsonb,
                ADD COLUMN IF NOT EXISTS acta_reemplazo_json JSONB DEFAULT '{}'::jsonb,
                ADD COLUMN IF NOT EXISTS fecha_series_reemplazo TIMESTAMP,
                ADD COLUMN IF NOT EXISTS fraude_detectado BOOLEAN DEFAULT FALSE,
                ADD COLUMN IF NOT EXISTS motivos_fraude JSONB DEFAULT '[]'::jsonb,
                ADD COLUMN IF NOT EXISTS fecha_reemplazo_entregado TIMESTAMP,
                ADD COLUMN IF NOT EXISTS fecha_cierre TIMESTAMP
        ");
        $pdo->exec("UPDATE devoluciones SET historial_estados = '[]'::jsonb WHERE historial_estados IS NULL");
        $pdo->exec("UPDATE devoluciones SET respuesta_usuario = 'pendiente' WHERE respuesta_usuario IS NULL");
    } catch (Throwable $e) {
        error_log('No se pudieron garantizar columnas de devoluciones: ' . $e->getMessage());
    }
    $done = true;
}
}


if (!function_exists('emxSeriesVendidasPedido')) {
function emxSeriesVendidasPedido($pdo, $pedido_id, $producto_id = null) {
    $series = [];
    if (!$pedido_id) return $series;

    $sql = "SELECT dp.producto_id, COALESCE(p.nombre, dp.nombre_producto, 'Producto') AS producto_nombre, dp.numero_serie_vendido
            FROM detalle_pedidos dp
            LEFT JOIN productos p ON p.id = dp.producto_id
            WHERE dp.pedido_id = ?";
    $params = [$pedido_id];
    if (!empty($producto_id)) {
        $sql .= " AND dp.producto_id = ?";
        $params[] = $producto_id;
    }

    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $raw = trim((string)($row['numero_serie_vendido'] ?? ''));
            if ($raw === '') continue;
            $json = json_decode($raw, true);
            $lista = is_array($json) ? $json : [$raw];
            foreach ($lista as $serie) {
                $serie = trim((string)$serie);
                if ($serie === '') continue;
                $series[] = [
                    'producto_id' =>$row['producto_id'] ?? null,
                    'producto' =>$row['producto_nombre'] ?? 'Producto',
                    'serie' =>$serie
                ];
            }
        }
    } catch (Throwable $e) {
        return [];
    }
    return $series;
}
}

if (!function_exists('emxSeriesVendidasTexto')) {
function emxSeriesVendidasTexto($pdo, $pedido_id, $producto_id = null) {
    $series = emxSeriesVendidasPedido($pdo, $pedido_id, $producto_id);
    if (empty($series)) return 'Sin serie registrada';
    $partes = [];
    foreach ($series as $s) {
        $partes[] = ($s['producto'] ? $s['producto'] . ': ' : '') . $s['serie'];
    }
    return implode(' | ', $partes);
}
}

if (!function_exists('emxValidarSerieVendida')) {
function emxValidarSerieVendida($pdo, $pedido_id, $producto_id, $serie_devuelta) {
    $serie_devuelta = trim(strtoupper((string)$serie_devuelta));
    if ($serie_devuelta === '') return true;

    $series = emxSeriesVendidasPedido($pdo, $pedido_id, $producto_id);
    foreach ($series as $s) {
        if (trim(strtoupper((string)$s['serie'])) === $serie_devuelta) return true;
    }

    // Si el detalle no tiene serie registrada, no bloqueamos por serie.
    return empty($series);
}
}

if (!function_exists('emxRegistrarAlertaFraudeDevolucion')) {
function emxRegistrarAlertaFraudeDevolucion($pdo, $devolucion_id, $motivo, $detalle = []) {
    $motivo = (string)$motivo;
    $detalle['motivo'] = $motivo;
    $detalle['fecha'] = date('c');

    try {
        emxGarantizarColumnasDevoluciones($pdo);
        $stmt = $pdo->prepare("SELECT motivos_fraude FROM devoluciones WHERE id = ? FOR UPDATE");
        $stmt->execute([$devolucion_id]);
        $actual = $stmt->fetchColumn();
        $arr = json_decode($actual ?: '[]', true);
        if (!is_array($arr)) $arr = [];
        $arr[] = $detalle;

        $upd = $pdo->prepare("UPDATE devoluciones SET fraude_detectado = TRUE, motivos_fraude = ?::jsonb, updated_at = NOW() WHERE id = ?");
        $upd->execute([json_encode($arr, JSON_UNESCAPED_UNICODE), $devolucion_id]);
    } catch (Throwable $e) {
        error_log('No se pudo registrar alerta de fraude devolución: ' . $e->getMessage());
    }
}
}


if (!function_exists('emxCrearIncidenciaPedido')) {
function emxCrearIncidenciaPedido($pdo, $pedido, $motivo, $descripcion, $estadoInicial = 'pendiente_revision', $tipoCaso = 'incidencia') {
    emxGarantizarColumnasDevoluciones($pdo);
    $stmt = $pdo->prepare("SELECT id FROM devoluciones WHERE pedido_id = ? AND estado NOT IN ('cerrada', 'rechazada', 'reembolsado') LIMIT 1");
    $stmt->execute([$pedido['id']]);
    $existente = $stmt->fetchColumn();
    if ($existente) return $existente;

    $fotos = json_encode([], JSON_UNESCAPED_UNICODE);
    $historial = emxAgregarHistorial('[]', $estadoInicial, 'Incidencia creada desde pedido: ' . $descripcion, 'fa-exclamation-triangle');

    $sql = "INSERT INTO devoluciones
        (pedido_id, usuario_id, motivo, descripcion, fotos_evidencia, estado, tipo_reembolso, costo_envio_retorno, tipo_caso, historial_estados, created_at, updated_at)
        VALUES (?, ?, ?, ?, ?::jsonb, ?, 'pendiente_definir', 0, ?, ?::jsonb, NOW(), NOW())
        RETURNING id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $pedido['id'],
        $pedido['usuario_id'],
        $motivo,
        $descripcion,
        $fotos,
        $estadoInicial,
        $tipoCaso,
        $historial
    ]);
    return $stmt->fetchColumn();
}
}

if (!function_exists('emxEjecutarAccionPedido')) {
function emxEjecutarAccionPedido($pdo, $pedido_id, $accion, $admin_id, $comentario = '') {
    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ? FOR UPDATE");
    $stmt->execute([$pedido_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$pedido) throw new Exception('Pedido no encontrado.');

    $acciones = emxAccionesPedido($pedido['estado']);
    if (!isset($acciones[$accion])) {
        throw new Exception("La acción no es válida para el estado actual del pedido ({$pedido['estado']}).");
    }

    $meta = $acciones[$accion];
    if (!empty($meta['comentario_obligatorio']) && trim($comentario) === '') {
        throw new Exception('Esta acción requiere comentario del administrador.');
    }

    $nuevoEstado = $meta['estado'];
    $descripcion = $meta['label'] . '. ' . (trim($comentario) !== '' ? 'Comentario: ' . trim($comentario) : 'Acción registrada por admin.');
    $historial = emxAgregarHistorial($pedido['historial_estados'] ?? '[]', $nuevoEstado, $descripcion, $meta['icon'] ?? 'fa-edit', ['admin_id' =>$admin_id, 'accion' =>$accion]);

    $sql = "UPDATE pedidos SET estado = ?, historial_estados = ?::jsonb";
    $params = [$nuevoEstado, $historial];
    if ($nuevoEstado === 'Pago confirmado') {
        $sql .= ", estado_pago = 'aprobado'";
    } elseif ($nuevoEstado === 'Cancelado') {
        $sql .= ", estado_pago = 'cancelado', cancelado_en = NOW(), cancelado_por = ?, motivo_cancelacion = COALESCE(NULLIF(?, ''), motivo_cancelacion)";
        $params[] = $admin_id;
        $params[] = trim($comentario);
    }
    if ($nuevoEstado === 'Entregado') {
        $sql .= ", confirmacion_cliente_estado = COALESCE(NULLIF(confirmacion_cliente_estado, ''), 'pendiente'), fecha_limite_confirmacion = COALESCE(fecha_limite_confirmacion, NOW() + INTERVAL '7 days')";
    }
    if ($accion === 'no_recibido') {
        $sql .= ", confirmacion_cliente_estado = 'no_recibido'";
    } elseif ($accion === 'danado_entrega') {
        $sql .= ", confirmacion_cliente_estado = 'llego_danado'";
    }
    $sql .= " WHERE id = ?";
    $params[] = $pedido_id;
    $pdo->prepare($sql)->execute($params);

    if ($nuevoEstado === 'Pago confirmado' && function_exists('emxGenerarFacturaPedido')) {
        emxGenerarFacturaPedido($pdo, $pedido_id, true);
        emxNotificar($pdo, $pedido['usuario_id'], 'factura_emitida', 'Factura emitida', 'Tu pago fue aprobado y la factura fue generada.', 'mi_cuenta.php?seccion=pedidos', 'pedido');
    }
    if ($nuevoEstado === 'Cancelado' && function_exists('emxLiberarInventarioPedidoCancelado')) {
        emxLiberarInventarioPedidoCancelado($pdo, $pedido_id);
    }

    $incidencia_id = null;
    if (!empty($meta['crea_incidencia'])) {
        $motivo = 'incidencia_general';
        $estadoInicial = 'pendiente_revision';
        $tipoCaso = 'incidencia';
        if ($accion === 'extravio_courier') {
            $motivo = 'extravio_courier';
            $estadoInicial = 'investigacion_courier';
            $tipoCaso = 'incidencia_courier';
        } elseif ($accion === 'no_recibido') {
            $motivo = 'no_recibido';
            $estadoInicial = 'investigacion_courier';
            $tipoCaso = 'incidencia_courier';
        } elseif ($accion === 'danado_entrega') {
            $motivo = 'danado_envio';
            $estadoInicial = 'pendiente_revision';
            $tipoCaso = 'incidencia_entrega';
        } elseif ($accion === 'problema_stock') {
            $motivo = 'incidencia_stock';
            $estadoInicial = 'pendiente_revision';
            $tipoCaso = 'incidencia_interna';
        }
        $incidencia_id = emxCrearIncidenciaPedido($pdo, $pedido, $motivo, $descripcion, $estadoInicial, $tipoCaso);
        emxNotificar($pdo, $pedido['usuario_id'], 'incidencia_pedido', 'Caso abierto en tu pedido', 'Hemos abierto una incidencia para revisar tu pedido. Nuestro equipo te notificará la resolución.', 'mi_cuenta.php?seccion=devoluciones', 'devolucion');
    }

    return ['estado' =>$nuevoEstado, 'incidencia_id' =>$incidencia_id];
}
}


if (!function_exists('emxGarantizarColumnasPedidosReemplazo')) {
function emxGarantizarColumnasPedidosReemplazo($pdo) {
    static $done = false;
    if ($done) return;
    try {
        $pdo->exec("\n            ALTER TABLE pedidos\n                ADD COLUMN IF NOT EXISTS fecha_estimada_entrega TIMESTAMP,\n                ADD COLUMN IF NOT EXISTS prioridad_entrega VARCHAR(30) DEFAULT 'normal',\n                ADD COLUMN IF NOT EXISTS mensaje_logistico TEXT,\n                ADD COLUMN IF NOT EXISTS fecha_reestimada_en TIMESTAMP,\n                ADD COLUMN IF NOT EXISTS motivo_reemplazo VARCHAR(100),\n                ADD COLUMN IF NOT EXISTS pedido_original_id UUID REFERENCES pedidos(id),\n                ADD COLUMN IF NOT EXISTS historial_estados JSONB DEFAULT '[]'::jsonb\n        ");

        $pdo->exec("\n            ALTER TABLE detalle_pedidos\n                ADD COLUMN IF NOT EXISTS numero_serie_vendido TEXT,\n                ADD COLUMN IF NOT EXISTS es_reemplazo BOOLEAN DEFAULT FALSE,\n                ADD COLUMN IF NOT EXISTS detalle_original_id UUID,\n                ADD COLUMN IF NOT EXISTS series_originales_json JSONB DEFAULT '[]'::jsonb,\n                ADD COLUMN IF NOT EXISTS series_reemplazo_json JSONB DEFAULT '[]'::jsonb,\n                ADD COLUMN IF NOT EXISTS trazabilidad_reemplazo_json JSONB DEFAULT '{}'::jsonb\n        ");
    } catch (Throwable $e) {
        error_log('No se pudieron garantizar columnas de pedidos/reemplazo: ' . $e->getMessage());
    }
    $done = true;
}
}

if (!function_exists('emxPedidoEsPrime')) {
function emxPedidoEsPrime($pdo, $usuario_id) {
    if (!$usuario_id) return false;
    try {
        $stmt = $pdo->prepare("\n            SELECT COALESCE(pl.es_prime, FALSE)\n            FROM usuarios u\n            LEFT JOIN planes pl ON pl.id = u.plan_id\n            WHERE u.id = ?\n            LIMIT 1\n        ");
        $stmt->execute([$usuario_id]);
        $val = $stmt->fetchColumn();
        return ($val === true || $val === 't' || $val === '1' || $val === 1);
    } catch (Throwable $e) {
        return false;
    }
}
}

if (!function_exists('emxPedidoTieneStockReemplazo')) {
function emxPedidoTieneStockReemplazo($pdo, $pedido_id, $sucursal_id = null) {
    try {
        $stmt = $pdo->prepare("\n            SELECT producto_id, cantidad\n            FROM detalle_pedidos\n            WHERE pedido_id = ?\n        ");
        $stmt->execute([$pedido_id]);
        $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (empty($items)) return true;

        foreach ($items as $it) {
            $productoId = $it['producto_id'] ?? null;
            $cantidad = max(1, (int)($it['cantidad'] ?? 1));
            if (!$productoId) continue;

            $stockSucursal = null;
            if (!empty($sucursal_id)) {
                try {
                    $st = $pdo->prepare("SELECT COALESCE(stock,0) FROM inventario_sucursal WHERE sucursal_id = ? AND producto_id = ? LIMIT 1");
                    $st->execute([$sucursal_id, $productoId]);
                    $stockSucursal = $st->fetchColumn();
                } catch (Throwable $e) {
                    $stockSucursal = null;
                }
            }

            if ($stockSucursal !== null && $stockSucursal !== false) {
                if ((int)$stockSucursal < $cantidad) return false;
                continue;
            }

            try {
                $sg = $pdo->prepare("SELECT COALESCE(stock_actual_global,0) FROM productos WHERE id = ? LIMIT 1");
                $sg->execute([$productoId]);
                if ((int)($sg->fetchColumn() ?: 0) < $cantidad) return false;
            } catch (Throwable $e) {
                // Si no podemos validar stock, no bloqueamos; solo evitamos alargar la fecha sin base.
            }
        }
    } catch (Throwable $e) {
        return true;
    }
    return true;
}
}

if (!function_exists('emxCalcularEstimacionPedidoReemplazo')) {
function emxCalcularEstimacionPedidoReemplazo($pdo, array $pedidoOriginal, $modo = 'preparacion') {
    $distancia = max(0, (float)($pedidoOriginal['distancia_km'] ?? 0));
    $usuarioId = $pedidoOriginal['usuario_id'] ?? null;
    $esPrime = emxPedidoEsPrime($pdo, $usuarioId);
    $sucursalId = $pedidoOriginal['sucursal_asignada_id'] ?? null;
    $hayStock = emxPedidoTieneStockReemplazo($pdo, $pedidoOriginal['id'] ?? null, $sucursalId);

    // Días base desde el momento en que se genera o se mueve el reemplazo.
    // Prime siempre tiene prioridad logística, pero sin fechas imposibles.
    if ($modo === 'transito') {
        if ($distancia <= 30) $dias = $esPrime ? 1 : 1;
        elseif ($distancia <= 150) $dias = $esPrime ? 1 : 2;
        elseif ($distancia <= 400) $dias = $esPrime ? 2 : 3;
        else $dias = $esPrime ? 3 : 5;
    } else {
        if ($distancia <= 30) $dias = $esPrime ? 1 : 2;
        elseif ($distancia <= 150) $dias = $esPrime ? 2 : 3;
        elseif ($distancia <= 400) $dias = $esPrime ? 3 : 5;
        else $dias = $esPrime ? 5 : 7;
    }

    // Si el reemplazo no está disponible inmediatamente, se suma colchón de reposición.
    if (!$hayStock) {
        $dias += $esPrime ? 2 : 3;
    }

    // Corte operativo: si se crea al final del día, sale como gestión del día siguiente.
    $hora = (int)date('G');
    if ($hora >= 17 && $modo !== 'transito') {
        $dias += 1;
    }

    $fecha = new DateTime('now', new DateTimeZone('America/Guayaquil'));
    $fecha->modify('+' . max(1, (int)$dias) . ' days');
    $fecha->setTime(18, 0, 0);

    $prioridad = $esPrime ? 'prime_prioritario' : 'normal';
    $tipoDistancia = $distancia <= 30 ? 'entrega local' : ($distancia <= 150 ? 'entrega provincial' : ($distancia <= 400 ? 'entrega interprovincial' : 'entrega nacional'));
    $mensaje = ($esPrime ? 'Prioridad Prime aplicada. ' : 'Prioridad estándar. ')
        . 'Reemplazo con ' . $tipoDistancia
        . ($hayStock ? ' y stock disponible.' : ' con tiempo adicional por reposición de stock.')
        . ' Nueva fecha estimada: ' . $fecha->format('d/m/Y') . '.';

    return [
        'fecha_estimada' =>$fecha->format('Y-m-d H:i:s'),
        'prioridad' =>$prioridad,
        'mensaje' =>$mensaje,
        'dias' =>max(1, (int)$dias),
        'es_prime' =>$esPrime,
        'hay_stock' =>$hayStock
    ];
}
}

if (!function_exists('emxRecalcularEstimacionPedidoReemplazo')) {
function emxRecalcularEstimacionPedidoReemplazo($pdo, $pedido_reemplazo_id, $modo = 'preparacion') {
    if (!$pedido_reemplazo_id) return null;
    emxGarantizarColumnasPedidosReemplazo($pdo);

    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ? LIMIT 1");
    $stmt->execute([$pedido_reemplazo_id]);
    $pedido = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$pedido) return null;

    // Para pedidos de reemplazo, el pedido original queda en pedido_original_id.
    $pedidoBase = $pedido;
    if (!empty($pedido['pedido_original_id'])) {
        $stOrig = $pdo->prepare("SELECT * FROM pedidos WHERE id = ? LIMIT 1");
        $stOrig->execute([$pedido['pedido_original_id']]);
        $orig = $stOrig->fetch(PDO::FETCH_ASSOC);
        if ($orig) $pedidoBase = $orig;
    }

    $estimacion = emxCalcularEstimacionPedidoReemplazo($pdo, $pedidoBase, $modo);
    $historial = emxAgregarHistorial(
        $pedido['historial_estados'] ?? '[]',
        'Fecha estimada recalculada',
        $estimacion['mensaje'],
        'fa-calendar-check',
        ['prioridad' =>$estimacion['prioridad'], 'modo' =>$modo]
    );

    $upd = $pdo->prepare("\n        UPDATE pedidos\n        SET fecha_estimada_entrega = ?,\n            prioridad_entrega = ?,\n            mensaje_logistico = ?,\n            fecha_reestimada_en = NOW(),\n            historial_estados = ?::jsonb\n        WHERE id = ?\n    ");
    $upd->execute([
        $estimacion['fecha_estimada'],
        $estimacion['prioridad'],
        $estimacion['mensaje'],
        $historial,
        $pedido_reemplazo_id
    ]);

    return $estimacion;
}
}


if (!function_exists('emxGenerarSerieUnica')) {
function emxGenerarSerieUnica($marca) {
    $year = date('Y');
    $brandCode = strtoupper(substr(preg_replace('/[^A-Za-z]/', '', (string)$marca), 0, 3));
    if ($brandCode === '') $brandCode = 'EMX';
    $hash = strtoupper(substr(md5(uniqid((string)mt_rand(), true)), 0, 8));
    return "{$brandCode}-{$year}-{$hash}";
}
}

if (!function_exists('emxSeriesDesdeTextoDetalle')) {
function emxSeriesDesdeTextoDetalle($raw) {
    $raw = trim((string)$raw);
    if ($raw === '') return [];
    $json = json_decode($raw, true);
    $arr = is_array($json) ? $json : [$raw];
    $out = [];
    foreach ($arr as $s) {
        $s = trim((string)$s);
        if ($s !== '') $out[] = $s;
    }
    return $out;
}
}

if (!function_exists('emxSerieEsPlaceholder')) {
function emxSerieEsPlaceholder($serie) {
    $serie = strtoupper(trim((string)$serie));
    return $serie === ''
        || str_starts_with($serie, 'RESERVADO_ENTREGA_TOTAL_')
        || str_starts_with($serie, 'PENDIENTE_BACKORDER_')
        || str_starts_with($serie, 'PENDIENTE_REEMPLAZO_');
}
}

if (!function_exists('emxSeriesFisicasDesdeDetalle')) {
function emxSeriesFisicasDesdeDetalle($raw) {
    $series = [];
    foreach (emxSeriesDesdeTextoDetalle($raw) as $serie) {
        if (!emxSerieEsPlaceholder($serie)) $series[] = $serie;
    }
    return $series;
}
}

if (!function_exists('emxBuscarDetallePorSerieVendida')) {
function emxBuscarDetallePorSerieVendida($pdo, $pedido_id, $serie) {
    $serieBuscada = trim(strtoupper((string)$serie));
    if ($serieBuscada === '') return null;

    $st = $pdo->prepare("
        SELECT dp.*, COALESCE(p.nombre, dp.nombre_producto, 'Producto') AS producto_nombre, m.nombre AS marca_nombre
        FROM detalle_pedidos dp
        LEFT JOIN productos p ON p.id = dp.producto_id
        LEFT JOIN marcas m ON m.id = p.marca_id
        WHERE dp.pedido_id = ?
    ");
    $st->execute([$pedido_id]);

    foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $row) {
        foreach (emxSeriesDesdeTextoDetalle($row['numero_serie_vendido'] ?? '') as $serieActual) {
            if (trim(strtoupper((string)$serieActual)) === $serieBuscada) {
                return $row;
            }
        }
    }
    return null;
}
}

if (!function_exists('emxGenerarSeriesReemplazoProducto')) {
function emxGenerarSeriesReemplazoProducto($pdo, $producto_id, $cantidad) {
    $cantidad = max(1, (int)$cantidad);
    $marca = 'EMX';
    try {
        $st = $pdo->prepare("
            SELECT COALESCE(m.nombre, 'EMX') AS marca
            FROM productos p
            LEFT JOIN marcas m ON m.id = p.marca_id
            WHERE p.id = ?
            LIMIT 1
        ");
        $st->execute([$producto_id]);
        $marca = $st->fetchColumn() ?: 'EMX';
    } catch (Throwable $e) {}

    $series = [];
    for ($i = 0; $i < $cantidad; $i++) {
        $series[] = emxGenerarSerieUnica($marca);
    }
    return $series;
}
}

if (!function_exists('emxItemsReemplazoDesdeDevolucion')) {
function emxItemsReemplazoDesdeDevolucion($pdo, array $devolucion) {
    $pedidoId = $devolucion['pedido_id'] ?? null;
    if (!$pedidoId) return [];

    $baseSql = "
        SELECT dp.*, COALESCE(p.nombre, dp.nombre_producto, 'Producto de reemplazo') AS producto_nombre, p.iva_porcentaje, m.nombre AS marca_nombre
        FROM detalle_pedidos dp
        LEFT JOIN productos p ON p.id = dp.producto_id
        LEFT JOIN marcas m ON m.id = p.marca_id
        WHERE dp.pedido_id = ?
    ";

    // 1) Si el técnico validó una serie, esa serie manda.
    $serieValidada = trim((string)($devolucion['numero_serie_validado'] ?? $devolucion['numero_serie_devuelto'] ?? ''));
    if ($serieValidada !== '') {
        $detalle = emxBuscarDetallePorSerieVendida($pdo, $pedidoId, $serieValidada);
        if ($detalle) return [$detalle];
    }

    // 2) Si la devolución ya tiene detalle/producto objetivo, reemplazamos solo ese detalle/producto.
    if (!empty($devolucion['detalle_pedido_id'])) {
        $st = $pdo->prepare($baseSql . " AND dp.id = ? LIMIT 1");
        $st->execute([$pedidoId, $devolucion['detalle_pedido_id']]);
        $row = $st->fetch(PDO::FETCH_ASSOC);
        if ($row) return [$row];
    }

    if (!empty($devolucion['producto_id'])) {
        $st = $pdo->prepare($baseSql . " AND dp.producto_id = ?");
        $st->execute([$pedidoId, $devolucion['producto_id']]);
        $rows = $st->fetchAll(PDO::FETCH_ASSOC);
        if ($rows) return $rows;
    }

    // 3) Caso heredado: la devolución es del pedido completo y no capturó producto.
    $st = $pdo->prepare($baseSql);
    $st->execute([$pedidoId]);
    return $st->fetchAll(PDO::FETCH_ASSOC);
}
}

if (!function_exists('emxResumenSeriesReemplazoDevolucion')) {
function emxResumenSeriesReemplazoDevolucion($pdo, $devolucion_id) {
    try {
        $st = $pdo->prepare("SELECT series_reemplazo_json FROM devoluciones WHERE id = ? LIMIT 1");
        $st->execute([$devolucion_id]);
        $raw = $st->fetchColumn();
        $arr = json_decode($raw ?: '[]', true);
        if (!is_array($arr) || empty($arr)) return '';

        $partes = [];
        foreach ($arr as $item) {
            $producto = $item['producto'] ?? 'Producto';
            $series = $item['series_nuevas'] ?? $item['series'] ?? [];
            if (!is_array($series)) $series = [$series];
            $series = array_values(array_filter(array_map('strval', $series)));
            if ($series) $partes[] = $producto . ': ' . implode(', ', $series);
        }
        return implode(' | ', $partes);
    } catch (Throwable $e) {
        return '';
    }
}
}

if (!function_exists('emxAsegurarSeriesPedidoReemplazo')) {
function emxAsegurarSeriesPedidoReemplazo($pdo, $pedido_reemplazo_id, array $devolucion) {
    if (!$pedido_reemplazo_id) return [];

    emxGarantizarColumnasPedidosReemplazo($pdo);
    emxGarantizarColumnasDevoluciones($pdo);

    $st = $pdo->prepare("
        SELECT dp.*, COALESCE(p.nombre, dp.nombre_producto, 'Producto de reemplazo') AS producto_nombre
        FROM detalle_pedidos dp
        LEFT JOIN productos p ON p.id = dp.producto_id
        WHERE dp.pedido_id = ?
    ");
    $st->execute([$pedido_reemplazo_id]);
    $rows = $st->fetchAll(PDO::FETCH_ASSOC);

    $trazabilidad = [];
    $seriesOriginalesResumen = [];
    $seriesReemplazoResumen = [];

    foreach ($rows as $row) {
        $seriesActuales = emxSeriesFisicasDesdeDetalle($row['numero_serie_vendido'] ?? '');
        $cantidad = max(1, (int)($row['cantidad'] ?? 1));
        if (count($seriesActuales) < $cantidad) {
            $seriesNuevas = emxGenerarSeriesReemplazoProducto($pdo, $row['producto_id'], $cantidad);
            $seriesJson = json_encode($seriesNuevas, JSON_UNESCAPED_UNICODE);
            $traza = [
                'tipo' => 'reemplazo_mismo_producto',
                'devolucion_id' => $devolucion['id'] ?? null,
                'pedido_original_id' => $devolucion['pedido_id'] ?? null,
                'pedido_reemplazo_id' => $pedido_reemplazo_id,
                'producto_id' => $row['producto_id'],
                'producto' => $row['producto_nombre'],
                'series_originales' => [],
                'series_nuevas' => $seriesNuevas,
                'fecha' => date('c'),
                'factura_original_se_conserva' => true
            ];
            $pdo->prepare("
                UPDATE detalle_pedidos
                SET numero_serie_vendido = ?,
                    es_reemplazo = TRUE,
                    series_reemplazo_json = ?::jsonb,
                    trazabilidad_reemplazo_json = ?::jsonb
                WHERE id = ?
            ")->execute([$seriesJson, $seriesJson, json_encode($traza, JSON_UNESCAPED_UNICODE), $row['id']]);
            $seriesActuales = $seriesNuevas;
        }

        $seriesReemplazoResumen[] = [
            'producto_id' => $row['producto_id'],
            'producto' => $row['producto_nombre'],
            'series_nuevas' => $seriesActuales
        ];
        $trazabilidad[] = [
            'producto_id' => $row['producto_id'],
            'producto' => $row['producto_nombre'],
            'series_nuevas' => $seriesActuales
        ];
    }

    if (!empty($devolucion['id']) && !empty($seriesReemplazoResumen)) {
        $pdo->prepare("
            UPDATE devoluciones
            SET series_reemplazo_json = ?::jsonb,
                acta_reemplazo_json = ?::jsonb,
                fecha_series_reemplazo = COALESCE(fecha_series_reemplazo, NOW())
            WHERE id = ?
        ")->execute([
            json_encode($seriesReemplazoResumen, JSON_UNESCAPED_UNICODE),
            json_encode(['pedido_reemplazo_id'=>$pedido_reemplazo_id, 'items'=>$trazabilidad, 'fecha'=>date('c'), 'factura_original_se_conserva'=>true], JSON_UNESCAPED_UNICODE),
            $devolucion['id']
        ]);
    }

    return $seriesReemplazoResumen;
}
}

if (!function_exists('emxCrearPedidoReemplazoDesdeDevolucion')) {
function emxCrearPedidoReemplazoDesdeDevolucion($pdo, $devolucion, $comentario = '') {
    emxGarantizarColumnasPedidosReemplazo($pdo);
    emxGarantizarColumnasDevoluciones($pdo);

    if (!empty($devolucion['pedido_reemplazo_id'])) {
        emxAsegurarSeriesPedidoReemplazo($pdo, $devolucion['pedido_reemplazo_id'], $devolucion);
        emxRecalcularEstimacionPedidoReemplazo($pdo, $devolucion['pedido_reemplazo_id'], 'preparacion');
        return $devolucion['pedido_reemplazo_id'];
    }

    $stmt = $pdo->prepare("SELECT * FROM pedidos WHERE id = ?");
    $stmt->execute([$devolucion['pedido_id']]);
    $pedidoOriginal = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$pedidoOriginal) throw new Exception('Pedido original no encontrado para crear reemplazo.');

    $items = emxItemsReemplazoDesdeDevolucion($pdo, $devolucion);
    if (empty($items)) throw new Exception('No se encontraron productos del pedido original para crear el reemplazo.');

    $estimacion = emxCalcularEstimacionPedidoReemplazo($pdo, $pedidoOriginal, 'preparacion');
    $historial = emxAgregarHistorial(
        '[]',
        'En Preparación',
        'Pedido de reemplazo generado desde devolución #' . substr($devolucion['id'], 0, 8) . '. La factura original se conserva; se asignan nuevas series físicas al reemplazo. ' . $estimacion['mensaje'],
        'fa-exchange-alt',
        [
            'pedido_original_id' =>$pedidoOriginal['id'],
            'prioridad' =>$estimacion['prioridad'],
            'fecha_estimada_entrega' =>$estimacion['fecha_estimada'],
            'factura_original_se_conserva' =>true
        ]
    );

    $stmt = $pdo->prepare("INSERT INTO pedidos
        (usuario_id, nombre_cliente, email, telefono, direccion, ciudad, codigo_postal, provincia, subtotal, iva_total, total, estado, metodo_pago, sucursal_asignada_id, direccion_envio_id, distancia_km, fecha_estimada_entrega, prioridad_entrega, mensaje_logistico, created_at, motivo_reemplazo, pedido_original_id, historial_estados, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 0, 0, 0, 'En Preparación', 'Reemplazo', ?, ?, ?, ?, ?, ?, NOW(), ?, ?, ?::jsonb, NOW())
        RETURNING id");
    $stmt->execute([
        $pedidoOriginal['usuario_id'] ?? $devolucion['usuario_id'],
        $pedidoOriginal['nombre_cliente'] ?? 'Cliente',
        $pedidoOriginal['email'] ?? '',
        $pedidoOriginal['telefono'] ?? '',
        $pedidoOriginal['direccion'] ?? '',
        $pedidoOriginal['ciudad'] ?? '',
        $pedidoOriginal['codigo_postal'] ?? null,
        $pedidoOriginal['provincia'] ?? null,
        $pedidoOriginal['sucursal_asignada_id'] ?? null,
        $pedidoOriginal['direccion_envio_id'] ?? null,
        $pedidoOriginal['distancia_km'] ?? null,
        $estimacion['fecha_estimada'],
        $estimacion['prioridad'],
        $estimacion['mensaje'],
        'DEVOLUCION_APROBADA',
        $pedidoOriginal['id'],
        $historial
    ]);
    $nuevoPedidoId = $stmt->fetchColumn();

    $trazabilidad = [];
    $seriesOriginalesResumen = [];
    $seriesReemplazoResumen = [];

    foreach ($items as $item) {
        $nombreProducto = $item['nombre_producto'] ?? $item['producto_nombre'] ?? 'Producto de reemplazo';
        $cantidad = max(1, (int)($item['cantidad'] ?? 1));
        $precio = (float)($item['precio_unitario'] ?? 0);
        $iva = (float)($item['iva_porcentaje'] ?? 15);
        $total = isset($item['total']) ? (float)$item['total'] : ($precio * $cantidad * (1 + ($iva / 100)));

        $seriesOriginales = emxSeriesFisicasDesdeDetalle($item['numero_serie_vendido'] ?? '');
        if (empty($seriesOriginales) && !empty($devolucion['numero_serie_validado'])) {
            $seriesOriginales = [(string)$devolucion['numero_serie_validado']];
        }

        $seriesNuevas = emxGenerarSeriesReemplazoProducto($pdo, $item['producto_id'], $cantidad);
        $seriesNuevasJson = json_encode($seriesNuevas, JSON_UNESCAPED_UNICODE);
        $seriesOriginalesJson = json_encode($seriesOriginales, JSON_UNESCAPED_UNICODE);

        $traza = [
            'tipo' => 'reemplazo_mismo_producto',
            'devolucion_id' => $devolucion['id'] ?? null,
            'pedido_original_id' => $pedidoOriginal['id'],
            'pedido_reemplazo_id' => $nuevoPedidoId,
            'detalle_original_id' => $item['id'] ?? null,
            'producto_id' => $item['producto_id'],
            'producto' => $nombreProducto,
            'series_originales' => $seriesOriginales,
            'series_nuevas' => $seriesNuevas,
            'factura_original_se_conserva' => true,
            'genera_nueva_factura' => false,
            'fecha' => date('c'),
            'comentario' => $comentario
        ];

        $stmtIns = $pdo->prepare("INSERT INTO detalle_pedidos
            (pedido_id, producto_id, nombre_producto, cantidad, precio_unitario, iva_porcentaje, total, sucursal_origen_id, numero_serie_vendido, es_reemplazo, detalle_original_id, series_originales_json, series_reemplazo_json, trazabilidad_reemplazo_json)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, TRUE, ?, ?::jsonb, ?::jsonb, ?::jsonb)
            RETURNING id");
        $stmtIns->execute([
            $nuevoPedidoId,
            $item['producto_id'],
            $nombreProducto,
            $cantidad,
            $precio,
            $iva,
            $total,
            $item['sucursal_origen_id'] ?? null,
            $seriesNuevasJson,
            $item['id'] ?? null,
            $seriesOriginalesJson,
            $seriesNuevasJson,
            json_encode($traza, JSON_UNESCAPED_UNICODE)
        ]);
        $detalleReemplazoId = $stmtIns->fetchColumn();

        if ($detalleReemplazoId && function_exists('emxAplicarGarantiaADetalle')) {
            emxAplicarGarantiaADetalle($pdo, $detalleReemplazoId, $item['producto_id'], date('Y-m-d'));
        }

        $seriesOriginalesResumen[] = [
            'producto_id' => $item['producto_id'],
            'producto' => $nombreProducto,
            'series_originales' => $seriesOriginales
        ];
        $seriesReemplazoResumen[] = [
            'producto_id' => $item['producto_id'],
            'producto' => $nombreProducto,
            'series_nuevas' => $seriesNuevas
        ];
        $trazabilidad[] = $traza;
    }

    $acta = [
        'tipo' => 'acta_reemplazo_mismo_producto',
        'devolucion_id' => $devolucion['id'] ?? null,
        'pedido_original_id' => $pedidoOriginal['id'],
        'pedido_reemplazo_id' => $nuevoPedidoId,
        'factura_original_se_conserva' => true,
        'genera_nueva_factura' => false,
        'items' => $trazabilidad,
        'fecha' => date('c')
    ];

    if (!empty($devolucion['id'])) {
        $pdo->prepare("
            UPDATE devoluciones
            SET pedido_reemplazo_id = ?,
                series_originales_json = ?::jsonb,
                series_reemplazo_json = ?::jsonb,
                acta_reemplazo_json = ?::jsonb,
                fecha_series_reemplazo = NOW(),
                producto_id = COALESCE(producto_id, ?),
                detalle_pedido_id = COALESCE(detalle_pedido_id, ?)
            WHERE id = ?
        ")->execute([
            $nuevoPedidoId,
            json_encode($seriesOriginalesResumen, JSON_UNESCAPED_UNICODE),
            json_encode($seriesReemplazoResumen, JSON_UNESCAPED_UNICODE),
            json_encode($acta, JSON_UNESCAPED_UNICODE),
            $items[0]['producto_id'] ?? null,
            $items[0]['id'] ?? null,
            $devolucion['id']
        ]);
    }

    return $nuevoPedidoId;
}
}

if (!function_exists('emxEjecutarAccionDevolucion')) {
function emxEjecutarAccionDevolucion($pdo, $post, $admin_id, $rol_actual = 'ADMIN') {
    $dev_id = $post['dev_id'] ?? null;
    $accion = $post['accion_flujo'] ?? null;
    $comentario = trim($post['comentario_admin'] ?? '');
    $solucion_propuesta = $post['solucion_propuesta'] ?? null;
    $diagnostico_tecnico = $post['diagnostico_tecnico'] ?? null;
    $numero_serie_tecnico = trim($post['numero_serie_tecnico'] ?? '');
    $comentario_tecnico = trim($post['comentario_tecnico'] ?? '');

    if (!$dev_id || !$accion) throw new Exception('Datos inválidos para procesar devolución.');

    emxGarantizarColumnasDevoluciones($pdo);

    $stmt = $pdo->prepare("SELECT d.* FROM devoluciones d WHERE d.id = ? FOR UPDATE");
    $stmt->execute([$dev_id]);
    $dev = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$dev) throw new Exception('Devolución o incidencia no encontrada.');

    $estadoActual = emxEstadoDevolucionNormalizado($dev['estado']);
    $acciones = emxAccionesDevolucion($estadoActual, $dev['motivo'] ?? '');
    if (!isset($acciones[$accion])) {
        throw new Exception("La acción no es válida para el estado actual ({$estadoActual}).");
    }

    $meta = $acciones[$accion];
    if (!empty($meta['comentario_obligatorio']) && $comentario === '') {
        throw new Exception('Esta acción requiere comentario del administrador.');
    }

    // La solución al cliente solo se permite cuando el admin ya terminó la inspección.
    // En ese punto el cliente no decide libremente: solo elige entre opciones habilitadas.
    $opcionesSolucionCliente = ['opcion_reembolso', 'opcion_cambio', 'opcion_reembolso_cambio'];
    if (!empty($meta['requiere_solucion'])) {
        if (!in_array((string)$solucion_propuesta, $opcionesSolucionCliente, true)) {
            throw new Exception('Debes indicar qué opción verá el cliente: reembolso, cambio por el mismo producto o ambas opciones.');
        }
        $permitidasPorCaso = emxSolucionesPermitidasDevolucion($dev['motivo'] ?? '', $dev['tipo_caso'] ?? '');
        if (!in_array((string)$solucion_propuesta, $permitidasPorCaso, true)) {
            $nombresPermitidos = array_map('emxTextoSolucionPropuesta', $permitidasPorCaso);
            throw new Exception('Esa solución no corresponde al flujo de este motivo. Opciones permitidas: ' . implode(', ', $nombresPermitidos) . '.');
        }
    } elseif ($accion !== 'ofrecer_solucion') {
        $estadosQuePermitenSolucion = ['investigacion_courier', 'reclamo_courier', 'garantia_proveedor', 'aprobado_reembolso', 'aprobado_cambio'];
        if (!in_array($estadoActual, $estadosQuePermitenSolucion, true)) {
            $solucion_propuesta = null;
        }
    }

    $nuevoEstado = $meta['estado'];
    $productoId = $dev['producto_id'] ?? null;

    $serieCoincideConVenta = true;
    $detalleDetectadoPorSerie = null;
    if ($numero_serie_tecnico !== '') {
        $detalleDetectadoPorSerie = emxBuscarDetallePorSerieVendida($pdo, $dev['pedido_id'], $numero_serie_tecnico);
        if ($detalleDetectadoPorSerie) {
            $productoId = $detalleDetectadoPorSerie['producto_id'] ?? $productoId;
            $dev['producto_id'] = $productoId;
            $dev['detalle_pedido_id'] = $detalleDetectadoPorSerie['id'] ?? ($dev['detalle_pedido_id'] ?? null);
            $serieCoincideConVenta = true;
        } else {
            $serieCoincideConVenta = false;
            emxRegistrarAlertaFraudeDevolucion($pdo, $dev_id, 'serie_no_vendida', [
                'serie_devuelta' =>$numero_serie_tecnico,
                'series_vendidas' =>emxSeriesVendidasTexto($pdo, $dev['pedido_id'])
            ]);
            if ($accion !== 'rechazar') {
                throw new Exception("Alerta de fraude: la serie física '{$numero_serie_tecnico}' no coincide con ninguna serie vendida en este pedido. El caso debe revisarse o rechazarse con comentario.");
            }
        }
    }

    if (!empty($meta['requiere_diagnostico'])) {
        if ($diagnostico_tecnico === '' || $diagnostico_tecnico === null) {
            throw new Exception('Debes registrar el diagnóstico técnico antes de resolver la inspección.');
        }
    }

    $codigoGuia = null;
    if ($accion === 'marcar_en_camino' && empty($dev['codigo_guia'])) {
        $codigoGuia = 'EMX-RET-' . strtoupper(substr(md5(uniqid('', true)), 0, 6));
    }

    $nuevoPedidoId = null;
    if ($accion === 'crear_reemplazo') {
        $nuevoPedidoId = emxCrearPedidoReemplazoDesdeDevolucion($pdo, $dev, $comentario);
    }

    if ($accion === 'garantia_proveedor' && $productoId) {
        $stmtProv = $pdo->prepare("SELECT proveedor_id FROM producto_proveedor WHERE producto_id = ? LIMIT 1");
        $stmtProv->execute([$productoId]);
        $proveedorId = $stmtProv->fetchColumn();
        if ($proveedorId) {
            $stmtExiste = $pdo->prepare("SELECT id FROM reclamos_proveedor WHERE devolucion_id = ? LIMIT 1");
            $stmtExiste->execute([$dev_id]);
            if (!$stmtExiste->fetchColumn()) {
                $stmtReclamo = $pdo->prepare("INSERT INTO reclamos_proveedor (devolucion_id, producto_id, numero_serie, proveedor_id, estado, evidencia_fotos, comentario_tecnico, tipo_reclamo, solucion_propuesta) VALUES (?, ?, ?, ?, 'pendiente', '[]'::jsonb, ?, 'defecto_fabrica', ?) ");
                $stmtReclamo->execute([$dev_id, $productoId, $numero_serie_tecnico, $proveedorId, $comentario_tecnico ?: $comentario, $solucion_propuesta]);
            }
        }
    }

    $descripcionHistorial = $meta['label'] . '. ' . ($comentario !== '' ? 'Comentario: ' . $comentario : 'Acción registrada por admin.');
    $historial = emxAgregarHistorial($dev['historial_estados'] ?? '[]', $nuevoEstado, $descripcionHistorial, $meta['icon'] ?? 'fa-edit', [
        'admin_id' =>$admin_id,
        'accion' =>$accion,
        'estado_anterior' =>$estadoActual
    ]);

    $sql = "UPDATE devoluciones SET estado = ?, solucion_propuesta = COALESCE(NULLIF(?, ''), solucion_propuesta), comentario_admin = COALESCE(NULLIF(?, ''), comentario_admin), historial_estados = ?::jsonb, updated_at = NOW()";
    $params = [$nuevoEstado, $solucion_propuesta, $comentario, $historial];

    if (!empty($productoId)) {
        $sql .= ", producto_id = COALESCE(producto_id, ?)";
        $params[] = $productoId;
    }
    if (!empty($dev['detalle_pedido_id'])) {
        $sql .= ", detalle_pedido_id = COALESCE(detalle_pedido_id, ?)";
        $params[] = $dev['detalle_pedido_id'];
    }

    if ($accion === 'ofrecer_solucion') {
        $sql .= ", respuesta_usuario = 'pendiente'";
    }

    if ($codigoGuia) {
        $sql .= ", codigo_guia = ?, codigo_etiqueta = COALESCE(codigo_etiqueta, ?)";
        $params[] = $codigoGuia;
        $params[] = $codigoGuia;
    }
    if ($accion === 'recibir_almacen') {
        $sql .= ", fecha_recepcion = NOW()";
    }
    if (!empty($diagnostico_tecnico)) {
        $sql .= ", tipo_dano = ?, fecha_inspeccion_tecnica = COALESCE(fecha_inspeccion_tecnica, NOW()), tecnico_id = ?, comentario_tecnico = COALESCE(NULLIF(?, ''), comentario_tecnico)";
        $params[] = $diagnostico_tecnico;
        $params[] = $admin_id;
        $params[] = $comentario_tecnico;
    }
    if ($numero_serie_tecnico !== '') {
        $sql .= ", numero_serie_devuelto = ?";
        $params[] = $numero_serie_tecnico;
        if ($serieCoincideConVenta) {
            $sql .= ", numero_serie_validado = ?";
            $params[] = $numero_serie_tecnico;
        }
    }
    if ($accion === 'confirmar_reemplazo_entregado') {
        $sql .= ", fecha_reemplazo_entregado = NOW()";
    }
    if ($accion === 'cerrar') {
        $sql .= ", fecha_cierre = NOW()";
    }
    if ($accion === 'rechazar') {
        $sql .= ", motivo_rechazo = COALESCE(NULLIF(?, ''), motivo_rechazo)";
        $params[] = $comentario;
        if ($numero_serie_tecnico !== '' && !$serieCoincideConVenta) {
            $sql .= ", fraude_detectado = TRUE";
        }
    }
    if ($nuevoPedidoId) {
        $sql .= ", pedido_reemplazo_id = ?";
        $params[] = $nuevoPedidoId;
    }
    $sql .= " WHERE id = ?";
    $params[] = $dev_id;
    $pdo->prepare($sql)->execute($params);

    if ($accion === 'ejecutar_reembolso') {
        $pdo->prepare("UPDATE pedidos SET estado = 'Reembolsado' WHERE id = ?")->execute([$dev['pedido_id']]);
        if (function_exists('emxGenerarNotaCreditoTotal')) {
            emxGenerarNotaCreditoTotal($pdo, $dev['pedido_id'], $dev_id, $comentario ?: 'Reembolso aprobado por devolución/incidencia');
        }
        emxNotificar($pdo, $dev['usuario_id'], 'reembolso_completado', 'Reembolso completado', 'Tu reembolso fue procesado por el equipo de ElectroMax.', 'mi_cuenta.php?seccion=pedidos', 'pedido');
    } elseif ($accion === 'crear_reemplazo') {
        $pdo->prepare("UPDATE pedidos SET estado = 'Reemplazo generado' WHERE id = ?")->execute([$dev['pedido_id']]);
        if ($nuevoPedidoId) {
            $estimacion = emxRecalcularEstimacionPedidoReemplazo($pdo, $nuevoPedidoId, 'preparacion');
            $pdo->prepare("UPDATE pedidos SET estado = 'Pago confirmado' WHERE id = ?")->execute([$nuevoPedidoId]);
            $fechaTxt = !empty($estimacion['fecha_estimada']) ? date('d/m/Y', strtotime($estimacion['fecha_estimada'])) : 'por definir';
            $seriesTxt = emxResumenSeriesReemplazoDevolucion($pdo, $dev_id);
            $msgRep = 'Se creó un pedido de reemplazo para tu caso. Nueva fecha estimada: ' . $fechaTxt . '. ' . (($estimacion['es_prime'] ?? false) ? 'Tu prioridad Prime fue aplicada.' : 'Se aplicó prioridad estándar.');
            if ($seriesTxt !== '') $msgRep .= ' Nueva serie asignada: ' . $seriesTxt . '.';
            emxNotificar($pdo, $dev['usuario_id'], 'cambio_despachado', 'Reemplazo generado', $msgRep, 'tracking.php?id=' . $nuevoPedidoId, 'pedido');
        } else {
            emxNotificar($pdo, $dev['usuario_id'], 'cambio_despachado', 'Reemplazo generado', 'Se creó un pedido de reemplazo para tu caso. Ahora ElectroMax preparará y enviará el reemplazo.', 'mi_cuenta.php?seccion=pedidos', 'pedido');
        }
    } elseif ($accion === 'marcar_reemplazo_transito') {
        if (!empty($dev['pedido_reemplazo_id'])) {
            $estimacionTransito = emxRecalcularEstimacionPedidoReemplazo($pdo, $dev['pedido_reemplazo_id'], 'transito');
            $pdo->prepare("UPDATE pedidos SET estado = 'En Tránsito' WHERE id = ?")->execute([$dev['pedido_reemplazo_id']]);
            $fechaTxt = !empty($estimacionTransito['fecha_estimada']) ? date('d/m/Y', strtotime($estimacionTransito['fecha_estimada'])) : 'por definir';
            emxNotificar($pdo, $dev['usuario_id'], 'reemplazo_en_transito', 'Reemplazo en tránsito', 'Tu producto de reemplazo ya fue enviado. Fecha estimada actualizada: ' . $fechaTxt . '.', 'tracking.php?id=' . $dev['pedido_reemplazo_id'], 'pedido');
        } else {
            emxNotificar($pdo, $dev['usuario_id'], 'reemplazo_en_transito', 'Reemplazo en tránsito', 'Tu producto de reemplazo ya fue enviado.', 'mi_cuenta.php?seccion=pedidos', 'pedido');
        }
    } elseif ($accion === 'confirmar_reemplazo_entregado') {
        if (!empty($dev['pedido_reemplazo_id'])) {
            $pdo->prepare("UPDATE pedidos SET estado = 'Entregado' WHERE id = ?")->execute([$dev['pedido_reemplazo_id']]);
        }
        emxNotificar($pdo, $dev['usuario_id'], 'reemplazo_entregado', 'Reemplazo entregado', 'Tu producto de reemplazo fue marcado como entregado. El caso quedará listo para cierre.', 'mi_cuenta.php?seccion=devoluciones', 'devolucion');
    } elseif ($accion === 'rechazar') {
        emxNotificar($pdo, $dev['usuario_id'], 'devolucion_rechazada', 'Solicitud rechazada', $comentario ?: 'Tu solicitud fue rechazada por el equipo de soporte.', 'mi_cuenta.php?seccion=devoluciones', 'devolucion');
    } elseif ($accion === 'ofrecer_solucion') {
        emxNotificar($pdo, $dev['usuario_id'], 'decision_devolucion', 'Elige la solución para tu devolución', 'Tu caso fue aprobado por el equipo de ElectroMax. Ingresa a Mis devoluciones y elige una de las soluciones que te habilitó el admin: reembolso, cambio o ambas.', 'mi_cuenta.php?seccion=devoluciones', 'devolucion');
    } elseif ($accion === 'autorizar_retorno') {
        emxNotificar($pdo, $dev['usuario_id'], 'devolucion_autorizada', 'Retorno autorizado', 'Tu retorno fue autorizado. El equipo te indicará la guía o método de devolución.', 'mi_cuenta.php?seccion=devoluciones', 'devolucion');
    } elseif ($accion === 'solicitar_evidencia') {
        emxNotificar($pdo, $dev['usuario_id'], 'evidencia_requerida', 'Necesitamos más evidencia', $comentario ?: 'Por favor envía más información para revisar tu caso.', 'mi_cuenta.php?seccion=devoluciones', 'devolucion');
    } elseif (in_array($accion, ['investigar_courier', 'reclamo_courier'], true)) {
        emxNotificar($pdo, $dev['usuario_id'], 'investigacion_courier', 'Caso en investigación con courier', 'Estamos revisando la entrega con el transportista antes de tomar una decisión final.', 'mi_cuenta.php?seccion=devoluciones', 'devolucion');
    }

    return ['estado' =>$nuevoEstado, 'pedido_reemplazo_id' =>$nuevoPedidoId];
}
}
?>