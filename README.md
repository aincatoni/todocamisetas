# TodoCamisetas

API REST desarrollada para el examen de Desarrollo Backend. El proyecto permite administrar `clientes`, `camisetas` y `tallas`, gestionar la relacion muchos a muchos entre camisetas y tallas, y calcular el `precio_final` de una camiseta segun el cliente que la consulta.

## Repositorio

- URL: `https://github.com/aincatoni/todocamisetas`

## Objetivo del proyecto

La solucion fue construida para cumplir con los requerimientos del examen mediante una API RESTful documentada, probada y ejecutable en Docker, con foco en:

- CRUD completo de `clientes`
- CRUD completo de `camisetas`
- CRUD completo de `tallas`
- asociacion y desasociacion `camisetas` <-> `tallas`
- regla de negocio para calcular `precio_final`
- documentacion Swagger / OpenAPI
- coleccion Postman para pruebas manuales y de runner
- pruebas automatizadas Feature con PHPUnit

## Stack tecnologico

- PHP `8.2`
- Laravel `11`
- MySQL `8.0`
- Nginx
- Docker Compose
- Swagger con `darkaonline/l5-swagger`
- PHPUnit
- Postman

## Arquitectura de la solucion

La aplicacion sigue una estructura tipica de Laravel orientada a API:

- `routes/api.php`: definicion de endpoints REST
- `app/Http/Controllers`: logica HTTP y reglas de validacion
- `app/Models`: entidades del dominio con Eloquent
- `database/migrations`: definicion del esquema relacional
- `database/seeders`: datos base para pruebas y demostracion
- `tests/Feature`: pruebas automatizadas de endpoints
- `Evidencias/swaggerui`: capturas de pruebas manuales en Swagger UI

### Flujo general

```mermaid
flowchart LR
    U[Usuario / evaluador] -->|HTTP JSON| N[Nginx]
    N --> A[Laravel 11 API]
    A --> C[Controllers]
    C --> M[Models Eloquent]
    M --> D[(MySQL 8.0)]
    A --> S[Swagger UI / OpenAPI]
```

## Modelo de entidades

### Entidades principales

1. `clientes`
2. `camisetas`
3. `tallas`
4. `camiseta_talla` como tabla pivote

### Diagrama entidad-relacion

```mermaid
erDiagram
    CLIENTES {
        bigint id PK
        string nombre_comercial
        string rut UK
        string direccion
        string categoria
        string contacto_nombre
        string contacto_email
        decimal porcentaje_oferta
        datetime created_at
        datetime updated_at
    }

    CAMISETAS {
        bigint id PK
        string titulo
        string club
        string pais
        string tipo
        string color
        decimal precio
        decimal precio_oferta
        text detalles
        string codigo_producto UK
        datetime created_at
        datetime updated_at
    }

    TALLAS {
        bigint id PK
        string nombre UK
        datetime created_at
        datetime updated_at
    }

    CAMISETA_TALLA {
        bigint id PK
        bigint camiseta_id FK
        bigint talla_id FK
        datetime created_at
        datetime updated_at
    }

    CAMISETAS ||--o{ CAMISETA_TALLA : tiene
    TALLAS ||--o{ CAMISETA_TALLA : pertenece
```

### Estructura y relaciones implementadas

#### `clientes`

Campos:

- `nombre_comercial`
- `rut` unico
- `direccion`
- `categoria`
- `contacto_nombre`
- `contacto_email`
- `porcentaje_oferta` nullable

Reglas:

- `categoria` acepta `Regular` o `Preferencial`
- `rut` debe ser unico
- `contacto_email` debe tener formato valido
- `porcentaje_oferta` admite valores entre `0` y `100`

#### `camisetas`

Campos:

- `titulo`
- `club`
- `pais`
- `tipo`
- `color`
- `precio`
- `precio_oferta` nullable
- `detalles` nullable
- `codigo_producto` unico

Reglas:

- `precio` debe ser numerico y mayor o igual a `0`
- `precio_oferta` puede ser nulo
- al crear, `precio_oferta` no puede ser mayor que `precio`
- al actualizar, se vuelve a validar que `precio_oferta` no supere el precio base efectivo
- `codigo_producto` debe ser unico

#### `tallas`

Campos:

- `nombre` unico

Reglas:

- catalogo reutilizable para multiples camisetas
- no se puede eliminar una talla si sigue asociada a una camiseta

#### `camiseta_talla`

Reglas de integridad:

- relacion muchos a muchos entre `camisetas` y `tallas`
- `camiseta_id` usa `cascadeOnDelete()`
- `talla_id` usa `restrictOnDelete()`
- existe restriccion `unique(camiseta_id, talla_id)` para evitar asociaciones duplicadas

## Decisiones de desarrollo y arquitectura

Estas fueron las decisiones principales reflejadas en la implementacion:

