-- ============================================================================
-- ELECTROMAX - SCRIPT DE INSTALACIÓN LIMPIO DE BASE DE DATOS (PostgreSQL)
-- ============================================================================

-- 1. Habilitar extensión para UUIDs
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";

-- ============================================================================
-- 2. TABLAS DE REFERENCIA (Sin dependencias de claves foráneas)
-- ============================================================================

CREATE TABLE IF NOT EXISTS roles (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nombre VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS planes (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nombre VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    precio_mensual NUMERIC(10,2) NOT NULL,
    descripcion TEXT,
    beneficios JSONB DEFAULT '[]'::jsonb,
    es_prime BOOLEAN DEFAULT FALSE,
    orden INTEGER DEFAULT 1,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sucursales (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nombre VARCHAR(100) NOT NULL,
    direccion TEXT NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    telefono VARCHAR(50),
    email VARCHAR(150),
    latitud DECIMAL(10, 8) NOT NULL,
    longitud DECIMAL(11, 8) NOT NULL,
    horario_atencion VARCHAR(100),
    es_matriz BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS categorias (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nombre VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    filtros_disponibles JSONB DEFAULT '[]'::jsonb,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS marcas (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nombre VARCHAR(100) NOT NULL,
    pais_origen VARCHAR(100),
    logo_url TEXT,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 3. GESTIÓN DE USUARIOS
-- ============================================================================

CREATE TABLE IF NOT EXISTS usuarios (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    rol_id UUID REFERENCES roles(id),
    plan_id UUID REFERENCES planes(id),
    cedula_ruc VARCHAR(20),
    nombres VARCHAR(100) NOT NULL,
    apellidos VARCHAR(100) NOT NULL,
    email VARCHAR(150) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    foto_perfil_url TEXT,
    ultima_ip VARCHAR(45),
    ciudad_detectada VARCHAR(100),
    tiene_badge_verificado BOOLEAN DEFAULT FALSE,
    es_prime BOOLEAN DEFAULT FALSE,
    es_verificado BOOLEAN DEFAULT FALSE,
    plan_activo BOOLEAN DEFAULT FALSE,
    plan_expira_en TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS direcciones_usuario (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    usuario_id UUID REFERENCES usuarios(id) ON DELETE CASCADE,
    alias VARCHAR(50) NOT NULL,
    direccion TEXT NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    codigo_postal VARCHAR(20),
    telefono VARCHAR(50),
    latitud DECIMAL(10, 8),
    longitud DECIMAL(11, 8),
    es_principal BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(usuario_id, alias)
);

-- ============================================================================
-- 4. CATÁLOGO DE PRODUCTOS
-- ============================================================================

CREATE TABLE IF NOT EXISTS productos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    categoria_id UUID REFERENCES categorias(id),
    marca_id UUID REFERENCES marcas(id),
    nombre VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    sku VARCHAR(50) UNIQUE,
    descripcion_corta TEXT,
    especificaciones_tecnicas JSONB DEFAULT '{}'::jsonb,
    peso_kg NUMERIC(10,2),
    precio_base NUMERIC(12,2) NOT NULL,
    iva_porcentaje NUMERIC(5,2) DEFAULT 15.00,
    descuento_porcentaje NUMERIC(5,2) DEFAULT 0,
    descuento_desde DATE,
    descuento_hasta DATE,
    stock_actual_global INTEGER DEFAULT 0,
    stock_maximo INTEGER DEFAULT 0,
    punto_reorden INTEGER DEFAULT 0,
    estado_seguridad VARCHAR(20) DEFAULT 'NORMAL',
    calificacion_promedio NUMERIC(3,2) DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS producto_multimedia (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    producto_id UUID REFERENCES productos(id) ON DELETE CASCADE,
    tipo VARCHAR(50) NOT NULL DEFAULT 'FOTO',
    url VARCHAR(500) NOT NULL,
    orden INTEGER DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_producto_multimedia_prod ON producto_multimedia(producto_id);

-- ============================================================================
-- 5. INVENTARIO Y PROVEEDORES
-- ============================================================================

CREATE TABLE IF NOT EXISTS inventario_sucursal (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    sucursal_id UUID REFERENCES sucursales(id) ON DELETE CASCADE,
    producto_id UUID REFERENCES productos(id) ON DELETE CASCADE,
    stock INTEGER DEFAULT 0,
    stock_reservado INTEGER DEFAULT 0,
    ultimo_reabastecimiento TIMESTAMP,
    UNIQUE(sucursal_id, producto_id)
);
CREATE INDEX IF NOT EXISTS idx_inventario_sucursal ON inventario_sucursal(sucursal_id, producto_id);

CREATE TABLE IF NOT EXISTS producto_proveedor (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    producto_id UUID REFERENCES productos(id) ON DELETE CASCADE,
    proveedor_id UUID REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE(producto_id, proveedor_id)
);

CREATE TABLE IF NOT EXISTS solicitudes_reabastecimiento (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    producto_id UUID REFERENCES productos(id),
    sucursal_matriz_id UUID REFERENCES sucursales(id),
    cantidad_necesaria INTEGER NOT NULL,
    estado VARCHAR(20) DEFAULT 'pendiente', -- pendiente, cotizada, aprobada, cancelada
    fecha_limite DATE DEFAULT (CURRENT_DATE + INTERVAL '7 days'),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS cotizaciones_proveedores (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    solicitud_id UUID REFERENCES solicitudes_reabastecimiento(id) ON DELETE CASCADE,
    proveedor_id UUID REFERENCES usuarios(id),
    precio_unitario NUMERIC(10,2) NOT NULL,
    dias_entrega INTEGER NOT NULL,
    estado VARCHAR(20) DEFAULT 'pendiente', -- pendiente, aprobada, rechazada
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ============================================================================
-- 6. PEDIDOS Y CHECKOUT
-- ============================================================================

CREATE TABLE IF NOT EXISTS pedidos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    usuario_id UUID REFERENCES usuarios(id),
    nombre_cliente VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefono VARCHAR(50) NOT NULL,
    direccion VARCHAR(255) NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    codigo_postal VARCHAR(20),
    direccion_envio_id UUID REFERENCES direcciones_usuario(id),
    sucursal_asignada_id UUID REFERENCES sucursales(id),
    distancia_km DECIMAL(8,2),
    subtotal NUMERIC(10,2) NOT NULL,
    iva_total NUMERIC(10,2) NOT NULL,
    descuento_aplicado NUMERIC(10,2) DEFAULT 0,
    total NUMERIC(10,2) NOT NULL,
    estado VARCHAR(50) DEFAULT 'Pendiente',
    metodo_pago VARCHAR(50) DEFAULT 'Tarjeta de Crédito',
    ultimos_4_digitos VARCHAR(4),
    marca_tarjeta VARCHAR(20),
    tipo_descuento VARCHAR(50),
    membresia_usada VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS detalle_pedidos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    pedido_id UUID REFERENCES pedidos(id) ON DELETE CASCADE,
    producto_id UUID REFERENCES productos(id),
    sucursal_origen_id UUID REFERENCES sucursales(id),
    nombre_producto VARCHAR(255) NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario NUMERIC(10,2) NOT NULL,
    iva_porcentaje NUMERIC(5,2) NOT NULL,
    total NUMERIC(10,2) NOT NULL
);
CREATE INDEX IF NOT EXISTS idx_detalle_pedidos_sucursal ON detalle_pedidos(sucursal_origen_id);

-- ============================================================================
-- 7. FUNCIONALIDADES EXTRA (Vistos, Banners)
-- ============================================================================

CREATE TABLE IF NOT EXISTS productos_vistos (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    usuario_id UUID REFERENCES usuarios(id) ON DELETE CASCADE,
    producto_id UUID REFERENCES productos(id) ON DELETE CASCADE,
    visto_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(usuario_id, producto_id)
);
CREATE INDEX IF NOT EXISTS idx_productos_vistos_usuario ON productos_vistos(usuario_id, visto_en DESC);

CREATE TABLE IF NOT EXISTS page_sections (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    nombre VARCHAR(100) NOT NULL,
    tipo VARCHAR(50) NOT NULL, -- 'carousel', 'grid_2', 'grid_3', 'grid_4', 'single'
    posicion INTEGER NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS banners_promocionales (
    id UUID PRIMARY KEY DEFAULT uuid_generate_v4(),
    section_id UUID REFERENCES page_sections(id) ON DELETE CASCADE,
    titulo VARCHAR(150),
    subtitulo VARCHAR(250),
    imagen_url TEXT NOT NULL,
    enlace_destino TEXT,
    orden INTEGER DEFAULT 1,
    fecha_inicio DATE,
    fecha_fin DATE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX IF NOT EXISTS idx_banners_posicion ON banners_promocionales(section_id, is_active);

-- ============================================================================
-- 8. DATOS DE PRUEBA (SEEDERS)
-- ============================================================================

-- Roles
INSERT INTO roles (nombre, descripcion) VALUES 
('SUPERADMIN', 'Acceso total e irrestricto al sistema.'),
('ADMIN', 'Administrador general. Gestiona productos, pedidos, usuarios.'),
('CLIENTE', 'Usuario estándar de la tienda.'),
('CLIENTE_PRIME', 'Cliente con suscripción activa y beneficios.'),
('TECNICO', 'Encargado de revisiones, garantías y devoluciones.'),
('PROVEEDOR', 'Acceso limitado al portal de proveedores.')
ON CONFLICT (nombre) DO NOTHING;

-- Superadmin por defecto (Contraseña: password)
INSERT INTO usuarios (rol_id, nombres, apellidos, email, password_hash, telefono, cedula_ruc, tiene_badge_verificado, is_active, ultima_ip, ciudad_detectada)
SELECT id, 'Super', 'Admin', 'admin@electromax.com', '$2y$10$N9qo8uLOickgx2ZMRZoMyeIjZAgcfl7p92ldGxad68LJZdL17lhWy', '0999999999', '1234567890', TRUE, TRUE, '127.0.0.1', 'Quito'
FROM roles WHERE nombre = 'SUPERADMIN'
ON CONFLICT (email) DO NOTHING;

-- Categorías con filtros
INSERT INTO categorias (nombre, slug, filtros_disponibles) VALUES 
('Televisores', 'televisores', '[{"campo": "pulgadas", "label": "Pulgadas", "tipo": "number"}, {"campo": "resolucion", "label": "Resolución", "tipo": "text"}]'::jsonb),
('Refrigeradoras', 'refrigeradoras', '[{"campo": "capacidad_litros", "label": "Capacidad (Litros)", "tipo": "number"}, {"campo": "tipo", "label": "Tipo", "tipo": "select", "opciones": ["No Frost", "Convencional"]}]'::jsonb),
('Lavadoras', 'lavadoras', '[{"campo": "capacidad_kg", "label": "Capacidad (Kg)", "tipo": "number"}]'::jsonb),
('Aires Acondicionados', 'aires-acondicionados', '[{"campo": "capacidad_btu", "label": "Capacidad (BTU)", "tipo": "number"}]'::jsonb)
ON CONFLICT (slug) DO NOTHING;

-- Marcas
INSERT INTO marcas (nombre, pais_origen) VALUES 
('Samsung', 'Corea del Sur'),
('LG', 'Corea del Sur'),
('Indurama', 'Ecuador')
ON CONFLICT DO NOTHING;

-- Planes de Membresía
INSERT INTO planes (nombre, slug, precio_mensual, descripcion, beneficios, es_prime, orden) VALUES
('Plus', 'plus', 9.99, 'Ideal para compradores frecuentes', '[{"beneficio": "Envíos gratis en compras > $50", "icono": "fa-truck"}, {"beneficio": "5% de descuento", "icono": "fa-percent"}]'::jsonb, FALSE, 1),
('Pro', 'pro', 19.99, 'Para quienes buscan más beneficios', '[{"beneficio": "Envíos gratis sin mínimo", "icono": "fa-truck"}, {"beneficio": "10% de descuento", "icono": "fa-percent"}]'::jsonb, FALSE, 2),
('Prime', 'prime', 29.99, 'La experiencia definitiva', '[{"beneficio": "Envíos express 24h", "icono": "fa-shipping-fast"}, {"beneficio": "15% de descuento", "icono": "fa-percent"}, {"beneficio": "Badge verificado", "icono": "fa-check-circle"}]'::jsonb, TRUE, 3)
ON CONFLICT (slug) DO NOTHING;

-- Sucursales (La primera es la Matriz)
INSERT INTO sucursales (nombre, direccion, ciudad, telefono, email, latitud, longitud, horario_atencion, es_matriz) VALUES
('Matriz Central - Quicentro', 'Av. de la Prensa y Av. Naciones Unidas', 'Quito', '02-223-4567', 'matriz@electromax.com', -0.1807, -78.4678, 'Lun-Sáb 10:00-21:00', TRUE),
('Sucursal Sur - Mall del Sur', 'Av. Mariscal Sucre y Av. de los Shyris', 'Quito', '02-234-5678', 'sur@electromax.com', -0.2201, -78.5012, 'Lun-Sáb 10:00-21:00', FALSE),
('Sucursal Cumbayá', 'Av. Interoceánica y Eloy Alfaro', 'Cumbayá', '02-256-7890', 'cumbaya@electromax.com', -0.2089, -78.4345, 'Lun-Sáb 10:00-20:00', FALSE)
ON CONFLICT DO NOTHING;

-- Secciones de Banners
INSERT INTO page_sections (nombre, tipo, posicion) VALUES
('Hero Principal', 'carousel', 1),
('Promociones Destacadas', 'grid_2', 2),
('Categorías Populares', 'grid_3', 3),
('Ofertas Especiales', 'grid_4', 4)
ON CONFLICT DO NOTHING;

-- Producto de Prueba
INSERT INTO productos (categoria_id, marca_id, nombre, slug, sku, precio_base, stock_actual_global, stock_maximo, punto_reorden, especificaciones_tecnicas) 
SELECT c.id, m.id, 'Smart TV 43" 4K UHD', 'smart-tv-43-4k-uhd', 'TV-SAM-001', 345.00, 15, 50, 10, '{"pulgadas": 43, "resolucion": "3840 x 2160"}'::jsonb
FROM categorias c, marcas m 
WHERE c.slug = 'televisores' AND m.nombre = 'Samsung'
ON CONFLICT (slug) DO NOTHING;

-- Inventario inicial para el producto de prueba en la matriz
INSERT INTO inventario_sucursal (sucursal_id, producto_id, stock, ultimo_reabastecimiento)
SELECT s.id, p.id, 15, NOW()
FROM sucursales s, productos p
WHERE s.es_matriz = TRUE AND p.slug = 'smart-tv-43-4k-uhd'
ON CONFLICT (sucursal_id, producto_id) DO NOTHING;


-- Tabla de logs de tracking (historial de movimientos)
CREATE TABLE IF NOT EXISTS tracking_logs (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    pedido_id UUID REFERENCES pedidos(id) ON DELETE CASCADE,
    estado VARCHAR(50) NOT NULL,
    descripcion TEXT,
    ubicacion VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_tracking_logs_pedido ON tracking_logs(pedido_id);

-- Tabla de reportes de problemas
CREATE TABLE IF NOT EXISTS reportes_pedidos (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    pedido_id UUID REFERENCES pedidos(id) ON DELETE CASCADE,
    usuario_id UUID REFERENCES usuarios(id),
    tipo_problema VARCHAR(50) NOT NULL,
    descripcion TEXT NOT NULL,
    estado VARCHAR(50) DEFAULT 'Pendiente', -- Pendiente, En Revisión, Resuelto
    respuesta_admin TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
CREATE INDEX idx_reportes_pedidos_pedido ON reportes_pedidos(pedido_id);



-- ============================================================================
-- SISTEMA DE DEVOLUCIONES INTELIGENTES - ElectroMax
-- ============================================================================

-- 1. Tabla principal de devoluciones
CREATE TABLE IF NOT EXISTS devoluciones (
    id UUID DEFAULT gen_random_uuid() PRIMARY KEY,
    pedido_id UUID REFERENCES pedidos(id) ON DELETE CASCADE,
    usuario_id UUID REFERENCES usuarios(id),
    motivo VARCHAR(50) NOT NULL, 
    descripcion TEXT,
    fotos_evidencia JSONB DEFAULT '[]'::jsonb,
    estado VARCHAR(30) DEFAULT 'pendiente_revision',
    tipo_reembolso VARCHAR(30),
    costo_envio_retorno NUMERIC(10,2) DEFAULT 0.00,
    comentario_admin TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Índices para rendimiento
CREATE INDEX IF NOT EXISTS idx_devoluciones_usuario ON devoluciones(usuario_id);
CREATE INDEX IF NOT EXISTS idx_devoluciones_pedido ON devoluciones(pedido_id);
CREATE INDEX IF NOT EXISTS idx_devoluciones_estado ON devoluciones(estado);

-- 3. Agregar columna para contar devoluciones (opcional, para detección de fraude más rápida)
ALTER TABLE usuarios 
ADD COLUMN IF NOT EXISTS total_devoluciones INTEGER DEFAULT 0;


ALTER TABLE devoluciones 
ADD COLUMN IF NOT EXISTS solucion_propuesta VARCHAR(50) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS respuesta_usuario VARCHAR(50) DEFAULT 'pendiente'; 
-- Valores de respuesta_usuario: 'pendiente', 'aceptada', 'rechazada'