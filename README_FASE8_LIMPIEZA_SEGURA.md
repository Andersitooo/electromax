# ElectroMax - Fase 8

## Fase realizada

Se limpió el proyecto de forma segura, sin eliminar archivos que puedan romper rutas, formularios o referencias de base de datos.

## Qué se limpió

```text
README antiguos de raíz -> docs/historial/readmes_raiz
Auditorías sueltas      -> docs/auditorias
```

## Qué no se eliminó

```text
Archivos PHP de raíz
Adaptadores PHP
Adaptadores SQL
Uploads e imágenes
database/
views/
app/
```

## Por qué

Algunos archivos parecen duplicados, pero pueden estar referenciados por:

```text
URLs antiguas
formularios POST
includes PHP
base de datos
rutas de imágenes guardadas
comandos psql antiguos
```

Por eso se documentaron los candidatos, pero no se borraron automáticamente.

## Siguiente fase

Fase 9: generar README técnico para defensa del proyecto.