1. Se uso Laravel 11 porque permite resolver rapidamente routing, validaciones, ORM, migraciones, seeders y testing en una estructura limpia.
2. Se separo el dominio en tres recursos principales (`clientes`, `camisetas`, `tallas`) para mantener endpoints claros y alineados con REST.
3. La relacion `camisetas` - `tallas` se modelo con una tabla pivote porque una camiseta puede existir en varias tallas y una talla puede reutilizarse en varias camisetas.
4. `cliente_id` no se persiste en `camisetas`; se usa solo como parametro de consulta para calcular `precio_final` en tiempo de respuesta.
5. `precio_final` no se guarda en base de datos; es un valor derivado de la regla de negocio y se calcula dinamicamente en `GET /api/camisetas/{id}`.
6. Se implemento un trait `ApiResponse` para homogeneizar respuestas exitosas y de error en toda la API.
7. Las validaciones se dejaron a nivel de controlador con mensajes en espanol para facilitar la correccion funcional del examen.
8. Se utilizaron atributos OpenAPI en los controladores para mantener documentacion y codigo cerca entre si.
9. Se agregaron seeders con datos base para que Swagger, Postman y las pruebas partan de un escenario controlado.
10. Se cubrieron escenarios exitosos y de error con pruebas Feature, priorizando comportamiento observable de la API por sobre pruebas unitarias aisladas.

## Regla de negocio: precio final

La regla implementada en `GET /api/camisetas/{id}` es:

- si se envia `cliente_id`
- y el cliente existe
- y su `categoria` es `Preferencial`
- y la camiseta tiene `precio_oferta`

Entonces:

- `precio_final = precio_oferta`

En cualquier otro caso:

- `precio_final = precio`

Ademas:

- si la camiseta no existe, responde `404`
- si el `cliente_id` consultado no existe, responde `404`
- la respuesta del detalle incluye `tallas` y, cuando aplica, `cliente_consultado`

## Validacion de precio solicitada en el examen

El examen pide validar que el precio final de una camiseta dependa del cliente que realiza la consulta. Esa validacion si esta implementada y se resuelve en tiempo de respuesta, no como un valor persistido.

Resumen funcional:

- endpoint: `GET /api/camisetas/{id}?cliente_id={clienteId}`
- si el cliente corresponde a categoria `Preferencial` y la camiseta tiene `precio_oferta`, entonces `precio_final` toma el valor de `precio_oferta`
- si el cliente corresponde a categoria `Regular`, o no existe oferta para la camiseta, entonces `precio_final` toma el valor de `precio`
- si se envia un `cliente_id` inexistente, la API responde `404`

Implementacion en codigo:

- `backend/app/Http/Controllers/CamisetaController.php`: metodo `show()`
- `backend/app/Http/Controllers/CamisetaController.php`: metodo privado `resolverPrecioFinal()`

Datos semilla para probarla:

- cliente `90minutos` con categoria `Preferencial`
- cliente `tdeportes` con categoria `Regular`
- camiseta `CHI-LOC-2025-01` con `precio = 45000` y `precio_oferta = 39990`
- camiseta `ARG-VIS-2025-01` con `precio = 47000` y `precio_oferta = null`

Casos esperados:

- `90minutos` consultando la camiseta con oferta -> `precio_final = 39990.00`
- `tdeportes` consultando esa misma camiseta -> `precio_final = 45000.00`
- cualquier cliente consultando una camiseta sin oferta -> `precio_final = precio`

Cobertura de pruebas:

- prueba automatica en `backend/tests/Feature/CamisetaApiTest.php`
- evidencia manual en `Evidencias/swaggerui/16_precio_final_preferencial.png`
- evidencia manual en `Evidencias/swaggerui/17_precio_final_regular.png`
- evidencia manual en `Evidencias/swaggerui/18_precio_final_cliente_404.png`

## Uso de if y try/catch

En el proyecto se usan ambas estructuras, pero con responsabilidades distintas.

Se usan `if` para casos esperados del negocio:

- validaciones de entrada que devuelven `422`
- recursos no encontrados que devuelven `404`
- conflictos funcionales, por ejemplo eliminar una talla asociada, que devuelven `409`
- regla de precio final segun `cliente_id`, `categoria` y `precio_oferta`

Se usa `try/catch` para capturar errores inesperados de infraestructura o persistencia:

- fallos de consultas a base de datos
- errores al crear, actualizar, eliminar o asociar registros
- excepciones no previstas durante la ejecucion del controlador

La decision aplicada fue mantener los `if` dentro del flujo normal y envolver los metodos con `try/catch` para responder `500` de forma controlada cuando ocurra una excepcion real. Esto permite distinguir correctamente entre errores funcionales del cliente y fallos internos del servidor.

## Convencion de respuestas JSON

### Exito

```json
{
  "success": true,
  "data": {}
}
```

