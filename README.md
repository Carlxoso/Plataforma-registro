# Plataforma de Registro

## Descripción

La **Plataforma de Registro** es un sistema web desarrollado como proyecto universitario, orientado a la gestión y administración de usuarios mediante una plataforma centralizada.

El sistema permite realizar procesos de registro, inicio de sesión, gestión de perfiles y administración de usuarios. También incorpora funcionalidades para actualizar información personal, gestionar fotografías de perfil y generar documentos en formato PDF.

El proyecto fue desarrollado aplicando tecnologías web del lado del servidor y del cliente, integrando PHP, JavaScript, CSS y una base de datos para el almacenamiento y gestión de la información.

## Tecnologías utilizadas

El proyecto fue desarrollado utilizando las siguientes tecnologías:

* **PHP**: utilizado para la programación del lado del servidor, procesamiento de formularios, autenticación y gestión de las diferentes funcionalidades del sistema.
* **MySQL**: utilizado para el almacenamiento y administración de la información de los usuarios.
* **JavaScript**: utilizado para implementar funcionalidades e interacciones dinámicas en la plataforma.
* **CSS3**: utilizado para el diseño y presentación de la interfaz.
* **HTML5**: utilizado para la estructura de las diferentes páginas del sistema.
* **FPDF**: biblioteca utilizada para la generación de documentos PDF.
* **Font Awesome**: utilizado para incorporar iconos en la interfaz.
* **Git y GitHub**: utilizados para el control de versiones y almacenamiento del código fuente.

## Funcionalidades principales

La plataforma cuenta con diferentes funcionalidades relacionadas con la gestión de usuarios:

### Registro de usuarios

Permite crear nuevas cuentas dentro del sistema mediante un formulario de registro.

### Inicio y cierre de sesión

El sistema incorpora autenticación de usuarios mediante las funcionalidades de inicio y cierre de sesión.

### Gestión de perfiles

Los usuarios pueden consultar y actualizar información relacionada con su perfil.

Entre las funcionalidades relacionadas con los perfiles se encuentran:

* Consulta de información del usuario.
* Actualización de datos personales.
* Cambio de contraseña.
* Subida y gestión de fotografías de perfil.

### Administración

El proyecto incluye un apartado destinado a la administración de usuarios y gestión de información dentro de la plataforma.

### Generación de PDF

La plataforma incorpora la biblioteca FPDF para generar documentos en formato PDF a partir de la información gestionada por el sistema.

## Estructura del proyecto

```text
Plataforma-registro/
│
├── src/
│   ├── assets/
│   │   └── img/                 # Imágenes y recursos visuales
│   │
│   ├── css/                     # Hojas de estilos
│   │
│   ├── fontawesome/             # Recursos de Font Awesome
│   │
│   ├── fpdf/                    # Biblioteca FPDF
│   │
│   ├── js/                      # Archivos JavaScript
│   │
│   ├── uploads/                 # Archivos e imágenes subidas
│   │
│   ├── index.php                # Página principal
│   ├── login.php                # Inicio de sesión
│   ├── logout.php               # Cierre de sesión
│   ├── registro.php             # Registro de usuarios
│   ├── conexion.php             # Conexión con la base de datos
│   ├── usuario.php              # Gestión del usuario
│   ├── administrador.php        # Panel de administración
│   ├── adminperfil.php          # Perfil del administrador
│   ├── actualizar_perfil.php    # Actualización del perfil
│   ├── subir_foto.php           # Gestión de fotografías
│   ├── usufoto.php              # Gestión de uso de fotografías
│   ├── descargar_pdf.php        # Generación y descarga de PDF
│   └── porcesar_cambio_password.php
│                                # Procesamiento del cambio de contraseña
│
└── README.md
```

## Arquitectura del proyecto

La aplicación utiliza una arquitectura basada en tecnologías web tradicionales, donde PHP se encarga principalmente del procesamiento del lado del servidor y de la comunicación con la base de datos.

La interfaz utiliza HTML y CSS para la presentación de la información, mientras que JavaScript permite implementar funcionalidades dinámicas en el cliente.

La información de los usuarios se gestiona mediante una base de datos, cuya conexión es establecida desde el archivo `conexion.php`.

## Seguridad y gestión de usuarios

El sistema incorpora funcionalidades relacionadas con la autenticación y administración de cuentas, incluyendo:

* Registro de nuevos usuarios.
* Inicio de sesión.
* Cierre de sesión.
* Gestión de perfiles.
* Cambio de contraseña.
* Administración de usuarios.
* Gestión de fotografías de perfil.

Estas funcionalidades permiten centralizar la información de los usuarios y controlar el acceso a las diferentes áreas de la plataforma.

## Generación de documentos

Una de las funcionalidades implementadas es la generación de documentos PDF mediante la biblioteca **FPDF**.

Esta funcionalidad permite transformar información almacenada en el sistema en documentos que pueden ser generados y descargados por el usuario.

## Objetivo académico

El proyecto fue desarrollado como parte de la formación universitaria con el objetivo de aplicar conocimientos relacionados con el desarrollo web, bases de datos, programación del lado del servidor y diseño de interfaces.

Su desarrollo permitió trabajar con diferentes componentes de una aplicación web completa, desde la creación de interfaces hasta el procesamiento de información, autenticación de usuarios y conexión con una base de datos.

## Estado del proyecto

**Proyecto académico.**

El proyecto representa una implementación desarrollada durante la formación universitaria y puede utilizarse como base para incorporar nuevas funcionalidades, mejoras de seguridad, optimizaciones de código y mejoras en la experiencia de usuario.

## Autor

**Carlxoso**

Proyecto desarrollado con fines académicos y de aprendizaje en desarrollo de aplicaciones web.
