# Fase 6: mover SQL, migraciones, funciones y triggers

Fecha de generación: 2026-08-02 23:18:19

## Objetivo

Organizar todos los archivos SQL del proyecto en una estructura clara y mantenible.

## Qué se hizo

1. Se movió el script base a `database/schema`.
2. Se movieron migraciones a `database/migrations`.
3. Se movieron hotfixes a `database/hotfixes`.
4. Se creó inventario de funciones SQL detectadas.
5. Se creó inventario de triggers detectados.
6. Se dejaron adaptadores en la raíz para no romper comandos antiguos.
7. Se creó manifiesto SQL en Markdown, CSV y JSON.

## Resultado

```text
Archivos SQL organizados: 22
Funciones SQL detectadas: 4
Triggers SQL detectados: 2
```

## Importante

No se ejecutó ningún SQL sobre una base de datos real. Esta fase solo organiza archivos.
