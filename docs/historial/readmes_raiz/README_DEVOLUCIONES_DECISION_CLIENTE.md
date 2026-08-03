# Parche devoluciones: admin ofrece, cliente decide

Corrige el error:

`SQLSTATE[42703]: Undefined column: no existe la columna historial_estados en devoluciones`

También ajusta el flujo de devoluciones antes de 30 días:

1. Cliente reporta el problema.
2. Admin revisa.
3. Admin autoriza retorno.
4. Producto llega al almacén.
5. Técnico inspecciona.
6. Admin usa `Ofrecer solución al cliente`.
7. Admin habilita:
   - Solo reembolso
   - Solo cambio por otro igual
   - Reembolso o cambio, cliente elige
8. Cliente decide desde Mi cuenta.
9. Admin ejecuta:
   - Reembolso + nota de crédito
   - Cambio por otro producto igual

Estados nuevos:
- `esperando_decision_cliente`
- `cliente_eligio_reembolso`
- `cliente_eligio_cambio`

SQL incluido:
- `migracion_devoluciones_decision_cliente.sql`

Ejecuta el SQL una sola vez si tu base todavía no tiene esas columnas.
El código también intenta agregar las columnas automáticamente para evitar el error fatal.
