# Esencia · Perfumería – Gestión de productos en PHP

Aplicación web modular en **PHP puro** (sin frameworks) para gestionar el catálogo de una perfumería online: productos, categorías (Mujer, Hombre, Unisex, Nicho y Estuches) y usuarios.

## Descripción

- CRUD completo de productos (crear, listar, ver detalle, editar, eliminar).
- Búsqueda de productos por marca o modelo.
- Subida de imágenes de producto.
- Autenticación de usuarios con roles **USER** y **ADMIN**: solo el administrador puede crear, editar o eliminar productos.

Arquitectura por capas: **modelos** (datos), **servicios** (lógica y acceso a BD), **configuración** (Singleton) y **vistas** (presentación).

> Estado actual: **Fases 1 a 4 completadas** (estructura, conexión con la BD, CRUD de productos con validaciones, login y permisos). Ver [Registro de desarrollo](#registro-de-desarrollo).

## Tecnologías utilizadas

- **PHP 8.2** + Apache (imagen Docker `php:8.2-apache`)
- **PostgreSQL 12** con **PDO** (`pdo_pgsql`)
- **Bootstrap 5** para la interfaz
- **Composer** con las librerías:
  - `vlucas/phpdotenv` – carga de variables del `.env`
  - `ramsey/uuid` – generación de UUID
  - `phpunit/phpunit` – pruebas automatizadas
- **Docker** y **Docker Compose** (servicios: PHP, PostgreSQL y Adminer)

## Requisitos previos

- Docker Desktop (o Docker Engine + Docker Compose v2)
- Git
- Opcional: PHP 8 y Composer en local (si no, Composer se ejecuta dentro del contenedor)

## Estructura del proyecto

```
tienda-php/
├── database/
│   └── init.sql            # Tablas y datos iniciales (se carga al crear el contenedor)
├── src/                    # Document root de Apache
│   ├── config/
│   │   └── Config.php      # Configuración y conexión PDO (Singleton)
│   ├── models/             # Producto, Categoria, User
│   ├── services/           # ProductosService, CategoriasService, UsersService, SessionService
│   ├── uploads/            # Imágenes subidas de los productos
│   ├── header.php          # Cabecera y menú comunes
│   ├── footer.php          # Pie común
│   ├── index.php           # Listado y buscador de productos
│   ├── login.php           # Inicio de sesión
│   ├── logout.php          # Cierre de sesión
│   ├── create.php          # Alta de producto
│   ├── update.php          # Edición de producto
│   ├── details.php         # Ficha de producto
│   └── delete.php          # Eliminación con confirmación
├── tests/                  # Pruebas PHPUnit
├── .env                    # Variables de entorno (BD, puertos)
├── composer.json
├── docker-compose.yml
├── Dockerfile
└── phpunit.xml
```

## Instalación

1. Clonar el repositorio:
   ```bash
   git clone https://github.com/pabmoralc/tienda-php.git
   cd tienda-php
   ```
2. Levantar los contenedores (la base de datos se crea e importa sola desde `database/init.sql`):
   ```bash
   docker compose up -d --build
   ```
3. Instalar las dependencias de Composer dentro del contenedor:
   ```bash
   docker exec -it php_app composer install
   ```
   (Con PHP y Composer en local, también vale `composer install` desde la raíz.)
4. Abrir la aplicación en <http://localhost:8080>.

> `init.sql` solo se ejecuta la primera vez, con el volumen de datos vacío. Para reiniciar la base de datos desde cero: `docker compose down -v` y de nuevo `docker compose up -d`.

## Uso básico

- **Aplicación:** <http://localhost:8080>
- **Adminer** (gestor de la BD): <http://localhost:8082> – Sistema *PostgreSQL*, servidor `postgres-db`, usuario `admin`, contraseña `adminPassword123`, base de datos `tienda`.

Usuarios de prueba:

| Usuario | Contraseña | Roles        |
|---------|------------|--------------|
| admin   | Admin1     | USER, ADMIN  |
| user    | User1234   | USER         |
| test    | test1234   | USER         |

Ejecutar las pruebas:

```bash
docker exec -it php_app vendor/bin/phpunit
```

## Registro de desarrollo

### Fase 1 – Modelos y servicios base (02/11 – 03/11)

- Estructura de carpetas y ficheros de entorno: `Dockerfile`, `docker-compose.yml`, `.env`, `composer.json`, `.gitignore`.
- `database/init.sql` revisado: tablas ordenadas según sus dependencias, secuencias sincronizadas con `setval`, roles duplicados eliminados y contraseñas de prueba generadas con `password_hash()`.
- `Config.php` como **Singleton**: carga el `.env` con Dotenv y crea una única conexión PDO con errores en modo excepción.
- Modelos `Producto`, `Categoria` y `User` con métodos mágicos `__get` / `__set`.
- Servicios:
  - `CategoriasService`: `findAll()` y `findByName()`.
  - `UsersService`: `findUserByUsername()` (con roles) y `authenticate()` con `password_verify()`.
  - `SessionService`: base del Singleton (estado de sesión, usuario «Invitado»).
  - `ProductosService`: clase preparada; sus métodos se implementan en las fases 2 y 3.
- Pruebas PHPUnit en `tests/Fase1Test.php`.

### Fase 2 – CRUD de productos: primera parte (05/11 – 06/11)

- Temática: perfumería. `init.sql` con 5 categorías y 11 perfumes de ejemplo.
- `ProductosService`: `findAllWithCategoryName()` (JOIN con categorías y búsqueda por marca o modelo con `ILIKE`), `findById()` y `save()` (UUID con Ramsey, fechas con `NOW()` e id con `RETURNING`).
- Vistas `index.php` (listado y buscador) y `create.php` (alta con validación).
- `header.php` y `footer.php` comunes con Bootstrap 5.
- Mensajes flash en `SessionService` (`setMensaje()` / `getMensaje()`) para avisar del resultado tras redirigir.

### Fase 3 – CRUD de productos: segunda parte (09/11 – 10/11)

- `ProductosService`: `update()` y `deleteById()`.
- Vistas `update.php` (formulario prerrelleno), `details.php` (ficha) y `delete.php` (confirmación por POST y borrado de la imagen subida).
- Validaciones en servidor: campos obligatorios, longitud máxima, precio > 0, stock entero ≥ 0 y categoría existente. Errores junto a cada campo.
- Seguridad: consultas preparadas, `htmlspecialchars()` en toda la salida (XSS) y validación del `id` con `filter_input()`.
- Pruebas PHPUnit del CRUD en `tests/ProductosServiceTest.php`.

### Fase 4 – Login y control de sesiones (11/11 – 12/11)

- `SessionService` completo: `login()` (con `session_regenerate_id` contra la fijación de sesión), `logout()`, caducidad tras 1 hora de inactividad, contador de visitas, fecha del último login y `requireAdmin()`.
- `login.php`: formulario, validación de campos vacíos y autenticación con `UsersService::authenticate()` (`password_verify`).
- `logout.php`: borra los datos de la sesión y vuelve al listado con un mensaje.
- Permisos: crear, editar y eliminar solo para el rol ADMIN (comprobado en el servidor, no solo ocultando botones). Invitados y usuarios normales pueden ver el listado y los detalles.
- `index.php` muestra las visitas y el último login del usuario conectado; la cabecera muestra su nombre, una etiqueta ADMIN y el botón Login/Logout.

## Autor

- **Pablo** – 2º DAW (semipresencial), IES Juan de Garay
- GitHub: <https://github.com/pabmoralc>

## Licencia

Uso educativo.