### Error

```json
{
  "success": false,
  "message": "Descripcion del error",
  "errors": {
    "campo": ["mensaje"]
  }
}
```

## Endpoints implementados

### Health

- `GET /api/health`

### Clientes

- `GET /api/clientes`
- `POST /api/clientes`
- `GET /api/clientes/{id}`
- `PUT /api/clientes/{id}`
- `DELETE /api/clientes/{id}`

### Camisetas

- `GET /api/camisetas`
- `POST /api/camisetas`
- `GET /api/camisetas/{id}`
- `PUT /api/camisetas/{id}`
- `DELETE /api/camisetas/{id}`

Filtros disponibles en `GET /api/camisetas`:

- `club`
- `pais`
- `tipo`
- `color`

Parametro adicional en `GET /api/camisetas/{id}`:

- `cliente_id`

### Tallas

- `GET /api/tallas`
- `POST /api/tallas`
- `GET /api/tallas/{id}`
- `PUT /api/tallas/{id}`
- `DELETE /api/tallas/{id}`

### Asociaciones camiseta-talla

- `GET /api/camisetas/{id}/tallas`
- `POST /api/camisetas/{id}/tallas`
- `DELETE /api/camisetas/{id}/tallas/{tallaId}`

## Codigos HTTP cubiertos

- `200` operacion exitosa
- `201` recurso creado
- `404` recurso no encontrado
- `409` conflicto de integridad o asociacion duplicada
- `422` error de validacion

## Datos semilla

Despues de ejecutar `migrate:fresh --seed`, quedan disponibles datos base para pruebas:

### Clientes

- `90minutos` con categoria `Preferencial`
- `tdeportes` con categoria `Regular`

### Camisetas

- `CHI-LOC-2025-01`
- `ARG-VIS-2025-01`

### Tallas

- `S`
- `M`
- `L`
- `XL`

## Ejecucion del proyecto

### Requisitos

- Docker
- Docker Compose

### Levantar el entorno

```bash
docker compose up -d --build
```

### Instalar dependencias de Laravel

```bash
docker compose exec app composer install
```

### Preparar la base de datos

```bash
docker compose exec app php artisan migrate:fresh --seed
```

### Generar documentacion Swagger

```bash
docker compose exec app php artisan l5-swagger:generate
```

### URLs utiles

- API base: `http://localhost:8080/api`
- Swagger UI: `http://localhost:8080/api/documentation`

## Pruebas automatizadas

La API incluye pruebas Feature para validar los flujos principales y errores esperados:

- `HealthTest`
- `ClienteApiTest`
- `CamisetaApiTest`
- `TallaApiTest`

Resultado verificado:

- `14` tests aprobados
- `77` assertions

Comando:

```bash
php artisan test
```

## Swagger y OpenAPI

La documentacion OpenAPI fue implementada con atributos directamente en los controladores. Esto permite mantener sincronizados:

- contratos de entrada
- parametros de ruta y query
- respuestas esperadas
- etiquetas por recurso

La instancia disponible para revision manual es:

- `http://localhost:8080/api/documentation`

## Postman

El proyecto incluye la coleccion:

- `TodoCamisetas API.postman_collection.json`

La coleccion fue preparada para ejecutarse de forma secuencial en Postman Runner y contempla carpetas para:

- `01 Health`
- `02 Clientes`
- `03 Camisetas`
- `04 Tallas`
- `05 Asociaciones`
- `06 Precio Final`
- `07 Errores`
- `08 Cleanup`

Tambien incorpora variables de coleccion para reutilizar IDs generados durante la corrida, por ejemplo:

- `clienteId`
- `camisetaId`
- `tallaId`
- `clientePreferencialId`
- `clienteRegularId`
- `camisetaOfertaId`

## Anexos tecnicos

Como material complementario de la entrega se incluye una guia de apoyo usada durante el diseno y levantamiento de la API:

- `GUIA_ENDPOINTS_TODOCAMISETAS.md`

Este documento se deja como anexo tecnico. El documento principal de la entrega sigue siendo este `README.md`, que refleja el estado final implementado.

## Evidencias

### Evidencias en Swagger UI

Las siguientes capturas quedaron integradas dentro del repositorio en `Evidencias/swaggerui/`.

#### Health

1. `GET /api/health`

![Health](Evidencias/swaggerui/01_health.png)

#### Clientes

1. `GET /api/clientes`

![Listado de clientes](Evidencias/swaggerui/02_clientes_get_listado.png)

2. `POST /api/clientes` con datos validos

![Crear cliente valido](Evidencias/swaggerui/03_clientes_post_valido.png)

3. `GET /api/clientes/{id}`

![Obtener cliente por id](Evidencias/swaggerui/04_clientes_get_id.png)

4. `PUT /api/clientes/{id}`

