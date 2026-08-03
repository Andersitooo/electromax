# Fix iconos de categorías

Se restauraron los iconos visuales de la sección Categorías en `index.php`.

Causa:
La limpieza de emojis dejó clases pegadas, por ejemplo:
- `bg-blue-50border`
- `bg-blue-600w-12`
- `fa-tvtext-white`

Eso impedía que Tailwind/FontAwesome mostraran los iconos correctamente.

Corrección:
- Se restauraron espacios en clases Tailwind.
- Se restauró la clase FontAwesome del icono.
- Se corrigieron textos pegados como `1productos` y `4disponibles`.
- No se agregaron emojis tipo celular; solo iconos FontAwesome del diseño.
