# Aplicativo de gestión de envíos (PHP + MySQL)

Aplicación web CRUD para registrar, listar, editar y eliminar envíos. Cada envío tiene destinatario, dirección y descripción.

## Archivos
- `index.php`: interfaz y operaciones CRUD.
- `config.php`: conexión PDO y creación automática de la tabla.
- `README.md`: instrucciones de instalación.

## Requisitos
- PHP 8.0 o superior (con PDO MySQL).
- MySQL/MariaDB.
- La base de datos `webjobs_envios` debe existir y el usuario configurado debe tener permisos para crear tablas, consultar y modificar datos.

## Instalación
1. Sube `index.php`, `config.php` y este README a la carpeta pública de tu hosting.
2. Revisa los datos de conexión en `config.php`.
3. Asegúrate de que la base de datos `webjobs_envios` exista en el panel de hosting.
4. Abre `index.php` desde el navegador. Al conectar, la aplicación crea automáticamente la tabla `envios` si todavía no existe.

## Tabla creada automáticamente

```sql
CREATE TABLE IF NOT EXISTS envios (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  destinatario VARCHAR(150) NOT NULL,
  direccion VARCHAR(255) NOT NULL,
  descripcion TEXT NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## Operaciones
Todas las operaciones se realizan mediante formularios en `index.php`:
- **Crear:** enviar el formulario con `accion=crear`.
- **Leer/listar:** abrir `index.php`.
- **Actualizar:** seleccionar **Editar** y guardar con `accion=actualizar`.
- **Eliminar:** seleccionar **Eliminar** y confirmar con `accion=eliminar`.

La aplicación utiliza consultas preparadas PDO y escapa los datos mostrados en HTML.

## Nota de seguridad
Las credenciales están en `config.php` para facilitar la instalación del ejercicio. En un entorno de producción, usa variables de entorno o un archivo de configuración fuera de la carpeta pública y cambia cualquier contraseña que se haya compartido en mensajes o documentos.
