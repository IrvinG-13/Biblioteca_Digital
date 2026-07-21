<h1 align="center">📚 Biblioteca Digital</h1>

<p align="center"><b>Sistema web desarrollado en PHP 8.3, MySQL y arquitectura MVC.</b></p>

<p align="center">

![PHP](https://img.shields.io/badge/PHP-8.3-777BB4?style=for-the-badge&logo=php&logoColor=white)
![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)
![MVC](https://img.shields.io/badge/Architecture-MVC-blue?style=for-the-badge)
![Git](https://img.shields.io/badge/Git-GitHub-black?style=for-the-badge&logo=github)

</p>

> [!NOTE]
> Proyecto desarrollado con fines académicos para la Universidad Tecnológica de Panamá.

## 📑 Índice
- Información General
- Requisitos
- Instalación
- Roles
- Arquitectura
- Seguridad
- Manual de Usuario
- Estructura del Proyecto
- Repositorio
- Contacto

---

# 📚 Sistema de Biblioteca Digital

Sistema web desarrollado en **PHP 8.x**, **MySQL** y arquitectura **MVC**, orientado a la administración, consulta, solicitud, reserva, compra y acceso a recursos bibliográficos digitales.

La plataforma permite gestionar usuarios, estudiantes, profesores, carreras, materias, categorías, libros, solicitudes, reservas, facturas y estadísticas. También incorpora controles de seguridad como protección CSRF, bloqueo automático de cuentas, hash de contraseñas, auditoría y firmas digitales.

---

## 1. Información General y Evidencia Práctica

### 1.1. Nombre del Proyecto

**Sistema de Biblioteca Digital**

El Sistema de Biblioteca Digital centraliza la gestión de recursos bibliográficos de una institución educativa. Los administradores pueden gestionar usuarios, estudiantes, profesores, carreras, materias, categorías, libros, solicitudes, reservas y facturas.

Los estudiantes y profesores pueden consultar el catálogo, solicitar o reservar libros, acceder a documentos autorizados y revisar sus operaciones.

> Las cuentas de estudiantes y profesores son creadas por el administrador. El sistema no utiliza registro público voluntario.

### 1.2. Integrantes del Equipo

| Integrante | Cédula | Rol dentro del desarrollo |
|---|---|---|
| Elisa Oses | 8-1033-934 | Desarrollo de los módulos de categorías y libros, procesamiento de imágenes, thumbnails y PDF, reportes de reservas, catálogo para estudiantes, reservas y solicitudes de libros. |
| Irvin González | 8-1019-2150 | Desarrollo del inicio de sesión y seguridad del sistema, conexión a la base de datos, control de errores, aplicación de OWASP, DRY y SOLID, servicios criptográficos y facturación.|
| Kevyn Reyes | 8-1024-254 | Desarrollo del buscador de libros, módulo de estadísticas, página institucional pública y estilos CSS generales del sistema. |
| Aaron López | 20-53-8298 | Desarrollo de los módulos de usuarios, roles y permisos, CRUD de estudiantes, carreras y profesores.|

### 1.3. Fecha del Sistema y Versión

- **Fecha:** Julio de 2026
- **Versión actual:** `v1.0.0`
- **Estado:** Versión académica funcional
- **Arquitectura:** Modelo-Vista-Controlador (MVC)
- **Lenguaje:** PHP 8.3.28
- **Base de datos:** MySQL

### 1.4. Demostración en Video

**Enlace del video:** Pendiente de agregar


---

## 2. Requisitos de Infraestructura

### 2.1. Entorno de Ejecución

| Componente | Requisito |
|---|---|
| Lenguaje | PHP 8.3.28 |
| Base de datos | MySQL o MariaDB |
| Servidor web | Apache |
| Servidor local recomendado | WAMP, XAMPP o Laragon |
| Administrador de base de datos | phpMyAdmin o MySQL Workbench |
| Navegador | Google Chrome, Microsoft Edge o Mozilla Firefox |
| Control de versiones | Git |

Extensiones recomendadas de PHP:

- `mysqli`
- `pdo_mysql`
- `gd`
- `fileinfo`
- `mbstring`
- `openssl`
- `session`

La extensión `gd` es necesaria para procesar las imágenes de los libros y generar miniaturas.

### 2.2. Guía de Despliegue Rápido

#### Clonar el repositorio

Para WAMP:

```bash
cd C:\wamp64\www
git clone https://github.com/IrvinG-13/Biblioteca_Digital.git
cd Biblioteca_Digital
```

Para XAMPP:

```bash
cd C:\xampp\htdocs
git clone https://github.com/IrvinG-13/Biblioteca_Digital.git
cd Biblioteca_Digital
```

#### Iniciar servicios

Iniciar desde WAMP, XAMPP o Laragon:

- Apache
- MySQL

#### Crear la base de datos

Abrir:

```text
http://localhost/phpmyadmin
```

Crear una base de datos llamada:

```text
biblioteca_digital
```

Se recomienda usar la codificación:

```text
utf8mb4_general_ci
```

#### Importar los scripts SQL

Los scripts se encuentran en:

```text\
Biblioteca_Digital/
    └── Database/
        └── biblioteca_digital.sql
        └── Facturacion.sql

```

Primero se debe importar:

```text
Biblioteca_Digital/
    └── Database/
        └── biblioteca_digital.sql
```

Después, si es necesario, se importa:

```text
Biblioteca_Digital
    └── Database/
           └── Facturacion.sql
```

Enlace al script principal:

```text
https://github.com/IrvinG-13/Biblioteca_Digital/blob/main/Database/biblioteca_digital.sql
```

Enlace al script de facturación:

```text
https://github.com/IrvinG-13/Biblioteca_Digital/blob/main/Database/Facturacion.sql
```

#### Configurar la conexión

La conexión con la base de datos se administra en:

```text
Biblioteca_Digital/
    └── app/
          └── Core/
               └── Database.php
```

Configuración típica para WAMP o XAMPP:

```php
$host = 'localhost';
$database = 'biblioteca_digital';
$username = 'root';
$password = '';
```

Los valores deben modificarse si el entorno local utiliza credenciales diferentes.

#### Verificar carpetas de carga

El sistema utiliza:

```text
Biblioteca_Digital/
    └── uploads/
        ├── libros/
        ├── pdfs/
        └── thumnails/
```

Estas carpetas deben permitir escritura para que PHP pueda guardar imágenes, documentos PDF y miniaturas.

#### Ejecutar el sistema

Abrir:

```text
http://localhost/Biblioteca_Digital/public/
```

Inicio de sesión:

```text
http://localhost/Biblioteca_Digital/public/login.php
```

---

## 3. Matriz de Roles y Credenciales de Prueba

| Rol | Usuario | Contraseña | Funciones principales |
|---|---|---|---|
| Administrador | admin | root2514 | Gestionar usuarios, estudiantes, profesores, carreras, materias, categorías, libros, solicitudes, reservas, facturas y estadísticas |
| Estudiante | kevynreyes | 123456789 | Consultar catálogo, solicitar, reservar, comprar y acceder a libros autorizados |
| Profesor | irvin | 123456789 | Consultar catálogo, solicitar, reservar, comprar y acceder a materiales digitales |

> Las credenciales anteriores son únicamente para pruebas académicas.

> [!IMPORTANT]
> Estas al iniciar sesion les pedirá cambiar la contraseña.


---

## 4. Directrices Técnicas y Reglas del Backend

### 4.1. Arquitectura MVC

El proyecto está organizado de la siguiente forma:

```text
Biblioteca_Digital
├── app/
|    ├── Controllers/
|    ├── Core/
|    ├── Interfaces/
|    └── Models/
├── Database/
├── public/
└── uploads/
```

#### Controladores

Los controladores reciben las solicitudes, validan los datos y coordinan la comunicación entre vistas y modelos.

```text
Biblioteca_Digital
    └── app/
        └── Controllers/
            ├── AuthController.php
            ├── CarreraController.php
            ├── CategoriaController.php
            ├── EstadisticaController.php
            ├── EstudianteController.php
            ├── FacturaAdminController.php
            ├── FacturaController.php
            ├── LibroController.php
            ├── MateriaController.php
            ├── PerfilController.php
            ├── ProfesorController.php
            ├── ReservaController.php
            ├── SolicitudController.php
            └── UsuarioController.php
```

#### Modelos

Los modelos administran las consultas y operaciones con la base de datos.

```text
Biblioteca_Digital
    └── app/
        └── Models/
            ├── AuthModel.php
            ├── CarreraModel.php
            ├── CategoriaModel.php
            ├── EstadisticaModel.php
            ├── EstudianteModel.php
            ├── FacturaAdminModel.php
            ├── FacturaModel.php
            ├── LibroModel.php
            ├── MateriaModel.php
            ├── ProfesorModel.php
            ├── ReservaModel.php
            ├── SolicitudModel.php
            └── UsuarioModel.php
```

#### Vistas

Las vistas y puntos de entrada se encuentran en:

```text
Bibioteca_Digital
    └── app/
          └── public/
```

Entre las pantallas principales están:

```text
Biblioteca_Digital
    └── public/
        ├── login.php
        ├── dashboard.php
        ├── menu.php
        ├── menu_estudiante.php
        ├── usuarios.php
        ├── estudiantes.php
        ├── profesores.php
        ├── carreras.php
        ├── materias.php
        ├── categorias.php
        ├── libros.php
        ├── catalogo.php
        ├── mis_solicitudes.php
        ├── mis_reservas.php
        ├── solicitudes_admin.php
        ├── facturas.php
        ├── mis_facturas.php
        ├── estadisticas.php
        └── perfil.php
```

### 4.2. Control de Acceso Seguro

#### Validación de contraseñas

Las contraseñas deben tener entre **8 y 12 caracteres**.

Archivos relacionados:

```text
Bibioteca_Digital
    └── app/
        ├── Core/
        |    ├── Validator.php
        |    └── PasswordHasher.php
        ├── Controllers/
        |    ├── UsuarioController.php
        |    ├── EstudianteController.php
        |    └── ProfesorController.php
        └── Model/
            └── UsuarioModel.php
```

Las contraseñas se almacenan mediante hash seguro y se verifican durante el inicio de sesión.

#### Bloqueo al tercer intento fallido

El proceso de autenticación se administra mediante:

```text
Bibioteca_Digital
    └── app/
        ├── public/
        |    └── procesar_login.php
        ├── Controllers/
        |    └── AuthController.php
        └── Model/
            └── AuthModel.php
```

Después del tercer intento fallido, la cuenta se bloquea automáticamente. El administrador puede modificar el estado desde:

```text
Bibioteca_Digital
    └── app/
        ├── public/
        |    ├── usuario_estado.php
        ├── Controllers/
        |    ├── UsuarioController.php
        └── Model/
            ├── UsuarioModel.php
```

#### Protección de sesión

La protección de páginas privadas se centraliza en:

```text
Bibioteca_Digital
    ├── app/
    │   └── Core/
    │       ├── NoCache.php
    │       └── SesionGuard.php
    └── public/
            └── logout.php
```

#### Auditoría

Las operaciones críticas se registran en la tabla de logs para conservar trazabilidad.

Entre las acciones auditadas se encuentran:

- Inicios de sesión exitosos y fallidos.
- Bloqueo de cuentas.
- Creación y modificación de usuarios.
- Cambios de estado.
- Creación y modificación de libros.
- Solicitudes y reservas.
- Compras y facturas.

### 4.3. Mitigación OWASP y DRY

#### Protección CSRF

La protección contra solicitudes externas se implementa en:

```text
app/Core/Csrf.php
```

Los formularios generan y validan un token CSRF asociado a la sesión. Esto evita que las operaciones sensibles se ejecuten desde herramientas externas, como Postman, sin una sesión y un token válido.

#### Consultas preparadas

Los modelos utilizan consultas preparadas para evitar inyección SQL.

#### Sanitización

La limpieza de datos se centraliza en:

```text
app/Core/Sanitizer.php
```

#### Validación

Las reglas reutilizables se encuentran en:

```text
app/Core/Validator.php
```

#### Prevención de XSS

Los datos mostrados en las vistas se escapan con funciones como:

```php
htmlspecialchars($valor, ENT_QUOTES, 'UTF-8');
```

#### Principio DRY

El proyecto evita duplicar lógica mediante componentes reutilizables:

```text
Bibioteca_Digital
    └── app/
        └── Core/
            ├── Csrf.php
            ├── Database.php
            ├── ExcelLibro.php
            ├── ExcelReserva.php
            ├── FirmaDigital.php
            ├── ImagenLibro.php
            ├── NoCache.php
            ├── PasswordHasher.php
            ├── PdfLibro.php
            ├── Sanitizer.php
            ├── SesionGuard.php
            └── Validator.php
```

### 4.4. Sello de Integridad

La firma digital se administra mediante:

```text
Bibioteca_Digital
    └── app/
        └── Core/
            └── FirmaDigital.php
```

El backend reúne los campos críticos del registro, genera una firma criptográfica y la almacena junto con los datos.

Ejemplo conceptual:

```php
$datos = $usuarioId . '|' . $libroId . '|' . $estado . '|' . $fecha;

$firma = hash_hmac(
    'sha256',
    $datos,
    $claveSecreta
);
```

Al consultar el registro, el sistema vuelve a calcular la firma y la compara con la almacenada. Si los datos fueron modificados directamente en la base de datos, las firmas no coincidirán y el sistema detectará la alteración.

---

## 5. Manual de Usuario Operativo

### Video del proyecto

```text
https://youtu.be/kgEBriK9DTs
```

### 5.1. Guía de Usuario

#### Iniciar sesión
## 5.1. Guía rápida de uso

### Página pública

1. Abrir el sistema desde el navegador ingresando a:

   `http://localhost/biblioteca_digital/public/`

2. En la página pública se presenta información sobre ReadPoint, la importancia de las bibliotecas digitales, las tecnologías utilizadas y los datos de contacto.
3. Presionar el botón **Iniciar sesión** para acceder al sistema.

---

### Iniciar sesión

1. Introducir el nombre de usuario y la contraseña asignados por el administrador.
2. Presionar el botón **Iniciar sesión**.
3. El sistema valida las credenciales, el rol, el estado de la cuenta y los intentos fallidos.
4. El administrador será dirigido al panel administrativo.
5. Los estudiantes y profesores serán dirigidos al catálogo de libros.

Después de varios intentos incorrectos, la cuenta puede quedar bloqueada y deberá ser reactivada por un administrador.
<img width="1891" height="717" alt="image" src="https://github.com/user-attachments/assets/3fdefa3b-0818-436f-972a-8536d27e3e54" />


---

### Panel del administrador

Desde el panel principal, el administrador puede:

- Gestionar usuarios, estudiantes y profesores.
- Administrar carreras, materias y categorías.
- Registrar, editar y consultar libros.
- Revisar solicitudes de libros.
- Consultar reservas y facturas.
- Generar reportes y estadísticas.
<img width="1912" height="856" alt="image" src="https://github.com/user-attachments/assets/c9f60ce6-6e4c-40b5-b0d1-7c05b8e75ec5" />

---

### Gestión de libros

1. Entrar al módulo **Libros**.
2. Presionar **Nuevo libro**.
3. Completar el título, autor, descripción, categoría y costo.
4. Indicar si el libro es propio o pertenece a una institución externa.
5. Definir si el acceso será gratuito o de pago.
6. Registrar las unidades disponibles cuando corresponda.
7. Subir la imagen de portada y el archivo PDF.
8. Guardar el libro.

Los libros externos deben incluir la institución de origen y su enlace. Los libros de pago deben tener precio y duración de acceso.
<img width="1892" height="825" alt="image" src="https://github.com/user-attachments/assets/98ce89c6-b377-4a5f-b548-3f6413913dbd" />

<img width="817" height="1052" alt="image" src="https://github.com/user-attachments/assets/e0745609-dcaf-46f7-af36-6ffc3a13a51b" />

---

### Catálogo de libros

Después de iniciar sesión, los estudiantes y profesores pueden:

- Buscar libros por título, autor o categoría.
- Consultar el detalle de un libro.
- Leer libros gratuitos.
- Comprar acceso a libros de pago.
- Visitar libros pertenecientes a bibliotecas externas.
<img width="1541" height="841" alt="image" src="https://github.com/user-attachments/assets/cec8f3d5-15d3-42fd-a7f4-fc0fdcd4ad23" />

---

### Mis libros

La sección **Mis libros** contiene los libros gratuitos abiertos y los libros de pago adquiridos.

1. Entrar a **Mis libros**.
2. Localizar el libro.
3. Presionar **Continuar leyendo**.
4. Cuando un acceso de pago haya vencido, seleccionar **Renovar acceso**.
<img width="1562" height="857" alt="image" src="https://github.com/user-attachments/assets/dc9956af-b0df-40a9-9b65-cd9ac4045915" />


---

### Solicitar un libro

1. Entrar a la sección **Mis solicitudes**.
2. Presionar **Nueva solicitud**.
3. Completar la información del libro.
4. Indicar la materia, área o motivo de interés.
5. Enviar la solicitud.
6. Consultar posteriormente su estado.
<img width="1567" height="851" alt="image" src="https://github.com/user-attachments/assets/40815825-7734-40a0-a42e-1ebc5cdca889" />


---

### Comprar acceso a un libro

1. Abrir el detalle de un libro de pago.
2. Presionar **Obtener acceso**.
3. Seleccionar el método de pago.
4. Introducir la referencia de pago cuando sea necesaria.
5. Presionar **Confirmar compra**.
6. El sistema genera la factura y agrega el libro a **Mis libros**.

<img width="1566" height="837" alt="image" src="https://github.com/user-attachments/assets/e7476e99-8182-40be-9101-1f647c5234e8" />

---

### Reportes y estadísticas

El administrador puede consultar las reservas, aplicar filtros por fecha y exportar los resultados a Excel.

El módulo de estadísticas permite visualizar:

- Total de reservas.
- Reservas realizadas por estudiantes.
- Reservas realizadas por profesores.
- Libros más utilizados durante un periodo.

<img width="471" height="287" alt="image" src="https://github.com/user-attachments/assets/ff29ee3e-2309-457b-83a6-daad713de08f" />

---

### Guia de usuario completa
https://utpac-my.sharepoint.com/:f:/g/personal/aaron_lopez2_utp_ac_pa/IgC-Pto8_SuiRaCmlXxmRpBhAeWkTs1vUH3eFKtJ_iFc6iQ?e=JSchjM

#### Gestión de libros

El módulo permite registrar:

- Título.
- Autor.
- Descripción.
- Categoría.
- Costo de adquisición.
- Origen propio o externo.
- Institución de origen.
- URL externa.
- Unidades totales y disponibles.
- Imagen.
- PDF.
- Tipo de acceso.
- Precio.
- Días de acceso.

Archivos principales:

```text
public/libros.php
public/libro_form.php
public/libro_procesar.php
public/libro_eliminar.php
public/libro_detalle.php
public/libro_exportar.php
app/Controllers/LibroController.php
app/Models/LibroModel.php
```

El procesamiento de imágenes se realiza en:

```text
app/Core/ImagenLibro.php
```

Las imágenes se almacenan en:

```text
uploads/libros/
```

Las miniaturas se guardan en:

```text
uploads/thumbnails/
```

Los PDF se procesan mediante:

```text
app/Core/PdfLibro.php
```

Y se guardan en:

```text
uploads/pdfs/
```

#### Catálogo y acceso a libros

El catálogo se encuentra en:

```text
public/catalogo.php
```

El detalle se muestra en:

```text
public/libro_detalle.php
```

El acceso a los documentos se realiza mediante:

```text
public/abrir_libro.php
public/leer_libro.php
```

#### Solicitudes

Archivos principales:

```text
public/solicitar_libro.php
public/solicitud_procesar.php
public/mis_solicitudes.php
public/solicitudes_admin.php
public/solicitud_estado_procesar.php
```

Estados:

- Pendiente.
- Aprobada.
- Rechazada.

#### Reservas

Archivos principales:

```text
public/mis_reservas.php
public/reporte_reservas.php
public/reserva_exportar.php
app/Controllers/ReservaController.php
app/Models/ReservaModel.php
```

#### Facturación

Archivos principales:

```text
public/comprar_libro.php
public/facturas.php
public/mis_facturas.php
public/factura_detalle.php
public/factura_procesar.php
public/factura_admin_detalle.php
app/Controllers/FacturaController.php
app/Controllers/FacturaAdminController.php
app/Models/FacturaModel.php
app/Models/FacturaAdminModel.php
```

#### Estadísticas

Archivos principales:

```text
public/estadisticas.php
app/Controllers/EstadisticaController.php
app/Models/EstadisticaModel.php
```

---

## 6. Estructura Principal del Proyecto

```text
Biblioteca_Digital/
│
├── app/
│   ├── Controllers/
│   ├── Core/
│   ├── Interfaces/
│   └── Models/
│
├── Database/
│   ├── biblioteca_digital.sql
│   └── Facturacion.sql
│
├── public/
│   ├── assets/
│   ├── login.php
│   ├── dashboard.php
│   ├── catalogo.php
│   ├── libros.php
│   ├── usuarios.php
│   ├── estudiantes.php
│   ├── profesores.php
│   ├── carreras.php
│   ├── materias.php
│   ├── categorias.php
│   ├── solicitudes_admin.php
│   ├── facturas.php
│   └── estadisticas.php
│
├── uploads/
│   ├── libros/
│   ├── pdfs/
│   └── thumbnails/
│
└── README.md
```

---

## 7. Repositorio

**Repositorio:**

```text
https://github.com/IrvinG-13/Biblioteca_Digital
```

**Base de datos:**

```text
https://github.com/IrvinG-13/Biblioteca_Digital/blob/main/Database/biblioteca_digital.sql
```

**Facturación:**

```text
https://github.com/IrvinG-13/Biblioteca_Digital/blob/main/Database/Facturacion.sql
```

---

## 8. Uso Académico

Este proyecto fue desarrollado con fines académicos para la carrera de **Desarrollo y Gestión de Software** de la **Universidad Tecnológica de Panamá**.

---

## 9. Contacto

- **Proyecto:** ReadPoint
- **Institución:** Universidad Tecnológica de Panamá
- **Carrera:** Desarrollo y Gestión de Software
- **Equipo:** Elisa Oses, Irvin González, Kevyn Reyes y Aaron López
- **Correo:** elisa.oses@utp.ac.pa, kevyn.reyes@utp.ac.pa, irvin.gonzalez@utp.ac.pa, aaron.lopez2@utp.ac.pa
