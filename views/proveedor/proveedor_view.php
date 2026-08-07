<?php
/**
 * Vista separada de `proveedor.php`.
 *
 * Fase 5:
 * Este archivo contiene principalmente HTML y PHP de presentación.
 * La lógica previa a cargar esta vista se mantiene en `proveedor.php`.
 *
 * Las variables usadas aquí vienen del controlador raíz por compatibilidad.
 */
?>
<!DOCTYPE html><html lang="es"><head>
<!-- Favicon ElectroMax global -->
<link rel="icon" href="assets/favicon/favicon.ico" sizes="any">
<link rel="icon" type="image/svg+xml" href="assets/favicon/favicon.svg">
<link rel="icon" type="image/png" sizes="32x32" href="assets/favicon/favicon-32x32.png">
<link rel="icon" type="image/png" sizes="16x16" href="assets/favicon/favicon-16x16.png">
<link rel="apple-touch-icon" sizes="180x180" href="assets/favicon/apple-touch-icon.png">
<link rel="manifest" href="assets/favicon/site.webmanifest">
<meta name="theme-color" content="#0b4da2">
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>Portal Proveedor - ElectroMax</title><script src="https://cdn.tailwindcss.com"></script><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"><link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet"><style>* { font-family: 'Inter', sans-serif; }
        body { background: #f1f5f9; }
        .sidebar { background: linear-gradient(180deg, #064e3b 0%, #065f46 100%); }
        .nav-link { transition: all 0.2s; }
        .nav-link:hover { transform: translateX(4px); }
        .nav-link.active { background: linear-gradient(90deg, #10b981 0%, #059669 100%); }
        .form-input { transition: all 0.2s; border: 1px solid #cbd5e1; }
        .form-input:focus { border-color: #10b981; box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1); outline: none; }
        .btn-primary { background: linear-gradient(135deg, #10b981 0%, #059669 100%); transition: all 0.2s; }
        .btn-primary:hover { transform: translateY(-1px); box-shadow: 0 6px 16px rgba(16, 185, 129, 0.4); }
        .modal-backdrop { backdrop-filter: blur(8px); background: rgba(15, 23, 42, 0.6); }
        .modal-content { animation: modalSlideIn 0.3s; }
        @keyframes modalSlideIn { from { opacity: 0; transform: scale(0.95); } to { opacity: 1; transform: scale(1); } }
        .badge { display: inline-flex; align-items: center; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 600; }
        .oferta-card { transition: all 0.2s; }
        .oferta-card:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style></head><body class="flex h-screen"><!-- SIDEBAR --><aside class="sidebar w-64 text-white flex flex-col shadow-2xl flex-shrink-0"><div class="p-6 border-b border-emerald-800"><div class="flex items-center gap-3 mb-4"><div class="w-10 h-10 bg-emerald-500 rounded-lg flex items-center justify-center"><i class="fas fa-truck text-xl"></i></div><div><h1 class="text-lg font-bold">Portal Proveedor</h1><p class="text-xs text-emerald-200">ElectroMax</p></div></div><div class="flex items-center gap-2"><div class="w-10 h-10 bg-emerald-700 rounded-full flex items-center justify-center font-bold text-sm overflow-hidden"><?php if (!empty($user['foto_perfil_url'])): ?><img src="<?= htmlspecialchars($user['foto_perfil_url']) ?>" class="w-full h-full object-cover"><?php else: ?><?= strtoupper(substr($user['nombres'], 0, 1)) ?><?php endif; ?></div><div class="flex-1 min-w-0"><p class="text-sm font-medium truncate"><?= htmlspecialchars($user['nombres'] . ' ' . $user['apellidos']) ?></p><p class="text-xs text-emerald-200 truncate"><?= htmlspecialchars($user['email']) ?></p></div></div></div><nav class="flex-1 p-4 space-y-1"><a href="?seccion=dashboard" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg <?= $seccion==='dashboard'?'active':'' ?>text-emerald-100"><i class="fas fa-tachometer-alt w-5"></i>Dashboard
            </a><a href="?seccion=perfil" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg <?= $seccion==='perfil'?'active':'' ?>text-emerald-100"><i class="fas fa-user-circle w-5"></i>Mi Perfil
            </a><a href="?seccion=capacidad" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg <?= $seccion==='capacidad'?'active':'' ?>text-emerald-100"><i class="fas fa-industry w-5"></i>Mi Capacidad de Producción
                <?php if ($stats['capacidades_registradas'] == 0): ?><span class="ml-auto bg-red-500 text-white text-xs font-bold px-2 py-0.5 rounded-full">Nuevo</span><?php endif; ?></a><a href="?seccion=solicitudes" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg <?= $seccion==='solicitudes'?'active':'' ?>text-emerald-100"><i class="fas fa-clipboard-list w-5"></i>Solicitudes de Reabastecimiento
                <?php if ($stats['solicitudes_pendientes'] >0): ?><span class="ml-auto bg-amber-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $stats['solicitudes_pendientes'] ?></span><?php endif; ?></a><a href="?seccion=propuestas" class="nav-link flex items-center gap-3 px-4 py-3 rounded-lg <?= $seccion==='propuestas'?'active':'' ?>text-emerald-100"><i class="fas fa-file-invoice-dollar w-5"></i>Mis Propuestas
                <?php if ($stats['propuestas_aprobadas'] >0): ?><span class="ml-auto bg-emerald-500 text-white text-xs font-bold px-2 py-0.5 rounded-full"><?= $stats['propuestas_aprobadas'] ?></span><?php endif; ?></a></nav><div class="p-4 border-t border-emerald-800"><a href="logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-red-300 hover:bg-red-900/20 transition"><i class="fas fa-sign-out-alt w-5"></i>Cerrar Sesión
            </a></div></aside><!-- MAIN --><main class="flex-1 overflow-y-auto"><header class="bg-white border-b border-slate-200 px-8 py-5"><h2 class="text-2xl font-bold text-slate-800"><?php
                $titulos = [
                    'dashboard' =>'Dashboard',
                    'perfil' =>'Mi Perfil',
                    'capacidad' =>'Mi Capacidad de Producción',
                    'solicitudes' =>'Solicitudes de Reabastecimiento',
                    'propuestas' =>'Mis Propuestas Enviadas'
                ];
                echo $titulos[$seccion] ?? 'Portal Proveedor';
                ?></h2></header><div class="p-8"><?php if ($msg): ?><div class="mb-6 p-4 rounded-lg <?= $msg_type === 'error' ? 'bg-red-50 border border-red-200 text-red-700' : 'bg-emerald-50 border border-emerald-200 text-emerald-700' ?>"><i class="fas fa-<?= $msg_type === 'error' ? 'exclamation-circle' : 'check-circle' ?>mr-2"></i><?= htmlspecialchars($msg) ?></div><?php endif; ?><!-- DASHBOARD --><?php if ($seccion === 'dashboard'): ?><div class="grid grid-cols-1 md:grid-cols-5 gap-6 mb-8"><div class="bg-gradient-to-br from-blue-500 to-blue-600 p-6 rounded-xl shadow-lg text-white"><p class="text-blue-100 text-sm font-medium mb-2"><i class="fas fa-box mr-1"></i>Productos Asignados</p><p class="text-3xl font-bold"><?= $stats['productos_asignados'] ?></p></div><div class="bg-gradient-to-br from-emerald-500 to-emerald-600 p-6 rounded-xl shadow-lg text-white"><p class="text-emerald-100 text-sm font-medium mb-2"><i class="fas fa-industry mr-1"></i>Capacidades Registradas</p><p class="text-3xl font-bold"><?= $stats['capacidades_registradas'] ?></p></div><div class="bg-gradient-to-br from-amber-500 to-amber-600 p-6 rounded-xl shadow-lg text-white"><p class="text-amber-100 text-sm font-medium mb-2"><i class="fas fa-clock mr-1"></i>Solicitudes Pendientes</p><p class="text-3xl font-bold"><?= $stats['solicitudes_pendientes'] ?></p></div><div class="bg-gradient-to-br from-purple-500 to-purple-600 p-6 rounded-xl shadow-lg text-white"><p class="text-purple-100 text-sm font-medium mb-2"><i class="fas fa-paper-plane mr-1"></i>Propuestas Enviadas</p><p class="text-3xl font-bold"><?= $stats['propuestas_enviadas'] ?></p></div><div class="bg-gradient-to-br from-green-500 to-green-600 p-6 rounded-xl shadow-lg text-white"><p class="text-green-100 text-sm font-medium mb-2"><i class="fas fa-check-circle mr-1"></i>Propuestas Aprobadas</p><p class="text-3xl font-bold"><?= $stats['propuestas_aprobadas'] ?></p></div></div><?php if ($stats['capacidades_registradas'] == 0): ?><div class="bg-amber-50 border-2 border-amber-300 rounded-xl p-6 mb-6"><div class="flex items-start gap-4"><i class="fas fa-exclamation-triangle text-amber-600 text-2xl"></i><div class="flex-1"><h3 class="text-lg font-bold text-amber-900 mb-2">¡Importante! Configura tu capacidad de producción</h3><p class="text-amber-800 mb-4">Para que el sistema pueda calcular estimaciones realistas de entrega y recibir solicitudes de reabastecimiento, debes declarar tu capacidad de producción para cada producto asignado.</p><a href="?seccion=capacidad" class="inline-flex items-center gap-2 px-6 py-3 bg-amber-600 text-white rounded-lg font-bold hover:bg-amber-700 transition"><i class="fas fa-industry"></i>Configurar Capacidad Ahora
                                </a></div></div></div><?php endif; ?><div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6"><h3 class="text-lg font-bold text-slate-900 mb-4"><i class="fas fa-clipboard-list text-emerald-500 mr-2"></i>Solicitudes Recientes</h3><?php if (empty($solicitudes)): ?><div class="text-center py-12 text-slate-500"><i class="fas fa-inbox text-4xl mb-3 text-slate-300"></i><p>No tienes solicitudes pendientes en este momento.</p></div><?php else: ?><div class="space-y-3"><?php foreach (array_slice($solicitudes, 0, 5) as $sol): ?><div class="flex justify-between items-center p-4 bg-slate-50 rounded-lg hover:bg-slate-100 transition"><div><p class="font-semibold text-slate-900"><?= htmlspecialchars($sol['producto_nombre']) ?></p><p class="text-sm text-slate-600">Necesitan: <strong><?= $sol['cantidad_necesaria'] ?>unidades</strong>| Límite: <?= date('d/m/Y', strtotime($sol['fecha_limite'])) ?></p></div><?php if ($sol['mi_propuesta'] >0): ?><span class="badge bg-blue-100 text-blue-700"><i class="fas fa-check mr-1"></i>Ya propuse</span><?php else: ?><a href="?seccion=solicitudes" class="px-4 py-2 bg-emerald-600 text-white rounded-lg text-sm font-medium hover:bg-emerald-700 transition"><i class="fas fa-plus mr-1"></i>Responder
                                        </a><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?></div><?php endif; ?><!-- PERFIL --><?php if ($seccion === 'perfil'): ?><div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6 sm:p-8"><h3 class="text-xl font-bold text-slate-900 mb-6 flex items-center gap-3"><span class="w-8 h-8 bg-emerald-600 text-white rounded-lg flex items-center justify-center text-sm"><i class="fas fa-user"></i></span>Información Personal
                    </h3><div class="flex flex-col sm:flex-row items-center gap-6 mb-8 pb-8 border-b border-slate-100"><div class="relative"><div class="w-24 h-24 rounded-full overflow-hidden bg-slate-100 border-4 border-white shadow-md ring-1 ring-slate-200"><?php if (!empty($user['foto_perfil_url'])): ?><img src="<?= htmlspecialchars($user['foto_perfil_url']) ?>" class="w-full h-full object-cover"><?php else: ?><div class="w-full h-full flex items-center justify-center bg-emerald-600 text-white text-3xl font-extrabold"><?= strtoupper(substr($user['nombres'], 0, 1)) ?></div><?php endif; ?></div></div><div class="flex flex-col gap-2 w-full"><form method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2"><?= emxCsrfCampo() ?><input type="file" name="foto_perfil" accept="image/*" class="form-input text-xs rounded-xl file:mr-2 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 w-full"><button type="submit" name="upload_foto_perfil" class="px-4 py-2 bg-emerald-600 text-white rounded-xl text-xs font-semibold hover:bg-emerald-700 whitespace-nowrap"><i class="fas fa-upload mr-1"></i>Subir Foto
                                </button></form><?php if (!empty($user['foto_perfil_url'])): ?><p class="text-xs text-slate-500">Foto actual: <?= basename($user['foto_perfil_url']) ?></p><?php endif; ?></div></div><form method="POST" class="space-y-5"><?= emxCsrfCampo() ?><input type="hidden" name="actualizar_perfil" value="1"><div class="grid grid-cols-1 md:grid-cols-2 gap-5"><div><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Nombres *</label><input type="text" name="nombres" value="<?= htmlspecialchars($user['nombres']) ?>" class="form-input rounded-xl px-4 py-2.5 text-sm" required></div><div><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Apellidos *</label><input type="text" name="apellidos" value="<?= htmlspecialchars($user['apellidos']) ?>" class="form-input rounded-xl px-4 py-2.5 text-sm" required></div><div><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Email *</label><input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" class="form-input rounded-xl px-4 py-2.5 text-sm" required></div><div><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Teléfono</label><input type="text" name="telefono" value="<?= htmlspecialchars($user['telefono'] ?? '') ?>" class="form-input rounded-xl px-4 py-2.5 text-sm"></div><div class="md:col-span-2"><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Cédula / RUC</label><input type="text" name="cedula_ruc" value="<?= htmlspecialchars($user['cedula_ruc'] ?? '') ?>" class="form-input rounded-xl px-4 py-2.5 text-sm"></div><div class="md:col-span-2"><label class="text-xs font-bold text-slate-500 uppercase tracking-widest mb-2 block">Nueva Contraseña <span class="text-slate-400 font-normal">(dejar vacío para mantener la actual)</span></label><input type="password" name="password" class="form-input rounded-xl px-4 py-2.5 text-sm" placeholder="••••••••"><p class="text-[11px] text-slate-400 mt-1">Mínimo 6 caracteres</p></div></div><div class="flex justify-end pt-4 border-t"><button type="submit" class="btn-primary px-6 py-2.5 text-white rounded-xl text-sm font-semibold"><i class="fas fa-save mr-1"></i>Guardar Cambios
                            </button></div></form></div><?php endif; ?><!-- CAPACIDAD DE PRODUCCIÓN --><?php if ($seccion === 'capacidad'): ?><div class="mb-6 flex flex-col md:flex-row md:items-center md:justify-between gap-4"><div><h3 class="text-lg font-black text-slate-900 flex items-center gap-2"><i class="fas fa-industry text-emerald-600"></i>Capacidad de producción</h3><p class="text-sm text-slate-500 mt-1">Puedes registrar una nueva capacidad o editar una capacidad ya registrada.</p></div><button onclick="abrirModalCapacidad()" class="btn-primary text-white px-6 py-3 rounded-lg font-bold flex items-center justify-center gap-2"><i class="fas fa-plus"></i>Registrar Nueva Capacidad
                    </button></div><?php if (empty($capacidades)): ?><div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center"><i class="fas fa-industry text-5xl text-slate-300 mb-4"></i><h3 class="text-lg font-bold text-slate-800 mb-2">No has registrado capacidades de producción</h3><p class="text-slate-500 mb-6">Declara tu capacidad para cada producto asignado para recibir solicitudes de reabastecimiento.</p><button onclick="abrirModalCapacidad()" class="btn-primary text-white px-6 py-3 rounded-lg font-bold"><i class="fas fa-plus mr-2"></i>Registrar Primera Capacidad
                        </button></div><?php else: ?><div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden"><table class="w-full"><thead class="bg-slate-50"><tr><th class="px-6 py-4 text-left text-xs font-semibold">Producto</th><th class="px-6 py-4 text-left text-xs font-semibold">Capacidad Diaria</th><th class="px-6 py-4 text-left text-xs font-semibold">Tiempo Entrega</th><th class="px-6 py-4 text-left text-xs font-semibold">Disponibles</th><th class="px-6 py-4 text-left text-xs font-semibold">Defectos</th><th class="px-6 py-4 text-right text-xs font-semibold">Acciones</th></tr></thead><tbody class="divide-y divide-slate-100"><?php foreach ($capacidades as $cap): ?><tr class="hover:bg-slate-50"><td class="px-6 py-4"><p class="font-medium text-slate-900"><?= htmlspecialchars($cap['producto_nombre']) ?></p><p class="text-xs text-slate-500 font-mono"><?= htmlspecialchars($cap['producto_sku'] ?? 'S/SKU') ?></p></td><td class="px-6 py-4"><p class="font-bold text-slate-900"><?= $cap['capacidad_diaria'] ?>unid/día</p><p class="text-xs text-slate-500"><?= $cap['capacidad_semanal'] ?>unid/semana</p></td><td class="px-6 py-4"><p class="font-medium text-slate-700"><?= $cap['tiempo_entrega_estandar'] ?>días</p><p class="text-xs text-slate-500"><?= $cap['distancia_km'] ?>km</p></td><td class="px-6 py-4"><span class="badge <?= $cap['unidades_disponibles'] >0 ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' ?>"><?= $cap['unidades_disponibles'] ?>unid
                                            </span></td><td class="px-6 py-4"><p class="text-sm text-slate-700"><?= number_format($cap['tasa_defectos_fabrica'] * 100, 1) ?>%</p></td><td class="px-6 py-4 text-right"><button type="button" data-capacidad="<?= htmlspecialchars(json_encode($cap, JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>" onclick="editarCapacidadDesdeBoton(this)" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-blue-50 text-blue-700 hover:bg-blue-100 font-bold text-xs transition" title="Editar capacidad"><i class="fas fa-edit"></i>Editar</button></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?><?php endif; ?><!-- SOLICITUDES --><?php if ($seccion === 'solicitudes'): ?><?php if (empty($solicitudes)): ?><div class="bg-white rounded-xl shadow-sm border border-slate-200 p-12 text-center"><i class="fas fa-clipboard-check text-5xl text-slate-300 mb-4"></i><h3 class="text-lg font-bold text-slate-800">No hay solicitudes disponibles</h3><p class="text-slate-500">Cuando ElectroMax necesite reabastecer productos que tú suministras, aparecerán aquí.</p></div><?php else: ?><div class="space-y-4"><?php foreach ($solicitudes as $sol): ?><div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6"><div class="flex justify-between items-start mb-4"><div><h4 class="text-lg font-bold text-slate-900"><?= htmlspecialchars($sol['producto_nombre']) ?></h4><p class="text-sm text-slate-600">SKU: <?= htmlspecialchars($sol['producto_sku'] ?? 'N/A') ?></p></div><span class="badge <?= $sol['mi_propuesta'] >0 ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' ?>"><?= $sol['mi_propuesta'] >0 ? 'Ya propuse' : 'Pendiente' ?></span></div><div class="grid grid-cols-3 gap-4 mb-4 p-3 bg-slate-50 rounded-lg"><div><p class="text-xs text-slate-500">Cantidad necesaria</p><p class="font-bold text-slate-900"><?= $sol['cantidad_necesaria'] ?>unidades</p></div><div><p class="text-xs text-slate-500">Fecha límite</p><p class="font-bold text-slate-900"><?= date('d/m/Y', strtotime($sol['fecha_limite'])) ?></p></div><div><p class="text-xs text-slate-500">Total propuestas</p><p class="font-bold text-slate-900"><?= $sol['total_propuestas'] ?>proveedores</p></div></div><?php if ($sol['mi_propuesta'] == 0): ?><button onclick='abrirModalPropuesta(<?= json_encode($sol) ?>)' class="btn-primary text-white px-6 py-2 rounded-lg font-medium"><i class="fas fa-paper-plane mr-2"></i>Enviar Propuesta
                                    </button><?php else: ?><div class="p-3 bg-blue-50 border border-blue-200 rounded-lg"><p class="text-sm text-blue-800"><i class="fas fa-check-circle mr-1"></i>Ya enviaste tu propuesta para esta solicitud. Esperando respuesta del admin.</p></div><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?><?php endif; ?><!-- PROPUESTAS --><?php if ($seccion === 'propuestas'): ?><div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden"><?php if (empty($propuestas_enviadas)): ?><div class="p-12 text-center"><i class="fas fa-file-invoice text-5xl text-slate-300 mb-4"></i><h3 class="text-lg font-bold text-slate-800">Aún no has enviado propuestas</h3><p class="text-slate-500">Responde a las solicitudes de reabastecimiento para que aparezcan aquí.</p></div><?php else: ?><table class="w-full"><thead class="bg-slate-50"><tr><th class="px-6 py-4 text-left text-xs font-semibold">Producto</th><th class="px-6 py-4 text-left text-xs font-semibold">Ofertas</th><th class="px-6 py-4 text-left text-xs font-semibold">Estado</th><th class="px-6 py-4 text-left text-xs font-semibold">Fecha</th><th class="px-6 py-4 text-right text-xs font-semibold">Acciones</th></tr></thead><tbody class="divide-y divide-slate-100"><?php foreach ($propuestas_enviadas as $prop): 
                                    $calendario = json_decode($prop['calendario_entregas'], true) ?: [];
                                    $es_parcial = count($calendario) >1;
                                ?><tr class="hover:bg-slate-50"><td class="px-6 py-4"><p class="font-medium text-slate-900"><?= htmlspecialchars($prop['producto_nombre']) ?></p><p class="text-xs text-slate-500 font-mono"><?= htmlspecialchars($prop['producto_sku'] ?? 'S/SKU') ?></p><p class="text-xs text-slate-500">Solicitado: <?= $prop['cantidad_necesaria'] ?>unid.</p></td><td class="px-6 py-4"><div class="mb-1"><span class="badge <?= $es_parcial ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' ?>text-[10px]"><i class="fas fa-<?= $es_parcial ? 'truck' : 'box' ?>mr-1"></i><?= $prop['cantidad_ofrecida'] ?>unid / <?= $prop['dias_entrega'] ?>días
                                                </span></div><p class="text-xs text-slate-600">$<?= number_format($prop['precio_unitario'], 2) ?>c/u</p><p class="text-xs font-bold text-slate-900">Total: $<?= number_format($prop['precio_total'], 2) ?></p><?php if ($es_parcial): ?><p class="text-[10px] text-blue-600 mt-1"><?= count($calendario) ?>lotes programados</p><?php endif; ?></td><td class="px-6 py-4"><span class="badge <?= $prop['estado'] === 'aprobada' ? 'bg-emerald-100 text-emerald-700' : ($prop['estado'] === 'rechazada' ? 'bg-red-100 text-red-700' : 'bg-amber-100 text-amber-700') ?>"><?= ucfirst($prop['estado']) ?></span></td><td class="px-6 py-4 text-sm text-slate-600"><?= date('d/m/Y', strtotime($prop['created_at'])) ?></td><td class="px-6 py-4 text-right"><button onclick='verDetallePropuesta(<?= json_encode($prop) ?>)' class="text-blue-600 hover:bg-blue-50 p-2 rounded inline-flex" title="Ver detalle"><i class="fas fa-eye"></i></button></td></tr><?php endforeach; ?></tbody></table><?php endif; ?></div><?php endif; ?></div></main><!-- MODAL CAPACIDAD DE PRODUCCIÓN (CREAR/EDITAR) --><div id="modalCapacidad" class="hidden fixed inset-0 z-50 modal-backdrop flex items-center justify-center p-4"><div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto"><div class="p-6 border-b flex justify-between items-center sticky top-0 bg-white z-10"><h3 class="text-xl font-bold"><i class="fas fa-industry text-emerald-500 mr-2"></i><span id="modal_capacidad_titulo">Registrar Nueva Capacidad de Producción</span></h3><button onclick="cerrarModalCapacidad()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button></div><form method="POST" action="?seccion=capacidad" class="p-6 space-y-6"><?= emxCsrfCampo() ?><input type="hidden" name="guardar_capacidad" value="1"><input type="hidden" name="capacidad_id" id="capacidad_id"><div><label class="block text-xs font-medium mb-1">Producto *</label><select name="producto_id" id="cap_producto_id" required class="form-input w-full rounded-lg px-3 py-2 text-sm"><option value="">Seleccionar producto...</option><?php foreach ($productos_asignados as $prod): ?><option value="<?= $prod['id'] ?>"><?= htmlspecialchars($prod['nombre']) ?>(<?= htmlspecialchars($prod['sku'] ?? 'S/SKU') ?>)</option><?php endforeach; ?></select></div><div class="border-t pt-6"><h4 class="text-sm font-bold text-slate-700 mb-4"><i class="fas fa-industry mr-1"></i>Capacidad de Producción</h4><div class="grid grid-cols-3 gap-4"><div><label class="block text-xs font-medium mb-1">Capacidad Diaria (unid/día) *</label><input type="number" name="capacidad_diaria" id="cap_capacidad_diaria" required min="1" class="form-input w-full rounded-lg px-3 py-2 text-sm" placeholder="Ej: 20"></div><div><label class="block text-xs font-medium mb-1">Capacidad Semanal (unid/semana)</label><input type="number" name="capacidad_semanal" id="cap_capacidad_semanal" class="form-input w-full rounded-lg px-3 py-2 text-sm" placeholder="Ej: 100"></div><div><label class="block text-xs font-medium mb-1">Capacidad Máxima por Pedido *</label><input type="number" name="capacidad_maxima_pedido" id="cap_capacidad_maxima" required min="1" class="form-input w-full rounded-lg px-3 py-2 text-sm" placeholder="Ej: 500"></div></div></div><div class="border-t pt-6"><h4 class="text-sm font-bold text-slate-700 mb-4"><i class="fas fa-truck mr-1"></i>Tiempo y Logística</h4><div class="grid grid-cols-4 gap-4"><div><label class="block text-xs font-medium mb-1">Tiempo Entrega Estándar (días) *</label><input type="number" name="tiempo_entrega_estandar" id="cap_tiempo_entrega" required min="1" class="form-input w-full rounded-lg px-3 py-2 text-sm" placeholder="Ej: 5"></div><div><label class="block text-xs font-medium mb-1">Distancia a Tienda (km)</label><input type="number" step="0.1" name="distancia_km" id="cap_distancia" class="form-input w-full rounded-lg px-3 py-2 text-sm" placeholder="Ej: 300"></div><div><label class="block text-xs font-medium mb-1">Velocidad Promedio (km/h)</label><input type="number" step="0.1" name="velocidad_promedio_kmh" id="cap_velocidad" class="form-input w-full rounded-lg px-3 py-2 text-sm" placeholder="Ej: 60"></div><div><label class="block text-xs font-medium mb-1">Tiempo Aduanas (días)</label><input type="number" name="tiempo_aduanas_dias" id="cap_aduanas" class="form-input w-full rounded-lg px-3 py-2 text-sm" placeholder="0 si es nacional"></div></div></div><div class="border-t pt-6"><h4 class="text-sm font-bold text-slate-700 mb-4"><i class="fas fa-check-circle mr-1"></i>Calidad</h4><div class="grid grid-cols-3 gap-4"><div><label class="block text-xs font-medium mb-1">Tasa de Defectos de Fábrica (%) *</label><input type="number" step="0.01" name="tasa_defectos_fabrica" id="cap_defectos" required min="0" max="100" class="form-input w-full rounded-lg px-3 py-2 text-sm" placeholder="Ej: 5"><p class="text-[10px] text-slate-400 mt-1">Porcentaje de productos defectuosos</p></div><div><label class="block text-xs font-medium mb-1">Unidades Disponibles Ahora</label><input type="number" name="unidades_disponibles" id="cap_disponibles" class="form-input w-full rounded-lg px-3 py-2 text-sm" placeholder="Ej: 150"></div><div><label class="block text-xs font-medium mb-1">Próxima Producción</label><input type="date" name="proxima_produccion" id="cap_proxima" class="form-input w-full rounded-lg px-3 py-2 text-sm"></div></div></div><div class="border-t pt-6"><div class="flex items-center justify-between mb-4"><h4 class="text-sm font-bold text-slate-700"><i class="fas fa-percentage mr-1"></i>Descuentos por Volumen (Opcional)</h4><button type="button" onclick="agregarRangoDescuento()" class="text-xs bg-emerald-600 text-white px-3 py-1.5 rounded-lg hover:bg-emerald-700 transition"><i class="fas fa-plus mr-1"></i>Agregar Rango
                        </button></div><p class="text-xs text-slate-500 mb-3">Define rangos de cantidad con descuentos. Ej: 10-50 unid = 5%, 51-100 unid = 10%</p><div id="contenedor_descuentos" class="space-y-3"><div class="text-center py-4 text-slate-400 text-xs italic" id="msg_descuentos_vacio"><i class="fas fa-info-circle mr-1"></i>No hay rangos configurados. Haz clic en "Agregar Rango" para empezar.
                        </div></div></div><div class="flex justify-end gap-3 pt-6 border-t"><button type="button" onclick="cerrarModalCapacidad()" class="px-4 py-2 bg-slate-100 rounded-lg hover:bg-slate-200">Cancelar</button><button type="submit" class="btn-primary px-6 py-2 text-white rounded-lg font-medium"><i class="fas fa-save mr-2"></i><span id="btn_capacidad_texto">Guardar Capacidad</span></button></div></form></div></div><!-- ⭐ MODAL ENVIAR PROPUESTA --><div id="modalPropuesta" class="hidden fixed inset-0 z-50 modal-backdrop flex items-center justify-center p-4"><div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto"><div class="p-6 border-b flex justify-between items-center sticky top-0 bg-white z-10"><h3 class="text-xl font-bold"><i class="fas fa-paper-plane text-emerald-500 mr-2"></i>Enviar Propuesta de Reabastecimiento</h3><button onclick="cerrarModalPropuesta()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-xl"></i></button></div><form method="POST" action="?seccion=solicitudes" class="p-6 space-y-6"><?= emxCsrfCampo() ?><input type="hidden" name="enviar_propuesta" value="1"><input type="hidden" name="solicitud_id" id="prop_solicitud_id"><div class="bg-blue-50 border border-blue-200 rounded-lg p-4"><p class="text-sm text-blue-900"><strong>Producto:</strong><span id="prop_producto_nombre"></span></p><p class="text-sm text-blue-900 mt-1"><strong>Cantidad solicitada por el admin:</strong><span id="prop_cantidad_solicitada"></span>unidades</p></div><div class="border-t pt-6"><h4 class="text-sm font-bold text-slate-700 mb-4"><i class="fas fa-lightbulb text-amber-500 mr-1"></i>Ofertas (puedes enviar ambas para que el admin elija)</h4><p class="text-xs text-slate-500 mb-4">Puedes ofrecer entrega total, entrega parcial, o ambas. El admin elegirá la que más le convenga.</p><!-- OFERTA COMPLETA --><div class="oferta-card bg-emerald-50 border-2 border-emerald-200 rounded-xl p-5 mb-4"><div class="flex items-center gap-2 mb-4"><input type="checkbox" id="activar_completa" class="w-4 h-4 text-emerald-600" onchange="toggleOfertaCompleta()"><label for="activar_completa" class="font-bold text-emerald-800"><i class="fas fa-box mr-1"></i>OPCIÓN A: Entrega Total
                            </label><span class="text-xs text-emerald-600">(Todas las unidades de una vez)</span></div><div id="campos_completa" class="grid grid-cols-3 gap-4 opacity-50 pointer-events-none"><div><label class="block text-xs font-medium mb-1">Cantidad total *</label><input type="number" name="oferta_completa[cantidad]" id="comp_cantidad" class="form-input w-full rounded-lg px-3 py-2 text-sm" placeholder="Ej: 600"></div><div><label class="block text-xs font-medium mb-1">Días de entrega *</label><input type="number" name="oferta_completa[dias]" id="comp_dias" class="form-input w-full rounded-lg px-3 py-2 text-sm" placeholder="Ej: 10"></div><div><label class="block text-xs font-medium mb-1">Precio por unidad (USD) *</label><input type="number" step="0.01" name="oferta_completa[precio]" id="comp_precio" class="form-input w-full rounded-lg px-3 py-2 text-sm" placeholder="Ej: 440.00"></div></div></div><!-- OFERTA PARCIAL --><div class="oferta-card bg-blue-50 border-2 border-blue-200 rounded-xl p-5"><div class="flex items-center gap-2 mb-4"><input type="checkbox" id="activar_parcial" class="w-4 h-4 text-blue-600" onchange="toggleOfertaParcial()"><label for="activar_parcial" class="font-bold text-blue-800"><i class="fas fa-truck mr-1"></i>OPCIÓN B: Entrega Parcial
                            </label><span class="text-xs text-blue-600">(Varios lotes en diferentes fechas)</span></div><div id="campos_parcial" class="opacity-50 pointer-events-none"><div class="mb-4"><label class="block text-xs font-medium mb-1">Precio por unidad (USD) *</label><input type="number" step="0.01" name="oferta_parcial[precio]" id="parc_precio" class="form-input w-full rounded-lg px-3 py-2 text-sm" placeholder="Ej: 445.00"><p class="text-[10px] text-slate-400 mt-1">El precio puede ser diferente al de la entrega total</p></div><div class="flex items-center justify-between mb-2"><h5 class="text-xs font-bold text-blue-700">Calendario de Entregas</h5><button type="button" onclick="agregarLote()" class="text-xs bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition"><i class="fas fa-plus mr-1"></i>Agregar Lote
                                </button></div><p class="text-xs text-slate-500 mb-3">Define las fechas y cantidades de cada lote de entrega</p><div id="contenedor_lotes" class="space-y-3"><div class="text-center py-4 text-slate-400 text-xs italic" id="msg_lotes_vacio"><i class="fas fa-info-circle mr-1"></i>No hay lotes configurados. Haz clic en "Agregar Lote" para empezar.
                                </div></div></div></div></div><div class="border-t pt-6"><label class="block text-xs font-medium mb-1">Notas adicionales (opcional)</label><textarea name="notas" rows="3" class="form-input w-full rounded-lg px-3 py-2 text-sm" placeholder="Ej: Podemos entregar antes si se confirma hoy..."></textarea></div><div class="flex justify-end gap-3 pt-6 border-t"><button type="button" onclick="cerrarModalPropuesta()" class="px-4 py-2 bg-slate-100 rounded-lg hover:bg-slate-200">Cancelar</button><button type="submit" class="btn-primary px-6 py-2 text-white rounded-lg font-medium"><i class="fas fa-paper-plane mr-2"></i>Enviar Propuesta
                    </button></div></form></div></div><!-- MODAL VER DETALLE DE PROPUESTA --><div id="modalDetallePropuesta" class="hidden fixed inset-0 z-50 modal-backdrop flex items-center justify-center p-4"><div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] overflow-y-auto"><div class="p-6 border-b flex justify-between items-center sticky top-0 bg-gradient-to-r from-emerald-500 to-teal-600 text-white z-10"><h3 class="text-xl font-bold"><i class="fas fa-file-invoice mr-2"></i>Comprobante de Abastecimiento</h3><button onclick="cerrarModalDetalle()" class="text-white/80 hover:text-white"><i class="fas fa-times text-xl"></i></button></div><div class="p-6 space-y-6" id="detalle_propuesta_content"><!-- Contenido dinámico --></div><div class="px-6 py-4 border-t border-slate-200 bg-slate-50 flex justify-end"><button onclick="cerrarModalDetalle()" class="px-6 py-2 bg-slate-600 text-white rounded-lg font-medium hover:bg-slate-700 transition"><i class="fas fa-times mr-2"></i>Cerrar
                </button></div></div></div><script>// ============================================
        // FUNCIONES DEL MODAL DE CAPACIDAD
        // ============================================
        function abrirModalCapacidad() {
            document.getElementById('capacidad_id').value = '';
            document.getElementById('modal_capacidad_titulo').textContent = 'Registrar Capacidad de Producción';
            document.getElementById('btn_capacidad_texto').textContent = 'Guardar Capacidad';
            
            document.getElementById('cap_producto_id').value = '';
            document.getElementById('cap_capacidad_diaria').value = '';
            document.getElementById('cap_capacidad_semanal').value = '';
            document.getElementById('cap_capacidad_maxima').value = '';
            document.getElementById('cap_tiempo_entrega').value = '';
            document.getElementById('cap_distancia').value = '';
            document.getElementById('cap_velocidad').value = '';
            document.getElementById('cap_aduanas').value = '';
            document.getElementById('cap_defectos').value = '';
            document.getElementById('cap_disponibles').value = '';
            document.getElementById('cap_proxima').value = '';
            
            document.getElementById('contenedor_descuentos').innerHTML = '<div class="text-center py-4 text-slate-400 text-xs italic" id="msg_descuentos_vacio"><i class="fas fa-info-circle mr-1"></i>No hay rangos configurados. Haz clic en "Agregar Rango" para empezar.</div>';
            
            document.getElementById('modalCapacidad').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalCapacidad() {
            document.getElementById('modalCapacidad').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function editarCapacidadDesdeBoton(btn) {
            try {
                const capacidad = JSON.parse(btn.getAttribute('data-capacidad') || '{}');
                editarCapacidad(capacidad);
            } catch (e) {
                alert('No se pudo cargar la capacidad para editar. Recarga la página e intenta otra vez.');
            }
        }

        function editarCapacidad(capacidad) {
            document.getElementById('capacidad_id').value = capacidad.id;
            document.getElementById('modal_capacidad_titulo').textContent = 'Editar Capacidad de Producción';
            document.getElementById('btn_capacidad_texto').textContent = 'Actualizar Capacidad';
            
            document.getElementById('cap_producto_id').value = capacidad.producto_id;
            document.getElementById('cap_capacidad_diaria').value = capacidad.capacidad_diaria;
            document.getElementById('cap_capacidad_semanal').value = capacidad.capacidad_semanal;
            document.getElementById('cap_capacidad_maxima').value = capacidad.capacidad_maxima_pedido;
            document.getElementById('cap_tiempo_entrega').value = capacidad.tiempo_entrega_estandar;
            document.getElementById('cap_distancia').value = capacidad.distancia_km;
            document.getElementById('cap_velocidad').value = capacidad.velocidad_promedio_kmh;
            document.getElementById('cap_aduanas').value = capacidad.tiempo_aduanas_dias;
            document.getElementById('cap_defectos').value = (parseFloat(capacidad.tasa_defectos_fabrica) * 100).toFixed(2);
            document.getElementById('cap_disponibles').value = capacidad.unidades_disponibles;
            document.getElementById('cap_proxima').value = capacidad.proxima_produccion || '';
            
            const descuentos = JSON.parse(capacidad.descuentos_volumen || '[]');
            const contenedor = document.getElementById('contenedor_descuentos');
            contenedor.innerHTML = '';
            
            if (descuentos.length >0) {
                descuentos.forEach((rango, idx) =>{
                    agregarRangoDescuentoConDatos(idx, rango);
                });
            } else {
                contenedor.innerHTML = '<div class="text-center py-4 text-slate-400 text-xs italic" id="msg_descuentos_vacio"><i class="fas fa-info-circle mr-1"></i>No hay rangos configurados. Haz clic en "Agregar Rango" para empezar.</div>';
            }
            
            document.getElementById('modalCapacidad').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        let contadorRangos = 0;

        function agregarRangoDescuento() {
            const contenedor = document.getElementById('contenedor_descuentos');
            const msgVacio = document.getElementById('msg_descuentos_vacio');
            if (msgVacio) msgVacio.remove();

            const idx = contadorRangos++;
            const rango = document.createElement('div');
            rango.className = 'rango-descuento grid grid-cols-4 gap-3 items-end p-3 bg-emerald-50 border border-emerald-200 rounded-lg';
            rango.innerHTML = `
                <div><label class="block text-[10px] font-bold text-emerald-700 mb-1">Desde (unid.)</label><input type="number" name="descuentos_volumen[${idx}][cantidad_min]" min="1" required class="form-input w-full rounded px-2 py-1.5 text-sm" placeholder="Ej: 10"></div><div><label class="block text-[10px] font-bold text-emerald-700 mb-1">Hasta (unid.)</label><input type="text" name="descuentos_volumen[${idx}][cantidad_max]" class="form-input w-full rounded px-2 py-1.5 text-sm" placeholder="Vacío = ilimitado"></div><div><label class="block text-[10px] font-bold text-emerald-700 mb-1">Descuento (%)</label><input type="number" step="0.01" min="0" max="100" required name="descuentos_volumen[${idx}][descuento]" class="form-input w-full rounded px-2 py-1.5 text-sm" placeholder="Ej: 5"></div><div class="flex justify-end"><button type="button" onclick="this.closest('.rango-descuento').remove()" class="w-8 h-8 bg-red-100 text-red-600 rounded hover:bg-red-200 transition"><i class="fas fa-trash text-xs"></i></button></div>`;
            contenedor.appendChild(rango);
        }

        function agregarRangoDescuentoConDatos(idx, rango) {
            const contenedor = document.getElementById('contenedor_descuentos');
            const msgVacio = document.getElementById('msg_descuentos_vacio');
            if (msgVacio) msgVacio.remove();

            const div = document.createElement('div');
            div.className = 'rango-descuento grid grid-cols-4 gap-3 items-end p-3 bg-emerald-50 border border-emerald-200 rounded-lg';
            div.innerHTML = `
                <div><label class="block text-[10px] font-bold text-emerald-700 mb-1">Desde (unid.)</label><input type="number" name="descuentos_volumen[${idx}][cantidad_min]" min="1" required class="form-input w-full rounded px-2 py-1.5 text-sm" value="${rango.cantidad_min}"></div><div><label class="block text-[10px] font-bold text-emerald-700 mb-1">Hasta (unid.)</label><input type="text" name="descuentos_volumen[${idx}][cantidad_max]" class="form-input w-full rounded px-2 py-1.5 text-sm" value="${rango.cantidad_max || ''}" placeholder="Vacío = ilimitado"></div><div><label class="block text-[10px] font-bold text-emerald-700 mb-1">Descuento (%)</label><input type="number" step="0.01" min="0" max="100" required name="descuentos_volumen[${idx}][descuento]" class="form-input w-full rounded px-2 py-1.5 text-sm" value="${rango.descuento}"></div><div class="flex justify-end"><button type="button" onclick="this.closest('.rango-descuento').remove()" class="w-8 h-8 bg-red-100 text-red-600 rounded hover:bg-red-200 transition"><i class="fas fa-trash text-xs"></i></button></div>`;
            contenedor.appendChild(div);
            contadorRangos = Math.max(contadorRangos, idx + 1);
        }

        // ============================================
        // FUNCIONES DEL MODAL DE PROPUESTA
        // ============================================
        function abrirModalPropuesta(solicitud) {
            document.getElementById('prop_solicitud_id').value = solicitud.id;
            document.getElementById('prop_producto_nombre').textContent = solicitud.producto_nombre;
            document.getElementById('prop_cantidad_solicitada').textContent = solicitud.cantidad_necesaria;
            
            document.getElementById('comp_cantidad').value = solicitud.cantidad_necesaria;
            
            document.getElementById('activar_completa').checked = true;
            document.getElementById('activar_parcial').checked = false;
            toggleOfertaCompleta();
            toggleOfertaParcial();
            
            document.getElementById('contenedor_lotes').innerHTML = '<div class="text-center py-4 text-slate-400 text-xs italic" id="msg_lotes_vacio"><i class="fas fa-info-circle mr-1"></i>No hay lotes configurados. Haz clic en "Agregar Lote" para empezar.</div>';
            
            document.getElementById('modalPropuesta').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalPropuesta() {
            document.getElementById('modalPropuesta').classList.add('hidden');
            document.body.style.overflow = '';
        }

        function toggleOfertaCompleta() {
            const activado = document.getElementById('activar_completa').checked;
            const campos = document.getElementById('campos_completa');
            if (activado) {
                campos.classList.remove('opacity-50', 'pointer-events-none');
            } else {
                campos.classList.add('opacity-50', 'pointer-events-none');
            }
        }

        function toggleOfertaParcial() {
            const activado = document.getElementById('activar_parcial').checked;
            const campos = document.getElementById('campos_parcial');
            if (activado) {
                campos.classList.remove('opacity-50', 'pointer-events-none');
            } else {
                campos.classList.add('opacity-50', 'pointer-events-none');
            }
        }

        let contadorLotes = 0;

        function agregarLote() {
            const contenedor = document.getElementById('contenedor_lotes');
            const msgVacio = document.getElementById('msg_lotes_vacio');
            if (msgVacio) msgVacio.remove();

            const idx = contadorLotes++;
            const lote = document.createElement('div');
            lote.className = 'lote-item grid grid-cols-3 gap-3 items-end p-3 bg-blue-50 border border-blue-200 rounded-lg';
            lote.innerHTML = `
                <div><label class="block text-[10px] font-bold text-blue-700 mb-1">Fecha de entrega</label><input type="date" name="oferta_parcial[lotes][${idx}][fecha]" required class="form-input w-full rounded px-2 py-1.5 text-sm"></div><div><label class="block text-[10px] font-bold text-blue-700 mb-1">Unidades</label><input type="number" name="oferta_parcial[lotes][${idx}][unidades]" min="1" required class="form-input w-full rounded px-2 py-1.5 text-sm" placeholder="Ej: 200"></div><div class="flex justify-end"><button type="button" onclick="this.closest('.lote-item').remove()" class="w-8 h-8 bg-red-100 text-red-600 rounded hover:bg-red-200 transition"><i class="fas fa-trash text-xs"></i></button></div>`;
            contenedor.appendChild(lote);
        }

        // ============================================
        // FUNCIONES DEL MODAL DE DETALLE DE PROPUESTA
        // ============================================
        function verDetallePropuesta(propuesta) {
            const content = document.getElementById('detalle_propuesta_content');
            const calendario = JSON.parse(propuesta.calendario_entregas || '[]');
            const es_parcial = calendario.length >1;
            
            const estadoBadge = propuesta.estado === 'aprobada' 
                ? '<span class="badge bg-emerald-100 text-emerald-700"><i class="fas fa-check-circle mr-1"></i>Aprobada</span>'
                : (propuesta.estado === 'rechazada' 
                    ? '<span class="badge bg-red-100 text-red-700"><i class="fas fa-times-circle mr-1"></i>Rechazada</span>'
                    : '<span class="badge bg-amber-100 text-amber-700"><i class="fas fa-clock mr-1"></i>Pendiente</span>');
            
            let html = `
                <div class="grid grid-cols-2 gap-6"><div><h4 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Información del Producto</h4><div class="bg-slate-50 rounded-lg p-4"><p class="text-lg font-bold text-slate-900">${propuesta.producto_nombre}</p><p class="text-sm text-slate-600 font-mono">SKU: ${propuesta.producto_sku || 'N/A'}</p><p class="text-xs text-slate-500 mt-2">Cantidad solicitada: <strong>${propuesta.cantidad_necesaria} unidades</strong></p></div></div><div><h4 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Estado de la Propuesta</h4><div class="bg-slate-50 rounded-lg p-4">${estadoBadge}
                            <p class="text-xs text-slate-500 mt-2">Enviada el ${new Date(propuesta.created_at).toLocaleDateString('es-ES')}</p></div></div></div><div class="border-t pt-6"><h4 class="text-sm font-bold ${es_parcial ? 'text-blue-700' : 'text-emerald-700'} uppercase tracking-wider mb-4"><i class="fas fa-${es_parcial ? 'truck' : 'box'} mr-1"></i>${es_parcial ? 'OPCIÓN: Entrega Parcial' : 'OPCIÓN: Entrega Total'}
                    </h4><div class="grid grid-cols-4 gap-4 mb-4"><div class="bg-slate-50 rounded-lg p-4"><p class="text-xs text-slate-600 font-medium">Cantidad Ofrecida</p><p class="text-2xl font-bold text-slate-900">${propuesta.cantidad_ofrecida}</p><p class="text-xs text-slate-500">unidades</p></div><div class="bg-slate-50 rounded-lg p-4"><p class="text-xs text-slate-600 font-medium">Días de Entrega</p><p class="text-2xl font-bold text-slate-900">${propuesta.dias_entrega}</p><p class="text-xs text-slate-500">días</p></div><div class="bg-slate-50 rounded-lg p-4"><p class="text-xs text-slate-600 font-medium">Precio Unitario</p><p class="text-2xl font-bold text-slate-900">$${parseFloat(propuesta.precio_unitario).toFixed(2)}</p><p class="text-xs text-slate-500">por unidad</p></div><div class="bg-slate-50 rounded-lg p-4"><p class="text-xs text-slate-600 font-medium">Precio Total</p><p class="text-2xl font-bold text-slate-900">$${parseFloat(propuesta.precio_total).toFixed(2)}</p><p class="text-xs text-slate-500">valor total</p></div></div>${es_parcial ? `
                        <h5 class="text-xs font-bold text-slate-700 mb-2"><i class="fas fa-calendar-alt mr-1"></i>Calendario de Entregas</h5><div class="space-y-2">${calendario.map((lote, idx) =>`
                                <div class="flex items-center gap-3 p-3 bg-blue-50 rounded-lg"><span class="badge bg-blue-600 text-white">Lote ${idx + 1}</span><span class="text-sm text-slate-700"><i class="fas fa-calendar mr-1"></i>${lote.fecha}</span><span class="text-sm font-bold text-slate-900"><i class="fas fa-box mr-1"></i>${lote.unidades} unidades</span></div>`).join('')}
                        </div>` : ''}
                </div>`;
            
            if (propuesta.notas) {
                html += `
                    <div class="border-t pt-6"><h4 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-2">Notas del Proveedor</h4><div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4"><p class="text-sm text-yellow-900">${propuesta.notas}</p></div></div>`;
            }
            
            content.innerHTML = html;
            document.getElementById('modalDetallePropuesta').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }

        function cerrarModalDetalle() {
            document.getElementById('modalDetallePropuesta').classList.add('hidden');
            document.body.style.overflow = '';
        }
    </script><script src="assets/emx_modales.js"></script></body></html>