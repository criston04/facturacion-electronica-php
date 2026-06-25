-- ============================================================
--  Base de datos: facturacion_electronica
--  Esquema reconstruido a partir del código PHP del proyecto
--  (clases en admin/classes + páginas en pages/)
--  Motor: MySQL / MariaDB  |  Charset: utf8mb4
-- ============================================================

CREATE DATABASE IF NOT EXISTS facturacion_electronica
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE facturacion_electronica;

SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- enumerados: catálogos genéricos (roles, etc.)
--   tipo = 1  -> roles de usuario  (valor = enum_rol)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS enumerados;
CREATE TABLE enumerados (
  id      INT AUTO_INCREMENT PRIMARY KEY,
  tipo    INT NOT NULL,
  valor   INT NOT NULL,
  nombre  VARCHAR(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- usuarios: login del sistema
-- ------------------------------------------------------------
DROP TABLE IF EXISTS usuarios;
CREATE TABLE usuarios (
  idusuarios INT AUTO_INCREMENT PRIMARY KEY,
  nombres    VARCHAR(100) NOT NULL,
  apellidos  VARCHAR(100) NOT NULL,
  usuario    VARCHAR(100) NOT NULL,
  clave      VARCHAR(255) NOT NULL,
  enum_rol   INT NOT NULL DEFAULT 1,
  estado     TINYINT NOT NULL DEFAULT 1,
  UNIQUE KEY uq_usuario (usuario)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- emisor: datos del emisor electrónico + credenciales OSE/PSE
--   (Facturalaya lee id=2 ; print_venta lee id=1)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS emisor;
CREATE TABLE emisor (
  id                      INT AUTO_INCREMENT PRIMARY KEY,
  ruc                     VARCHAR(11)  NOT NULL,
  tipo_doc                VARCHAR(2)   DEFAULT '6',
  razon_social            VARCHAR(255) NOT NULL,
  nom_comercial           VARCHAR(255) DEFAULT NULL,
  email                   VARCHAR(150) DEFAULT NULL,
  codigo_ubigeo           VARCHAR(10)  DEFAULT NULL,
  direccion               VARCHAR(255) DEFAULT NULL,
  direccion_departamento  VARCHAR(100) DEFAULT NULL,
  direccion_provincia     VARCHAR(100) DEFAULT NULL,
  direccion_distrito      VARCHAR(100) DEFAULT NULL,
  direccion_codigopais    VARCHAR(5)   DEFAULT 'PE',
  modalidad_envio_sunat   VARCHAR(50)  DEFAULT NULL,
  logo                    VARCHAR(255) DEFAULT NULL,
  token_cliente           VARCHAR(255) DEFAULT NULL,
  ruc_proveedor           VARCHAR(11)  DEFAULT NULL,
  tipo_certificado        VARCHAR(50)  DEFAULT NULL,
  tipo_proceso            VARCHAR(50)  DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- categorias
-- ------------------------------------------------------------
DROP TABLE IF EXISTS categorias;
CREATE TABLE categorias (
  id          INT AUTO_INCREMENT PRIMARY KEY,
  nombre      VARCHAR(150) NOT NULL,
  descripcion VARCHAR(255) DEFAULT NULL,
  estado      VARCHAR(20)  NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- proveedores
-- ------------------------------------------------------------
DROP TABLE IF EXISTS proveedores;
CREATE TABLE proveedores (
  id                   INT AUTO_INCREMENT PRIMARY KEY,
  empresa              VARCHAR(255) NOT NULL,
  nombre_comercial     VARCHAR(255) DEFAULT NULL,
  condicion            VARCHAR(50)  DEFAULT NULL,
  estado_ruc           VARCHAR(50)  DEFAULT NULL,
  tipo                 VARCHAR(50)  DEFAULT NULL,
  inscripcion          VARCHAR(50)  DEFAULT NULL,
  codigo_ubigeo        VARCHAR(10)  DEFAULT NULL,
  sistema_emision      VARCHAR(100) DEFAULT NULL,
  actividad_exterior   VARCHAR(100) DEFAULT NULL,
  sistema_contabilidad VARCHAR(100) DEFAULT NULL,
  emision_electronica  VARCHAR(100) DEFAULT NULL,
  ple                  VARCHAR(100) DEFAULT NULL,
  respuesta_api        TEXT         DEFAULT NULL,
  ruc                  VARCHAR(11)  DEFAULT NULL,
  contacto             VARCHAR(150) DEFAULT NULL,
  telefono             VARCHAR(30)  DEFAULT NULL,
  email                VARCHAR(150) DEFAULT NULL,
  direccion            VARCHAR(255) DEFAULT NULL,
  estado               VARCHAR(20)  NOT NULL DEFAULT 'ACTIVO'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- productos
-- ------------------------------------------------------------
DROP TABLE IF EXISTS productos;
CREATE TABLE productos (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  codigo        VARCHAR(50)  DEFAULT NULL,
  nombre        VARCHAR(255) NOT NULL,
  descripcion   TEXT         DEFAULT NULL,
  id_categoria  INT          DEFAULT NULL,
  id_proveedor  INT          DEFAULT NULL,
  precio_venta  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  costo_compra  DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  stock_actual  INT          NOT NULL DEFAULT 0,
  stock_minimo  INT          NOT NULL DEFAULT 0,
  codigo_barras VARCHAR(50)  DEFAULT NULL,
  imagen        VARCHAR(255) DEFAULT NULL,
  estado        VARCHAR(20)  NOT NULL DEFAULT 'ACTIVO',
  KEY idx_prod_categoria (id_categoria),
  KEY idx_prod_proveedor (id_proveedor)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- clientes
--   (columnas pais/ciudad/departamento/provincia/distrito las usa
--    Venta::getById y el payload de Facturalaya)
-- ------------------------------------------------------------
DROP TABLE IF EXISTS clientes;
CREATE TABLE clientes (
  id               INT AUTO_INCREMENT PRIMARY KEY,
  tipo_documento   VARCHAR(20)  DEFAULT NULL,
  numero_documento VARCHAR(20)  DEFAULT NULL,
  nombres          VARCHAR(150) DEFAULT NULL,
  apellido_paterno VARCHAR(100) DEFAULT NULL,
  apellido_materno VARCHAR(100) DEFAULT NULL,
  sexo             VARCHAR(10)  DEFAULT NULL,
  fecha_nacimiento DATE         DEFAULT NULL,
  razon_social     VARCHAR(255) DEFAULT NULL,
  nombre_comercial VARCHAR(255) DEFAULT NULL,
  condicion        VARCHAR(50)  DEFAULT NULL,
  estado           VARCHAR(50)  DEFAULT NULL,        -- recibe "estado_ruc" del formulario
  codigo_ubigeo    VARCHAR(10)  DEFAULT NULL,
  direccion        VARCHAR(255) DEFAULT NULL,
  telefono         VARCHAR(30)  DEFAULT NULL,
  email            VARCHAR(150) DEFAULT NULL,
  estado_cliente   VARCHAR(20)  NOT NULL DEFAULT 'ACTIVO',
  pais             VARCHAR(5)   DEFAULT 'PE',
  ciudad           VARCHAR(100) DEFAULT NULL,
  departamento     VARCHAR(100) DEFAULT NULL,
  provincia        VARCHAR(100) DEFAULT NULL,
  distrito         VARCHAR(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- ventas: cabecera de comprobantes (boleta/factura y, más adelante,
--   nota de crédito / débito). Incluye campos de respuesta SUNAT.
-- ------------------------------------------------------------
DROP TABLE IF EXISTS ventas;
CREATE TABLE ventas (
  id                 INT AUTO_INCREMENT PRIMARY KEY,
  tipo_comprobante   VARCHAR(20)  NOT NULL,
  serie              VARCHAR(10)  NOT NULL,
  correlativo        INT          NOT NULL,
  id_cliente         INT          DEFAULT NULL,
  id_usuario         INT          DEFAULT NULL,
  fecha_emision      DATETIME     NOT NULL,
  subtotal           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  igv                DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total              DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  metodo_pago        VARCHAR(30)  DEFAULT NULL,
  monto_recibido     DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  cambio             DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  estado             VARCHAR(20)  NOT NULL DEFAULT 'COMPLETADO',
  sunat_ticket       VARCHAR(100) DEFAULT NULL,
  sunat_estado       VARCHAR(30)  DEFAULT 'pendiente',
  sunat_mensaje      TEXT         DEFAULT NULL,
  sunat_cdr          LONGTEXT     DEFAULT NULL,
  xml_file           VARCHAR(255) DEFAULT NULL,
  descripcion_motivo VARCHAR(255) DEFAULT NULL,
  KEY idx_venta_cliente (id_cliente),
  KEY idx_venta_serie (serie, correlativo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- venta_detalle: líneas de cada comprobante
-- ------------------------------------------------------------
DROP TABLE IF EXISTS venta_detalle;
CREATE TABLE venta_detalle (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  id_venta        INT NOT NULL,
  id_producto     INT DEFAULT NULL,
  codigo_producto VARCHAR(50)  DEFAULT NULL,
  unidad_medida   VARCHAR(10)  DEFAULT 'NIU',
  tipo_operacion  VARCHAR(10)  DEFAULT '10',
  cantidad        DECIMAL(10,2) NOT NULL DEFAULT 1.00,
  precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  subtotal        DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  KEY idx_det_venta (id_venta),
  KEY idx_det_producto (id_producto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

SET FOREIGN_KEY_CHECKS = 1;
