# Sistema de Facturación Electrónica con PHP (SUNAT)

Sistema de facturación electrónica desarrollado en **PHP + MySQL** que emite comprobantes
electrónicos y se comunica con los Web Services de SUNAT a través de un proveedor OSE/PSE
(facturalahoy.com). Proyecto del curso–taller de **CETI**.

## ✨ Funcionalidades

- **Boleta** (03) y **Factura** (01) electrónicas
- **Nota de Crédito** (07) y **Nota de Débito** (08) con su motivo y documento de referencia
- **Comunicación de baja** (anulación) de facturas y notas
- **Resumen diario** de boletas
- **Consulta de estado** del comprobante por ticket
- **Código QR** en la representación impresa (ticket / A4)
- Gestión de clientes, productos, categorías, proveedores y usuarios
- Dashboard con indicadores

## 🛠️ Tecnologías

- PHP 8.x · MySQL / MariaDB
- Tailwind CSS · jQuery · Toastr
- Comunicación con SUNAT vía API OSE/PSE (JSON → XML UBL firmado)

## 🚀 Puesta en marcha

1. **Base de datos:** crea la base e importa el esquema y los datos de ejemplo:
   ```sql
   -- en MySQL/MariaDB
   SOURCE sql/schema.sql;
   SOURCE sql/seed.sql;
   ```
2. **Conexión:** revisa `admin/classes/Database.php` (host, usuario, contraseña, base y puerto).
   Por defecto: `localhost`, `root`, sin contraseña, base `facturacion_electronica`, puerto `3306`.
3. **Servidor:** apunta tu servidor (Apache/Laragon/XAMPP) a la carpeta del proyecto, o usa el
   servidor integrado de PHP:
   ```bash
   php -S localhost:8000
   ```
4. **Acceso:** abre `http://localhost:8000` e ingresa con:
   - Usuario: `admin`
   - Contraseña: `admin123`

## ⚙️ Modos de emisión

El sistema puede emitir de **dos formas**, configurables en `admin/classes/config_emision.php`:

- **`tercero`** → usa un proveedor OSE/PSE (clase `Facturalaya`, API de facturalahoy.com).
- **`certificado`** → emite **directo a SUNAT con tu propio certificado** (clase `EmisorSunat`,
  vía la librería [Greenter](https://github.com/thegreenter/greenter)). Genera el XML UBL 2.1,
  lo firma digitalmente y lo envía a los Web Services de SUNAT.

`EmisorFactory::crear()` devuelve el emisor correcto según ese archivo, sin tocar el resto del código.

### Usar el modo certificado
1. Instala las dependencias de PHP:
   ```bash
   composer install
   ```
2. Genera un certificado de prueba (para el entorno **beta** de SUNAT):
   ```bash
   php admin/certificados/generar_certificado_prueba.php
   ```
3. En `config_emision.php` deja `modo => 'certificado'` y `entorno => 'beta'`.
   Las credenciales de prueba de SUNAT son RUC `20000000001`, usuario `MODDATOS`, clave `MODDATOS`.
4. En **producción**: cambia `entorno => 'produccion'`, pon tu **RUC real**, tus credenciales
   **SOL** y la ruta de **tu certificado digital real** (de SUNAT o una entidad certificadora).

> **Notas API tercero:** los endpoints para **notas, baja, resumen y consulta** en
> `admin/classes/Facturalaya.php` están marcados con `// AJUSTAR` (confirmar con el proveedor).
> Si un endpoint no responde, el comprobante se guarda como `pendiente`.

## 📁 Estructura

```
admin/classes/     Lógica de negocio (Venta, NotaComprobante, ResumenDiario, Facturalaya, ...)
pages/             Vistas (ventas, notas, resúmenes, impresión) + JS
sql/               schema.sql (estructura) y seed.sql (datos de ejemplo)
docs/              Documentación de diseño
```

## 📄 Licencia

Material educativo del curso de Facturación Electrónica con PHP — CETI.
