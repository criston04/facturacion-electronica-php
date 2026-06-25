-- ============================================================
--  Datos de ejemplo para facturacion_electronica
--  Usuario de acceso:  admin  /  admin123
--  (el hash corresponde a password_hash('admin123', PASSWORD_DEFAULT))
-- ============================================================
USE facturacion_electronica;

-- Roles (tipo = 1)
INSERT INTO enumerados (tipo, valor, nombre) VALUES
  (1, 1, 'Administrador'),
  (1, 2, 'Vendedor');

-- Usuario administrador  (login: admin / admin123)
INSERT INTO usuarios (nombres, apellidos, usuario, clave, enum_rol, estado) VALUES
  ('Administrador', 'del Sistema', 'admin',
   '$2y$12$eto0CC5.4XGGC21hsmZBnuY5JNSnQXRmQ12O2BG0AJF6dvGuQkqUO', 1, 1);

-- Emisor de demostración.
--   NOTA: token_cliente / ruc_proveedor son de PRUEBA; con credenciales
--   reales de facturalahoy.com el envío a SUNAT funcionará. Sin ellas,
--   el comprobante se guarda localmente como 'pendiente' (el código lo maneja).
--   Se crean id=1 (lo usa print_venta.php) e id=2 (lo usa Facturalaya.php).
INSERT INTO emisor
  (id, ruc, tipo_doc, razon_social, nom_comercial, email, codigo_ubigeo, direccion,
   direccion_departamento, direccion_provincia, direccion_distrito, direccion_codigopais,
   modalidad_envio_sunat, logo, token_cliente, ruc_proveedor, tipo_certificado, tipo_proceso)
VALUES
  (1, '20123456789', '6', 'CETI CAPACITACIONES S.A.C.', 'CETI', 'ventas@ceti.org.pe',
   '150101', 'AV. AREQUIPA 123', 'LIMA', 'LIMA', 'LIMA', 'PE',
   'produccion', NULL, 'TOKEN_DE_PRUEBA', '20123456789', 'demo', 'produccion'),
  (2, '20123456789', '6', 'CETI CAPACITACIONES S.A.C.', 'CETI', 'ventas@ceti.org.pe',
   '150101', 'AV. AREQUIPA 123', 'LIMA', 'LIMA', 'LIMA', 'PE',
   'produccion', NULL, 'TOKEN_DE_PRUEBA', '20123456789', 'demo', 'produccion');

-- Categorías
INSERT INTO categorias (nombre, descripcion, estado) VALUES
  ('Abarrotes', 'Productos de abarrotes en general', 'ACTIVO'),
  ('Bebidas', 'Bebidas y gaseosas', 'ACTIVO'),
  ('Servicios', 'Servicios varios', 'ACTIVO');

-- Proveedor
INSERT INTO proveedores
  (empresa, nombre_comercial, condicion, estado_ruc, tipo, codigo_ubigeo, ruc,
   contacto, telefono, email, direccion, estado)
VALUES
  ('DISTRIBUIDORA LIMA S.A.C.', 'Distrilima', 'HABIDO', 'ACTIVO', 'JURIDICA',
   '150101', '20987654321', 'Juan Pérez', '987654321', 'contacto@distrilima.pe',
   'AV. INDUSTRIAL 456', 'ACTIVO');

-- Productos
INSERT INTO productos
  (codigo, nombre, descripcion, id_categoria, id_proveedor, precio_venta, costo_compra,
   stock_actual, stock_minimo, codigo_barras, estado)
VALUES
  ('P001', 'Arroz Costeño 1kg', 'Arroz extra bolsa 1kg', 1, 1, 5.50, 4.20, 120, 10, '7750001000011', 'ACTIVO'),
  ('P002', 'Aceite Primor 1L',  'Aceite vegetal 1 litro', 1, 1, 9.90, 7.50, 80, 10, '7750001000028', 'ACTIVO'),
  ('P003', 'Gaseosa Inca Kola 500ml', 'Gaseosa personal 500ml', 2, 1, 2.50, 1.80, 200, 20, '7750001000035', 'ACTIVO'),
  ('P004', 'Servicio de instalación', 'Servicio técnico por hora', 3, 1, 50.00, 0.00, 0, 0, NULL, 'ACTIVO');

-- Clientes (uno con RUC para factura, uno con DNI para boleta)
INSERT INTO clientes
  (tipo_documento, numero_documento, nombres, apellido_paterno, apellido_materno,
   razon_social, condicion, estado, codigo_ubigeo, direccion, telefono, email,
   estado_cliente, pais, departamento, provincia, distrito)
VALUES
  ('RUC', '20555666777', NULL, NULL, NULL, 'COMERCIAL ANDINA S.A.C.', 'HABIDO', 'ACTIVO',
   '150101', 'JR. COMERCIO 789', '012345678', 'compras@andina.pe', 'ACTIVO', 'PE', 'LIMA', 'LIMA', 'LIMA'),
  ('DNI', '45678912', 'María', 'Quispe', 'Flores', NULL, NULL, NULL,
   '150101', 'CALLE LAS FLORES 321', '999888777', 'maria.quispe@gmail.com', 'ACTIVO', 'PE', 'LIMA', 'LIMA', 'MIRAFLORES');
