# Documentación Técnica — BovWeight CR

> Sistema de Pesaje de Ganado Bovino  
> Versión: 0.0.1  
> Fecha: Junio 2026

---

## Tabla de Contenidos

1. [Descripción General del Sistema](#1-descripción-general-del-sistema)
2. [Arquitectura del Sistema](#2-arquitectura-del-sistema)
3. [Stack Tecnológico](#3-stack-tecnológico)
4. [Modelo de Base de Datos](#4-modelo-de-base-de-datos)
5. [Documentación de la API REST](#5-documentación-de-la-api-rest)
6. [Módulo de Machine Learning (BovweightML)](#6-módulo-de-machine-learning-bovweightml)
7. [Documentación de Componentes y Módulos](#7-documentación-de-componentes-y-módulos)
8. [Patrones de Diseño Aplicados](#8-patrones-de-diseño-aplicados)
9. [Manual de Instalación y Despliegue](#9-manual-de-instalación-y-despliegue)

---

## 1. Descripción General del Sistema

BovWeight CR es un sistema multiplataforma para el monitoreo y control del peso de ganado bovino. Está compuesto por cuatro repositorios independientes que trabajan en conjunto:

| Repositorio | Tecnología | Rol |
|---|---|---|
| `BovweightBackend` | Laravel 13 + MySQL | API REST central |
| `BovweightWeb` | Vue 3 + Vite | Panel web para el Técnico |
| `BovweightMovil` | Ionic + Vue 3 + Capacitor | App móvil (Ganadero, Veterinario, Ayudante) |
| `BovweightML` | FastAPI + PyTorch | Microservicio de estimación de peso por foto |

El sistema maneja cuatro roles de usuario con permisos diferenciados:

| ID Rol | Nombre | Plataforma | Descripción |
|---|---|---|---|
| 1 | Ganadero | Móvil | Propietario de fincas y animales |
| 2 | Veterinario | Móvil | Gestión clínica y tratamientos |
| 3 | Ayudante | Móvil | Captura rápida de pesos y consulta |
| 4 | Técnico | Web | Supervisión y administración general |

---

## 2. Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENTES                                 │
│                                                                 │
│   ┌──────────────────┐          ┌──────────────────────────┐    │
│   │   BovweightWeb   │          │     BovweightMovil       │    │
│   │   (Vue 3 + Vite) │          │ (Ionic + Vue 3 +         │    │
│   │   Rol: Técnico   │          │  Capacitor)              │    │
│   │   Puerto: 5173   │          │  Roles: Ganadero,        │    │
│   └────────┬─────────┘          │  Veterinario, Ayudante   │    │
│            │                    └───────────┬──────────────┘    │
└────────────┼──────────────────────────────┬─┘                   │
             │  HTTP + Bearer Token         │                     │
             ▼                              ▼                     │
┌─────────────────────────────────────────────────────────────┐   │
│                  BovweightBackend                           │   │
│              (Laravel 13 + Sanctum)                         │   │
│                   Puerto: 8000                              │   │
│                                                             │   │
│  ┌──────────────┐  ┌───────────────┐  ┌─────────────────┐   │   │
│  │ Controllers  │  │   Services    │  │    Observers    │   │   │
│  │ Auth         │  │ PesajeService │  │ UserObserver    │   │   │
│  │ Ganadero     │  │ HistorialSvc  │  │ AnimalObserver  │   │   │
│  │ Veterinario  │  └───────────────┘  │ FincaObserver   │   │   │
│  │ Ayudante     │  ┌───────────────┐  │ PesajeObserver  │   │   │
│  │ UserCtrl     │  │  Strategies   │  │ AtiendeObserver │   │   │
│  │ Dashboard    │  │ OrdenarPorPeso│  │ AyudanteObserver│   │   │
│  │ Historial    │  │ OrdenarPorNom │  │ TratamObserver  │   │   │
│  │ Finca        │  │ OrdenarPorFec │  └─────────────────┘   │   │
│  └──────┬───────┘  └───────┬───────┘                        │   │
│         │                  │                                │   │
│         ▼                  ▼                                │   │
│  ┌─────────────────────────────────────────────────────┐    │   │
│  │                MySQL / Base de Datos                │    │   │
│  │  roles, users, fincas, animales, pesajes,           │    │   │
│  │  tratamientos, atiende, ayudantes,                  │    │   │
│  │  historial_acciones                                 │    │   │
│  └─────────────────────────────────────────────────────┘    │   │
│                                                             │   │
│         HTTP interno al microservicio ML                    │   │
│         ▼                                                   │   │
│  ┌──────────────────────────────────┐                       │   │
│  │         BovweightML              │                       │   │
│  │   (FastAPI + PyTorch + YOLO)     │                       │   │
│  │         Puerto: 8001             │                       │   │
│  │   POST /predict → peso (kg)      │                       │   │
│  └──────────────────────────────────┘                       │   │
└─────────────────────────────────────────────────────────────┘
```

[Arquitectura del Sistema](imagenes-documentacion-tecnica/arquitectura.jpg)

### Flujo de autenticación

1. El cliente envía `POST /api/login` con `{ correo, clave }`.
2. El backend valida credenciales y estado del usuario.
3. Si es válido, crea un token con **Laravel Sanctum** y lo retorna.
4. Todas las peticiones siguientes incluyen el token en el header: `Authorization: Bearer <token>`.
5. El middleware `VerificarRol` valida adicionalmente que el rol del usuario coincida con el grupo de rutas requerido.

### Flujo de estimación de peso por foto

1. El cliente (Ganadero o Ayudante) toma una foto lateral del animal.
2. La foto se envía al backend vía `POST /api/ganadero/estimar-peso` o `POST /api/ayudante/estimar-peso`.
3. El backend (a través de `PesajeService`) reenvía la imagen al microservicio ML vía `POST /predict`.
4. El microservicio ML usa YOLOv8 para detectar la vaca en la imagen, recorta el bounding box y lo pasa a una CNN (EfficientNet-B0) para estimar el peso en kg.
5. El peso estimado regresa al cliente para que el usuario lo confirme o ajuste manualmente.
6. El usuario confirma el pesaje; el backend lo persiste en la tabla `pesajes` y actualiza el campo `peso` del animal.

---

## 3. Stack Tecnológico

### BovweightBackend
- **Framework:** Laravel 13
- **Autenticación:** Laravel Sanctum 4.3
- **Lenguaje:** PHP 8.x
- **Base de datos:** MySQL (driver Eloquent ORM)
- **Comunicación con ML:** `Illuminate\Support\Facades\Http` (HTTP client interno)
- **Patrones implementados:** Strategy, Observer, Service Layer, Interface/Dependency Injection

### BovweightWeb
- **Framework:** Vue 3 (Composition API)
- **Build tool:** Vite 8
- **Estado global:** Pinia 3
- **HTTP:** Axios 1.x con interceptores para Bearer token y manejo de errores
- **Router:** Vue Router 5

### BovweightMovil
- **Framework:** Ionic + Vue 3 + TypeScript
- **Mobile runtime:** Capacitor (Android)
- **Estado global:** Pinia
- **HTTP:** Axios
- **Plugins nativos:** `@capacitor/camera`, `@capacitor/app`, `@capacitor/haptics`, `@capacitor/keyboard`, `@capacitor/status-bar`
- **Reportes:** jsPDF (PDF), xlsx / xlsx-js-style (Excel)
- **Gráficos:** Chart.js

### BovweightML
- **Framework:** FastAPI 0.136
- **Server:** Uvicorn
- **Detección de objeto:** YOLOv8n-seg (Ultralytics)
- **Red de estimación de peso:** EfficientNet-B0 (PyTorch 2.6)
- **Preprocesamiento de imagen:** PIL, torchvision transforms
- **Soporte GPU/CPU:** automático (`cuda` si disponible, `cpu` como fallback)



## 4. Modelo de Base de Datos
[Base de datos](imagenes-documentacion-tecnica/modeloBaseDatos.png)
### Descripción de tablas

**`roles`** — Catálogo de roles del sistema.  
Valores: 1 = Ganadero, 2 = Veterinario, 3 = Ayudante, 4 = Técnico.

**`users`** — Usuarios del sistema. La PK es la cédula de identidad (`identificacion_usuario`), no un autoincremental. El campo `estado` permite activar/desactivar el acceso sin eliminar el registro.

**`fincas`** — Propiedades ganaderas registradas por un Ganadero (FK a `users`).

**`animales`** — Registro de bovinos. PK es el número de arete oficial SENASA (`n_arete`). Incluye `proximo_pesaje` y `repetir_cada_dias` para el sistema de recordatorios del calendario de pesajes.

**`pesajes`** — Historial de pesajes por animal. Cada pesaje guarda el peso aproximado, la fecha y opcionalmente la foto usada para la estimación.

**`tratamientos`** — Tratamientos médicos aplicados a un animal, registrados por un Veterinario. Incluye medicamento, descripción, fechas de inicio y fin.

**`atiende`** — Tabla de relación N:M entre Veterinarios y Fincas. Indica qué veterinario está asignado a qué finca.

**`ayudantes`** — Relación 1:1 entre un Ayudante y una Finca. Un ayudante solo puede pertenecer a una finca a la vez.

**`historial_acciones`** — Bitácora de auditoría automática. Registra toda acción relevante sobre los modelos del sistema mediante Observers de Eloquent.

---

## 5. Documentación de la API REST

**Base URL:** `http://127.0.0.1:8000/api`  
**Autenticación:** Bearer Token (Laravel Sanctum)  
**Content-Type:** `application/json`

### 5.1 Autenticación

#### `POST /login`
Autentica al usuario y retorna un token de acceso.

**Sin autenticación requerida.**

**Body:**
```json
{
  "correo": "usuario@ejemplo.com",
  "clave": "contraseña123"
}
```

**Respuesta 200:**
```json
{
  "token": "1|abc123...",
  "usuario": {
    "identificacion": 12345678,
    "nombre": "María",
    "apellido": "Vargas",
    "correo": "usuario@ejemplo.com",
    "rol": 4,
    "estado": true
  }
}
```

**Errores:**
- `401` — Credenciales incorrectas.
- `403` — Usuario inactivo.

---

#### `POST /logout`
Invalida el token actual del usuario autenticado.

**Requiere:** Bearer Token.

**Respuesta 200:**
```json
{ "message": "Sesión cerrada correctamente." }
```

---

#### `GET /me`
Retorna los datos del usuario autenticado.

**Requiere:** Bearer Token.

**Respuesta 200:**
```json
{
  "identificacion": 12345678,
  "nombre": "María",
  "apellido": "Vargas",
  "correo": "usuario@ejemplo.com",
  "rol": 4,
  "estado": true
}
```

---

#### `POST /auth/forgot-password`
Envía un enlace de restablecimiento de contraseña al correo indicado.

**Sin autenticación requerida.**

**Body:**
```json
{ "correo": "usuario@ejemplo.com" }
```

**Respuesta 200:**
```json
{ "mensaje": "Se ha enviado un enlace de restablecimiento al correo proporcionado" }
```

**Errores:** `422` si el correo no existe o falla la validación.

---

#### `POST /auth/reset-password`
Establece una nueva contraseña usando el token recibido por correo. El token expira en 60 minutos.

**Body:**
```json
{
  "correo": "usuario@ejemplo.com",
  "token": "token_recibido_por_email",
  "clave": "nuevaContraseña123",
  "clave_confirmation": "nuevaContraseña123"
}
```

**Respuesta 200:**
```json
{ "mensaje": "Contraseña restablecida exitosamente" }
```

---

### 5.2 Rutas del Técnico

**Requieren:** Bearer Token + rol Técnico (id_rol = 4).

#### `GET /usuarios`
Lista todos los usuarios del sistema.

#### `POST /usuarios`
Crea un nuevo usuario.

**Body:**
```json
{
  "identificacion_usuario": 87654321,
  "nombre_usuario": "Juan",
  "apellido1_usuario": "Pérez",
  "apellido2_usuario": "Mora",
  "correo": "juan@ejemplo.com",
  "clave": "contraseña123",
  "id_rol": 2
}
```

#### `GET /usuarios/{id}`
Retorna un usuario específico por su identificación.

#### `PUT /usuarios/{id}`
Actualiza los datos de un usuario. Si `clave` va vacía, no se modifica la contraseña.

#### `PATCH /usuarios/{id}/estado`
Activa o desactiva un usuario (toggle). Registra la acción en el historial como "Activar usuario" o "Desactivar usuario".

#### `GET /roles`
Retorna el catálogo completo de roles.

#### `GET /historial`
Retorna el historial de acciones paginado, ordenado por fecha descendente.

#### `GET /dashboard`
Retorna métricas generales del sistema: total de usuarios, animales, pesajes, fincas activas, distribución por rol y actividad de pesaje.

#### `GET /fincas`
Lista todas las fincas registradas en el sistema.

---

### 5.3 Rutas del Veterinario

**Requieren:** Bearer Token + rol Veterinario (id_rol = 2).

#### `GET /veterinario/fincas`
Lista las fincas a las que el veterinario autenticado está asignado.

#### `GET /veterinario/fincas/{idFinca}/animales`
Lista los animales activos de una finca asignada al veterinario.

#### `GET /veterinario/animales/{nArete}/pesajes`
Retorna el historial de pesajes de un animal específico.

#### `GET /veterinario/animales/{nArete}/tratamientos`
Lista los tratamientos registrados para un animal.

#### `POST /veterinario/tratamientos`
Crea un nuevo tratamiento para un animal.

**Body:**
```json
{
  "tipo_tratamiento": "Vacunación",
  "medicamento": "Ivermectina",
  "descripcion": "Dosis preventiva semestral",
  "fecha_inicio": "2026-06-01",
  "fecha_fin": "2026-06-01",
  "n_arete": "188000000000001"
}
```

#### `PUT /veterinario/tratamientos/{id}`
Actualiza un tratamiento existente.

---

### 5.4 Rutas del Ganadero

**Requieren:** Bearer Token + rol Ganadero (id_rol = 1).

#### `GET /ganadero/fincas`
Lista las fincas del ganadero autenticado.

#### `POST /ganadero/fincas`
Crea una nueva finca.

**Body:**
```json
{
  "nombre_finca": "Finca La Esperanza",
  "ubicacion_finca": "Liberia, Guanacaste"
}
```

#### `PUT /ganadero/fincas/{idFinca}`
Edita los datos de una finca.

#### `GET /ganadero/fincas/{idFinca}/animales`
Lista los animales de una finca específica.

#### `GET /ganadero/fincas/{idFinca}/resumen`
Retorna métricas de la finca: total de animales, peso promedio y peso máximo.

#### `POST /ganadero/fincas/{idFinca}/animales`
Registra un nuevo animal en la finca.

**Body:**
```json
{
  "n_arete": "188000000000001",
  "nombre_animal": "Canela",
  "raza": "Brahman",
  "sexo": "F",
  "edad": 3,
  "peso": 250.5,
  "id_finca": 1
}
```

#### `PUT /ganadero/animales/{nArete}`
Edita los datos de un animal (nombre, raza, estado, etc.).

#### `GET /ganadero/animales/{nArete}/pesajes`
Historial de pesajes de un animal.

#### `POST /ganadero/estimar-peso`
Envía una foto al microservicio ML y retorna el peso estimado en kg. No persiste el pesaje.

**Body:** `multipart/form-data`
- `foto`: archivo de imagen
- `sexo`: `"M"` o `"F"`

**Respuesta 200:**
```json
{ "peso_estimado_kg": 166.2 }
```

#### `POST /ganadero/animales/{nArete}/pesajes`
Persiste un pesaje para un animal. Actualiza el campo `peso` del animal y reprograma el recordatorio si aplica.

**Body:** `multipart/form-data`
- `peso`: decimal
- `foto` (opcional): archivo de imagen
- `sexo` (opcional): `"M"` o `"F"`

#### `GET /ganadero/recordatorios`
Lista los animales del ganadero que tienen un `proximo_pesaje` programado.

#### `GET /ganadero/animales-todos`
Lista todos los animales del ganadero (de todas sus fincas).

#### `PUT /ganadero/animales/{nArete}/programar`
Programa un recordatorio de pesaje para un animal.

**Body:**
```json
{
  "proximo_pesaje": "2026-06-24",
  "repetir_cada_dias": 7
}
```

#### `GET /ganadero/animales/{nArete}/tratamientos`
Lista los tratamientos de un animal (solo lectura para el ganadero).

#### `GET /ganadero/veterinarios`
Lista todos los veterinarios disponibles en el sistema.

#### `GET /ganadero/ayudantes`
Lista todos los ayudantes disponibles en el sistema.

#### `POST /ganadero/fincas/{idFinca}/veterinarios`
Asigna un veterinario a una finca (crea registro en tabla `atiende`).

**Body:** `{ "identificacion_usuario": 321 }`

#### `DELETE /ganadero/fincas/{idFinca}/veterinarios`
Desasigna un veterinario de una finca.

#### `POST /ganadero/fincas/{idFinca}/ayudantes`
Asigna un ayudante a una finca.

#### `DELETE /ganadero/ayudantes`
Desasigna un ayudante.

#### `GET /ganadero/fincas/{idFinca}/veterinarios`
Lista los veterinarios asignados a una finca.

#### `GET /ganadero/fincas/{idFinca}/ayudantes`
Lista los ayudantes asignados a una finca.

#### `POST /ganadero/reportes`
Registra la generación de un reporte PDF en el historial.

---

### 5.5 Rutas del Ayudante

**Requieren:** Bearer Token + rol Ayudante (id_rol = 3).

#### `GET /ayudante/finca`
Retorna la finca a la que está asignado el ayudante autenticado.

#### `GET /ayudante/animales`
Lista los animales de la finca asignada al ayudante.

#### `GET /ayudante/animales/{nArete}`
Retorna los datos de un animal específico.

#### `GET /ayudante/animales/{nArete}/pesajes`
Historial de pesajes de un animal.

#### `GET /ayudante/animales/{nArete}/tratamientos`
Lista los tratamientos de un animal (solo lectura).

#### `POST /ayudante/estimar-peso`
Igual que el del ganadero: envía foto al ML y retorna peso estimado.

#### `POST /ayudante/animales/{nArete}/pesajes`
Persiste un pesaje para un animal.

---

### 5.6 Códigos de respuesta comunes

| Código | Significado |
|---|---|
| 200 | OK — operación exitosa |
| 201 | Created — recurso creado |
| 401 | Unauthorized — token inválido o credenciales incorrectas |
| 403 | Forbidden — usuario inactivo o rol sin permisos |
| 404 | Not Found — recurso no encontrado |
| 422 | Unprocessable Entity — fallo de validación |
| 500 | Internal Server Error — error interno del servidor |

---

## 6. Módulo de Machine Learning (BovweightML)

### Descripción

Microservicio independiente construido con **FastAPI** que expone un endpoint de predicción de peso bovino a partir de una foto lateral del animal.

### Pipeline de inferencia
[Pipeline 1](imagenes-documentacion-tecnica/pipeline1.jpeg)
[Pipeline 2](imagenes-documentacion-tecnica/pipeline2.jpeg)
[Pipeline 3](imagenes-documentacion-tecnica/pipeline3.jpeg)
[Pipeline 4](imagenes-documentacion-tecnica/pipeline4.jpeg)


### Endpoints del microservicio

#### `GET /`
Retorna estado y modelo cargado.

**Respuesta:**
```json
{ "mensaje": "API Bovweight CNN lista.", "device": "cpu", "modelo": "..." }
```

#### `GET /health`
Retorna estado de salud y métricas de validación del modelo.

**Respuesta:**
```json
{
  "ok": true,
  "device": "cpu",
  "val": { "mae": 24.0, "r2": 0.42, "mape": 15.0 }
}
```

#### `POST /predict`
Estima el peso de un bovino a partir de una foto lateral.

**Body:** `multipart/form-data`
- `file`: imagen (JPEG/PNG)
- `sexo`: `"M"` o `"F"` (default: `"F"`)

**Respuesta 200:**
```json
{
  "peso_estimado_kg": 166.2,
  "bbox": [120, 45, 890, 650],
  "nota": "Estimacion CNN (~MAPE 15%). Foto lateral, vaca completa, buena luz."
}
```

**Error si no se detecta vaca:**
```json
{ "error": "No se detecto ninguna vaca en la imagen." }
```

### Métricas del modelo

| Métrica | Valor |
|---|---|
| Dataset entrenamiento | ~12,000 imágenes (vistas laterales) |
| MAE (validación) | ~24 kg |
| R² (validación) | ~0.42 |
| MAPE (validación) | ~15% |

> El modelo es un **soporte de apoyo**. No reemplaza una báscula oficial para fines comerciales o sanitarios (SENASA).

### Uso por línea de comando

```bash
python predict_cnn.py vaca.jpg F   # hembra
python predict_cnn.py toro.jpg M   # macho
```

---

## 7. Documentación de Componentes y Módulos

### 7.1 BovweightBackend

#### Controladores (`app/Http/Controllers/`)

| Controlador | Responsabilidad |
|---|---|
| `AuthController` | Login, logout, perfil propio, recuperación y restablecimiento de contraseña |
| `UserController` | CRUD de usuarios, toggle de estado activo/inactivo |
| `DashboardController` | Métricas globales del sistema para el panel del Técnico |
| `FincaController` | Listado de fincas para el Técnico (solo lectura) |
| `HistorialController` | Listado paginado del historial de acciones |
| `GanaderoController` | Gestión completa de fincas, animales, pesajes, asignaciones, reportes |
| `VeterinarioController` | Consulta de fincas/animales asignados y gestión de tratamientos |
| `AyudanteController` | Consulta de la finca asignada, animales y captura de pesajes |

#### Middleware (`app/Http/Middleware/`)

**`VerificarRol`** — Middleware de autorización por rol. Se registra como `rol` en el kernel. Mapeo de roles:

```php
'tecnico'     => 4
'veterinario' => 2
'ayudante'    => 3
'ganadero'    => 1
```

Uso en rutas: `middleware(['auth:sanctum', 'rol:ganadero'])`.

#### Servicios (`app/Services/`)

**`PesajeService`**
- `estimar(UploadedFile $foto, string $sexo): float` — Envía la foto al microservicio ML y retorna el peso estimado. Lanza `RuntimeException` si el servicio ML no responde.
- `registrar(string $nArete, float $peso, ?UploadedFile $foto, ?string $sexo): Pesaje` — Persiste el pesaje, actualiza el peso del animal y reprograma el recordatorio si `repetir_cada_dias` está definido.

**`HistorialService`** (implementa `HistorialServiceInterface`)
- `registrar(string $accion, string $tabla, $id): void` — Crea un registro en `historial_acciones` usando el usuario autenticado vía `Auth::user()`.

#### Observers (`app/Observers/`)

Los Observers de Eloquent registran automáticamente en el historial cada evento relevante sobre los modelos, sin necesidad de llamadas manuales en los controladores:

| Observer | Eventos que registra |
|---|---|
| `UserObserver` | Crear usuario, Editar usuario, Activar/Desactivar usuario, Eliminar usuario |
| `AnimalObserver` | Crear animal, Editar animal, Programar pesaje, Quitar recordatorio, Eliminar animal |
| `FincaObserver` | Crear finca, Editar finca, Eliminar finca |
| `PesajeObserver` | Registrar pesaje, Editar pesaje, Eliminar pesaje |
| `TratamientoObserver` | Crear tratamiento, Editar tratamiento, Eliminar tratamiento |
| `AtiendeObserver` | Asignar veterinario a finca, Desasignar veterinario de finca |
| `AyudanteObserver` | Asignar ayudante a finca, Reasignar ayudante, Desasignar ayudante |

#### Estrategias (`app/Strategies/`)

Implementan el patrón **Strategy** para ordenar listas de animales. Todos implementan la interfaz `OrdenarAnimalesStrategy`:

```php
interface OrdenarAnimalesStrategy {
    public function ordenar(Collection $animales): Collection;
}
```

| Clase | Criterio de ordenamiento |
|---|---|
| `OrdenarPorPeso` | Descendente por campo `peso` |
| `OrdenarPorNombre` | Ascendente por `nombre_animal` |
| `OrdenarPorFechaNacimiento` | Por `fecha_nacimiento` |

### 7.2 BovweightWeb

#### Estructura de directorios

```
src/
├── App.vue              # Raíz de la aplicación
├── main.js              # Bootstrap: Vue, Pinia, Router
├── router/              # Rutas (vue-router 5)
├── stores/              # Estado global (Pinia)
├── services/            # Capa de comunicación con la API
│   ├── api.js           # Instancia de Axios configurada
│   ├── auth.js          # Login / logout
│   ├── usuarios.js      # CRUD usuarios
│   ├── fincas.js        # Listado de fincas
│   ├── historial.js     # Historial de acciones
│   └── dashboard.js     # Métricas del dashboard
├── views/               # Páginas de la aplicación
│   ├── Login.vue
│   ├── ForgotPassword.vue
│   ├── ResetPassword.vue
│   ├── Inicio.vue       # Dashboard (Panel de Control)
│   ├── Usuarios.vue     # Listado de usuarios
│   ├── UsuarioForm.vue  # Crear / Editar usuario
│   ├── Fincas.vue       # Listado de fincas
│   └── Historial.vue    # Historial de acciones
└── components/          # Componentes reutilizables
```

#### `api.js` — Interceptores de Axios

- **Request interceptor:** adjunta automáticamente el `Authorization: Bearer <token>` desde `localStorage`.
- **Response interceptor:**
  - Sin conexión → alerta al usuario.
  - `401` → elimina el token y redirige a `/login`.
  - `403` → alerta de permisos.
  - `500` → alerta de error interno.

### 7.3 BovweightMovil

#### Estructura de directorios

```
src/
├── App.vue
├── main.ts
├── config.js            # API_HOST, API_BASE_URL, STORAGE_BASE_URL
├── router/
│   ├── index.ts         # Rutas por rol + guardias de navegación
│   └── paths.ts         # Constantes de rutas nombradas
├── stores/              # Pinia stores (auth, etc.)
├── services/            # Servicios HTTP por rol
├── composables/         # Lógica reutilizable (Vue composables)
├── views/
│   ├── auth/            # LoginPage
│   ├── ganadero/        # MenuPage, FincasPage, AnimalesPage,
│   │                    # CapturaRapidaPage, CalendarioPage,
│   │                    # ReportePage, AnimalDetallePage...
│   ├── veterinario/     # DashboardPage, FincasPage,
│   │                    # AnimalesPage, TratamientosPage
│   └── ayudante/        # MenuPage, AnimalesPage,
│                        # CapturaPage, AnimalDetallePage
├── components/          # Componentes Ionic reutilizables
└── theme/               # Variables CSS de Ionic
```

#### Guardias de navegación (router)

```typescript
router.beforeEach((to, from) => {
    const auth = useAuthStore()
    // Redirige a login si la ruta requiere auth y no hay sesión
    if (to.meta.requiresAuth && !auth.isAuthenticated) return '/login'
    // Redirige según rol si ya está autenticado e intenta ir al login
    if (to.meta.guest && auth.isAuthenticated) {
        if (rol === 2) return paths.veterinario.fincas
        if (rol === 1) return paths.ganadero.menu
        if (rol === 3) return paths.ayudante.menu
    }
})
```

---

## 8. Patrones de Diseño Aplicados

### Strategy (Ordenamiento de animales)

**Dónde:** `app/Strategies/`

El ordenamiento de la lista de animales está desacoplado del controlador mediante una interfaz `OrdenarAnimalesStrategy`. Esto permite agregar nuevos criterios de ordenamiento sin modificar los controladores existentes.

```
OrdenarAnimalesStrategy (interface)
        ├── OrdenarPorPeso
        ├── OrdenarPorNombre
        └── OrdenarPorFechaNacimiento
```

### Observer (Auditoría automática)

**Dónde:** `app/Observers/`

Cada modelo de Eloquent tiene su Observer registrado. Cuando Eloquent dispara un evento (`created`, `updated`, `deleted`), el Observer correspondiente llama a `HistorialService::registrar()` automáticamente. Esto garantiza que toda acción quede auditada sin acoplar la lógica de historial a los controladores.

### Service Layer (Lógica de negocio reutilizable)

**Dónde:** `app/Services/`

- `PesajeService` encapsula la comunicación con el microservicio ML y la lógica de persistencia de pesajes, compartida entre `GanaderoController` y `AyudanteController`.
- `HistorialService` encapsula la escritura en `historial_acciones`, inyectable vía la interfaz `HistorialServiceInterface`.

### Interface / Dependency Injection

**Dónde:** `app/Interfaces/HistorialServiceInterface.php`

`HistorialService` implementa `HistorialServiceInterface`, lo que permite sustituir la implementación en tests sin modificar los Observers ni los controladores.

---

## 9. Manual de Instalación y Despliegue

### Requisitos previos

| Herramienta | Versión mínima |
|---|---|
| PHP | 8.2+ |
| Composer | 2.x |
| Node.js | 18+ |
| npm | 9+ |
| MySQL | 8.0+ |
| Python | 3.10+ |
| pip | 23+ |

---

### 9.1 Backend (BovweightBackend)

```bash
# 1. Clonar el repositorio
git clone https://github.com/Cavalari2599/BovweightBackend.git
cd BovweightBackend

# 2. Instalar dependencias PHP
composer install

# 3. Crear el archivo de entorno
cp .env.example .env

# 4. Editar .env con tus datos
# Variables clave:
#   APP_URL=http://127.0.0.1:8000
#   DB_CONNECTION=mysql
#   DB_HOST=127.0.0.1
#   DB_PORT=3306
#   DB_DATABASE=bovweight
#   DB_USERNAME=root
#   DB_PASSWORD=tu_contraseña
#   MAIL_MAILER=smtp
#   MAIL_HOST=smtp.gmail.com
#   MAIL_PORT=587
#   MAIL_USERNAME=tucorreo@gmail.com
#   MAIL_PASSWORD=tu_app_password
#   MAIL_FROM_ADDRESS=tucorreo@gmail.com
#   ML_URL=http://127.0.0.1:8001   ← URL del microservicio ML

# 5. Generar la clave de la aplicación
php artisan key:generate

# 6. Crear la base de datos en MySQL
mysql -u root -p -e "CREATE DATABASE bovweight CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# 7. Ejecutar migraciones
php artisan migrate

# 8. Ejecutar seeders (datos iniciales: roles, usuario técnico por defecto)
php artisan db:seed

# 9. Iniciar el servidor de desarrollo
php artisan serve
# → Disponible en http://127.0.0.1:8000
```

> **Nota sobre `ML_URL`:** la URL del microservicio ML debe configurarse en `config/services.php` bajo la clave `ml.url`. `PesajeService` la consume via `config('services.ml.url')`.

---

### 9.2 Microservicio ML (BovweightML)

```bash
# 1. Clonar el repositorio
git clone https://github.com/Cavalari2599/BovweightML.git
cd BovweightML

# 2. (Opcional pero recomendado) Crear un entorno virtual
python -m venv venv
source venv/bin/activate       # Linux/Mac
venv\Scripts\activate          # Windows PowerShell

# 3. Instalar dependencias
pip install -r requirements.txt

# --- Para GPU con CUDA 12.4 (opcional, más rápido) ---
# pip install torch==2.6.0 torchvision==0.21.0 --index-url https://download.pytorch.org/whl/cu124
# pip install -r requirements.txt

# 4. Verificar que el modelo esté presente
ls modelo_peso_cnn.pt    # debe existir en la raíz del repo

# 5. Iniciar el microservicio
uvicorn main:app --host 0.0.0.0 --port 8001 --reload
# → Disponible en http://127.0.0.1:8001
# → Documentación automática: http://127.0.0.1:8001/docs
```

---

### 9.3 Frontend Web (BovweightWeb)

```bash
# 1. Clonar el repositorio
git clone https://github.com/Cavalari2599/BovweightWeb.git
cd BovweightWeb

# 2. Instalar dependencias
npm install

# 3. Iniciar en modo desarrollo
npm run dev
# → Disponible en http://localhost:5173

# 4. Compilar para producción
npm run build
# → Archivos generados en /dist
```

> La URL del backend está fijada en `src/services/api.js` como `http://127.0.0.1:8000/api`. Para producción, actualizar ese valor o manejarlo vía variable de entorno.

---

### 9.4 App Móvil (BovweightMovil)

```bash
# 1. Clonar el repositorio
git clone https://github.com/Cavalari2599/BovweightMovil.git
cd BovweightMovil

# 2. Instalar dependencias
npm install

# 3. Configurar la URL del backend
# Editar src/config.js o crear un archivo .env con:
# VITE_API_HOST=http://TU_IP_LOCAL:8000

# 4. Iniciar en modo desarrollo web (browser)
npm run dev

# 5. Para compilar y correr en Android:
npx cap sync android
npx cap open android
# → Abre Android Studio → Run
```

> Para pruebas en dispositivo físico Android, reemplazar `127.0.0.1` en `VITE_API_HOST` por la IP local de la máquina que corre el backend (ej. `192.168.1.100`).

---

### 9.5 Orden de arranque recomendado

Para un entorno de desarrollo local completo, iniciar los servicios en este orden:

1. **MySQL** — base de datos debe estar corriendo.
2. **BovweightML** — `uvicorn main:app --port 8001`
3. **BovweightBackend** — `php artisan serve` (puerto 8000)
4. **BovweightWeb** — `npm run dev` (puerto 5173)
5. **BovweightMovil** — `npm run dev` (puerto 8100) o via Capacitor en Android

---

*Documentación generada a partir del análisis del código fuente de los repositorios BovweightBackend, BovweightWeb, BovweightMovil y BovweightML — Junio 2026.*