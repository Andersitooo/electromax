# Analítica financiera avanzada + limpieza visual

Cambios incluidos:

- Reemplazo de `analitica.php` por un dashboard estilo Power BI para administración.
- Filtros por hoy, semana, mes, últimos 30 días, últimos 3 meses, trimestre, año y rango personalizado.
- Comparación contra período anterior, mes anterior o año anterior.
- Agrupación de gráficos por día, semana o mes.
- KPIs: ingresos, utilidad bruta, margen, pedidos, ticket promedio, tasa de devoluciones.
- Resumen financiero: subtotal, IVA, descuentos, costos estimados, notas de crédito e ingresos Prime.
- Gráficos: ingresos vs utilidad, categorías, marcas, métodos de pago, productos más vendidos, productos con mayor utilidad, productos menos vendidos, últimos 3 meses, últimos 12 meses, devoluciones por estado y por motivo.
- Tablas: productos financieros, clientes con mayor compra, ventas por ciudad y stock bajo.
- Exportación CSV de productos del período.
- Link agregado en el panel admin: `Analítica financiera`.
- Limpieza de emojis literales fastidiosos en textos/opciones/comentarios, conservando iconos FontAwesome del diseño, categorías, wishlist y especificaciones.
- Se mantiene el uso de modales del sistema; no se agregan `alert()` ni `confirm()` nativos.

No requiere SQL nuevo. El dashboard usa las tablas existentes y se adapta si algunas columnas opcionales no existen.
