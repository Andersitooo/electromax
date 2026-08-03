# Verificación de adaptadores

Se agregó el script:

```text
scripts/verificar_adaptadores_fase7.php
```

Uso:

```bash
php scripts/verificar_adaptadores_fase7.php
```

Qué revisa:

```text
1. Que cada ruta PHP antigua exista.
2. Que cada destino interno documentado exista.
3. Que cada adaptador SQL apunte a un archivo real en database/.
```

No se conecta a la base de datos.

No ejecuta rutas web.

Solo valida existencia de archivos.
