# Centro de Soporte ElectroMax

Módulo simple de tickets para que el cliente contacte a la empresa sin crear un flujo complicado.

## Archivos agregados

- `soporte.php`
- `soporte_admin.php`
- `funciones_soporte.php`
- `migracion_soporte_tickets.sql`

## Ejecutar migración

```bash
psql -d electro2 -f migracion_soporte_tickets.sql
```

## Motivos cubiertos

- Consulta sobre pedido
- Pago o factura
- Entrega o seguimiento
- Devolución o garantía
- Cuenta o acceso
- Consulta general

## Flujo del cliente

```text
Cliente crea ticket
↓
Admin revisa
↓
Admin responde o pide más información
↓
Cliente puede responder
↓
Admin o cliente cierra el ticket
```

## Estados

```text
abierto
en_revision
respondido
esperando_cliente
cerrado
```

## Admin

La nueva sección está en:

```text
soporte_admin.php
```

También se agregó enlace en el sidebar del admin y en el navbar/footer del cliente.
