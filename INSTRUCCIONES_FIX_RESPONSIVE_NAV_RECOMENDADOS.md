# INSTRUCCIONES DE ACTUALIZACIÓN - Fix responsive, navbar activo y recomendados

## En tu PC local

1. Descomprime este ZIP.
2. Copia el contenido de la carpeta `electromax` encima de tu carpeta local actual:

C:\Users\Ander Bustamante\Documents\Ander Bustamante Garcia\Sexto Semestre\Inteligencia de Negocios\posiblesubidaVPS\electromax

No borres la carpeta `.git`.

3. Abre CMD dentro de tu carpeta local `electromax` y ejecuta:

```cmd
git status
git add .
git commit -m "Fix responsive navbar activo y recomendados"
git push
```

## En el VPS

```bash
cd /var/www/anderspace/electromax
git pull
php scripts/verificar_fix_responsive_nav_recomendados.php
sudo systemctl restart php-fpm
sudo systemctl reload nginx
```

## Pruebas en navegador

- Abrir `https://anderspace.online` en móvil/tablet/PC.
- Entrar a Inicio, Aires acondicionados, Cocinas y hornos, Lavadoras, etc.; el menú debe marcar la sección activa.
- Abrir una ficha de producto y revisar debajo de especificaciones técnicas: debe aparecer Productos recomendados si existen otros productos activos.
