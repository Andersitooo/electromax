# Adaptadores opcionales en public

Se agregaron adaptadores en:

```text
public/
```

Ejemplos:

```text
public/index.php
public/admin.php
public/proveedor.php
```

## Para qué sirven

Hoy tu proyecto sigue pensado para funcionar desde la raíz en XAMPP:

```text
http://localhost/electro2/index.php
```

Pero si más adelante decides apuntar Apache hacia `public/`, esos adaptadores permiten cargar los controladores heredados sin moverlos de golpe.

## Importante

No es obligatorio cambiar Apache ni DocumentRoot ahora.