![Actualizar cliente](Evidencias/swaggerui/05_clientes_put.png)

5. `POST /api/clientes` con datos invalidos

![Crear cliente invalido](Evidencias/swaggerui/06_clientes_post_invalido.png)

6. `GET /api/clientes/{id}` con recurso inexistente

![Cliente no encontrado](Evidencias/swaggerui/07_clientes_get_404.png)

7. `DELETE /api/clientes/{id}`

![Eliminar cliente](Evidencias/swaggerui/08_clientes_delete.png)

#### Camisetas

1. `GET /api/camisetas`

![Listado de camisetas](Evidencias/swaggerui/09_camisetas_get_listado.png)

2. `POST /api/camisetas` con datos validos

![Crear camiseta valida](Evidencias/swaggerui/10_camisetas_post_valido.png)

3. `GET /api/camisetas/{id}`

![Obtener camiseta por id](Evidencias/swaggerui/11_camisetas_get_id.png)

4. `PUT /api/camisetas/{id}`

![Actualizar camiseta](Evidencias/swaggerui/12_camisetas_put.png)

5. `POST /api/camisetas` duplicado

![Crear camiseta duplicada](Evidencias/swaggerui/13_camisetas_post_duplicado.png)

6. `GET /api/camisetas/{id}` con recurso inexistente

![Camiseta no encontrada](Evidencias/swaggerui/14_camisetas_get_404.png)

7. `DELETE /api/camisetas/{id}`

![Eliminar camiseta](Evidencias/swaggerui/15_camisetas_delete.png)

#### Precio final

1. `GET /api/camisetas/{id}` con cliente preferencial

![Precio final preferencial](Evidencias/swaggerui/16_precio_final_preferencial.png)

2. `GET /api/camisetas/{id}` con cliente regular

![Precio final regular](Evidencias/swaggerui/17_precio_final_regular.png)

3. `GET /api/camisetas/{id}` con `cliente_id` inexistente

![Cliente no encontrado para precio final](Evidencias/swaggerui/18_precio_final_cliente_404.png)

#### Tallas

1. `GET /api/tallas`

![Listado de tallas](Evidencias/swaggerui/19_tallas_get_listado.png)

2. `POST /api/tallas` con datos validos

![Crear talla valida](Evidencias/swaggerui/20_tallas_post_valido.png)

3. `GET /api/tallas/{id}`

![Obtener talla por id](Evidencias/swaggerui/21_tallas_get_id.png)

4. `PUT /api/tallas/{id}`

![Actualizar talla](Evidencias/swaggerui/22_tallas_put.png)

5. `POST /api/tallas` invalido

![Crear talla invalida](Evidencias/swaggerui/23_tallas_post_invalido.png)

6. `DELETE /api/tallas/{id}` inexistente

![Eliminar talla inexistente](Evidencias/swaggerui/24_tallas_delete_404.png)

7. `DELETE /api/tallas/{id}` asociada

![Eliminar talla asociada](Evidencias/swaggerui/29_tallas_delete_409.png)

8. `DELETE /api/tallas/{id}` exitosa

![Eliminar talla exitosa](Evidencias/swaggerui/30_tallas_delete_ok.png)

#### Asociacion camiseta-talla

1. `GET /api/camisetas/{id}/tallas`

![Listado de tallas por camiseta](Evidencias/swaggerui/25_camiseta_tallas_get.png)

2. `POST /api/camisetas/{id}/tallas` valido

![Asociar talla a camiseta](Evidencias/swaggerui/26_camiseta_tallas_post_valido.png)

3. `POST /api/camisetas/{id}/tallas` duplicado

![Asociacion duplicada](Evidencias/swaggerui/27_camiseta_tallas_post_duplicado.png)

4. `DELETE /api/camisetas/{id}/tallas/{tallaId}`

![Desasociar talla de camiseta](Evidencias/swaggerui/28_camiseta_tallas_delete.png)

### Evidencias adicionales de Postman

Dentro del proyecto tambien quedaron copiadas las capturas del flujo en Postman, incluyendo importacion, ejecucion y runner:

- `Evidencias/postman/coleccion-final-postman.png`
- `Evidencias/postman/colecion-postman-importada.png`
- `Evidencias/postman/export-postman-collection.png`
- `Evidencias/postman/import-openapi-postman.png`
- `Evidencias/postman/run-coleccion-final-postman_00.png`
- `Evidencias/postman/run-coleccion-final-postman_01.png`
- `Evidencias/postman/run-postman.png`
- `Evidencias/postman/test-endpoints-postman.png`

## Material complementario de la entrega

- Guia de endpoints: `GUIA_ENDPOINTS_TODOCAMISETAS.md`
- Plan de pruebas Swagger: `PLAN_PRUEBAS_SWAGGER.md`
- Informe breve complementario: `Examen_Cortes_Ain.pdf`
