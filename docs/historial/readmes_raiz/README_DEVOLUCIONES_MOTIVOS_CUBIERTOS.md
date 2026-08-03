# Devoluciones: motivos cubiertos

Este parche corrige la cobertura de todos los motivos visibles en el formulario de devolución.

Motivos cubiertos por responsabilidad de ElectroMax:
- Producto defectuoso / no funciona
- Producto incorrecto
- Faltan piezas o accesorios
- Caja abierta / sello roto
- Dañado durante el envío

Motivos cubiertos por decisión del cliente, con costo de envío de retorno:
- No me gusta / arrepentimiento
- Talla, color o variante no esperada
- Encontró mejor precio
- Ya no lo necesita

Otro motivo:
- El sistema clasifica el texto como responsabilidad de ElectroMax, decisión del cliente, courier o sin clasificar.
- Si queda sin clasificar, lo deja en revisión para que el admin decida.

Corrección importante:
- `talla_color` existía en el formulario, pero no estaba en el arreglo backend de motivos por decisión del cliente. Ahora sí queda cubierto y aplica el costo de envío correcto.

Flujo:
- Cualquier motivo válido crea un caso.
- La categoría del motivo define evidencia, costo de retorno y tipo de caso.
- Luego entra al flujo secuencial de admin: revisión, retorno, almacén, inspección, solución ofrecida, decisión del cliente y cierre.
