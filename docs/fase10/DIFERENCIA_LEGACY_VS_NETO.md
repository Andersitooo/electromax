# Diferencia entre modo legacy y modo neto

## Modo legacy

Entrada:

```text
index.php
admin.php
proveedor.php
```

Este modo conserva la forma original de XAMPP.

## Modo neto

Entrada:

```text
public/index.php
public/admin.php
public/proveedor.php
```

Estos archivos cargan directamente controladores en `app/Controllers`.

## Por qué conviven

Durante pruebas conviene conservar ambos.

Si algo falla en modo neto, todavía puedes comparar con modo legacy y detectar exactamente qué cambio causó el problema.
