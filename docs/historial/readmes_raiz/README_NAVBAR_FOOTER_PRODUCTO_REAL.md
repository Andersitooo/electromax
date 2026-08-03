# Navbar y footer reales en producto.php

Cambios aplicados:
- Se reemplazó el navbar antiguo de `producto.php` por `components/navbar.php`.
- Se reemplazó el footer simple de `producto.php` por `components/footer.php`.
- Se reescribieron ambos componentes con diseño más profesional y reutilizable.
- Se quitó información ficticia anterior como San Salvador, El Salvador, +503 y contacto@electromax.com.
- El footer ahora usa información real de ElectroMax en Babahoyo, Los Ríos, Ecuador.
- Si existe tabla de empresa (`empresa_config`, `empresa_configuracion` o `empresa`), toma de ahí razón social, email, teléfono y dirección.
- Si no existe esa tabla, usa como respaldo:
  - ElectroMax S.A.S.
  - abustamante831@fafi.utb.edu.ec
  - 04-273-0000
  - Babahoyo, Los Ríos, Ecuador

Páginas mejoradas directamente:
- producto.php
- components/navbar.php
- components/footer.php
