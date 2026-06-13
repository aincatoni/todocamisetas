# Plan de Pruebas Swagger - TodoCamisetas

Este documento define el orden recomendado para probar los endpoints en Swagger UI y tomar capturas sin romper dependencias entre casos.

## Base

- Swagger UI: `http://localhost:8080/api/documentation`
- Base URL API: `http://localhost:8080/api`
- Se asume base reiniciada con:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

## Datos base esperados

Despues del seeder deberias tener:

- clientes:
  - `id=1` `90minutos` categoria `Preferencial`
  - `id=2` `tdeportes` categoria `Regular`
- camisetas:
  - `id=1` `CHI-LOC-2025-01`
  - `id=2` `ARG-VIS-2025-01`
- tallas:
  - `id=1` `S`
  - `id=2` `M`
  - `id=3` `L`
  - `id=4` `XL`

## Reglas para las capturas

- en `POST` y `PUT`, tomar 2 capturas:
  - solicitud con body cargado
  - respuesta exitosa o con error
- en `GET` y `DELETE`, una captura suele bastar
- si Swagger muestra codigo HTTP y body en la misma vista, aprovéchalo

## Orden recomendado

## 1. Health

### 1.1 `GET /health`

Objetivo: verificar que la API esta operativa.

Captura sugerida:

- `01_health.png`

## 2. Clientes

### 2.1 `GET /clientes`

Objetivo: mostrar los clientes del seeder.

Captura sugerida:

- `02_clientes_get_listado.png`

### 2.2 `POST /clientes`

Objetivo: crear un cliente nuevo para usarlo luego en `GET`, `PUT` y `DELETE`.

Body:

```json
{
  "nombre_comercial": "deportes_total",
  "rut": "77111222-3",
  "direccion": "Av. Grecia 123, Nunoa",
  "categoria": "Preferencial",
  "contacto_nombre": "Ana Torres",
  "contacto_email": "compras@deportestotal.cl",
  "porcentaje_oferta": 15
}
```

Guardar el `id` retornado. En este plan se llamara `cliente_nuevo_id`.

Capturas sugeridas:

- `03_clientes_post_valido_00.png`
- `03_clientes_post_valido_01.png`

### 2.3 `GET /clientes/{id}`

Objetivo: consultar el cliente recien creado.

Usar:

- `id = cliente_nuevo_id`

Captura sugerida:

- `04_clientes_get_id.png`

### 2.4 `PUT /clientes/{id}`

Objetivo: actualizar el cliente creado en 2.2.

Usar:

- `id = cliente_nuevo_id`

Body:

```json
{
  "direccion": "Av. Grecia 999, Nunoa",
  "categoria": "Regular",
  "contacto_nombre": "Ana Maria Torres"
}
```

Capturas sugeridas:

- `05_clientes_put_00.png`
- `05_clientes_put_01.png`

### 2.4.1 `GET /clientes/{id}/camisetas`

Objetivo: comprobar el endpoint de camisetas asociadas por cliente.

Usar:

- `id = cliente_nuevo_id`

Captura sugerida:

- alternativa vacia: `05b_clientes_camisetas_get_vacio.png`
- alternativa con datos: `12b_clientes_camisetas_get_con_datos.png`

### 2.5 `POST /clientes` invalido

Objetivo: evidenciar validacion `422`.

Body:

```json
{
  "nombre_comercial": "",
  "rut": "77111222-3"
}
```

Captura sugerida:

- `06_clientes_post_invalido.png`

### 2.6 `GET /clientes/{id}` inexistente

Objetivo: evidenciar error `404`.

Usar:

- `id = 999`

Captura sugerida:

- `07_clientes_get_404.png`

### 2.7 `DELETE /clientes/{id}`

Objetivo: eliminar el cliente creado en 2.2.

Usar:

- `id = cliente_nuevo_id`

Captura sugerida:

- `08_clientes_delete.png`

### 2.8 `DELETE /clientes/{id}` con camisetas asociadas

Objetivo: evidenciar `409` cuando el cliente aun tiene camisetas asociadas.

Prerequisito:

- ejecutar primero `POST /camisetas` de la seccion 3 usando `cliente_id = cliente_nuevo_id`

Usar:

- `id = cliente_nuevo_id`

Captura sugerida:

- `08b_clientes_delete_409_con_camisetas.png`

## 3. Camisetas

### 3.1 `GET /camisetas`

Objetivo: mostrar camisetas base del seeder.

Captura sugerida:

- `09_camisetas_get_listado.png`

### 3.2 `POST /camisetas`

Objetivo: crear una camiseta nueva para usarla luego en `GET`, `PUT` y `DELETE`.

Body:

```json
{
  "titulo": "Camiseta Alternativa 2025",
  "club": "Universidad de Chile",
  "pais": "Chile",
  "tipo": "Alternativa",
  "color": "Azul",
  "precio": 52990,
  "precio_oferta": 47990,
  "detalles": "Version jugador manga corta.",
  "codigo_producto": "UCH-ALT-2025-01",
  "cliente_id": 3
}
```

Guardar el `id` retornado. En este plan se llamara `camiseta_nueva_id`.

Nota:

- si estas siguiendo el flujo completo, reemplaza `cliente_id = 3` por `cliente_nuevo_id`

Capturas sugeridas:

- `10_camisetas_post_valido_00.png`
- `10_camisetas_post_valido_01.png`

### 3.3 `GET /camisetas/{id}`

Objetivo: consultar la camiseta creada en 3.2.

Usar:

- `id = camiseta_nueva_id`

Captura sugerida:

- `11_camisetas_get_id.png`

### 3.4 `PUT /camisetas/{id}`

Objetivo: actualizar la camiseta creada en 3.2.

Usar:

- `id = camiseta_nueva_id`

Body:

```json
{
  "color": "Azul Marino",
  "precio": 54990,
  "precio_oferta": 49990,
  "detalles": "Version jugador manga corta actualizada.",
  "cliente_id": 3
}
```

Capturas sugeridas:

- `12_camisetas_put_00.png`
- `12_camisetas_put_01.png`

### 3.4.1 `GET /clientes/{id}/camisetas`

Objetivo: comprobar que el cliente usado en `POST /camisetas` ya lista la camiseta creada.

Usar:

- `id = cliente_nuevo_id`

Captura sugerida:

- `12b_clientes_camisetas_get_con_datos.png`

### 3.5 `POST /camisetas` duplicado

Objetivo: evidenciar `422` por `codigo_producto` repetido.

Body:

```json
{
  "titulo": "Duplicada",
  "club": "Chile",
  "pais": "Chile",
  "tipo": "Local",
  "color": "Rojo",
  "precio": 1000,
  "codigo_producto": "CHI-LOC-2025-01"
}
```

Captura sugerida:

- `13_camisetas_post_duplicado.png`

### 3.6 `GET /camisetas/{id}` inexistente

Objetivo: evidenciar `404`.

Usar:

- `id = 999`

Captura sugerida:

- `14_camisetas_get_404.png`

### 3.7 `DELETE /camisetas/{id}`

Objetivo: eliminar la camiseta creada en 3.2.

Usar:

- `id = camiseta_nueva_id`

Captura sugerida:

- `15_camisetas_delete.png`

## 4. Regla de negocio `precio_final`

Estas pruebas deben ejecutarse antes de modificar o eliminar los datos del seeder.

### 4.1 `GET /camisetas/{id}` con cliente preferencial

Objetivo: demostrar que `precio_final = precio_oferta`.

Usar:

- `id = 1`
- `cliente_id = 1`

Resultado esperado:

- `cliente_consultado.categoria = Preferencial`
- `precio_final = 39990.00`

Captura sugerida:

- `16_precio_final_preferencial.png`

### 4.2 `GET /camisetas/{id}` con cliente regular

Objetivo: demostrar que `precio_final = precio`.

Usar:

- `id = 1`
- `cliente_id = 2`

Resultado esperado:

- `cliente_consultado.categoria = Regular`
- `precio_final = 45000.00`
- `precio_oferta = 39990.00`

Nota: este caso es mas fuerte si se usa la misma camiseta con oferta del escenario preferencial, porque demuestra que el precio cambia por el tipo de cliente y no por el producto consultado.

Captura sugerida:

- `17_precio_final_regular.png`

### 4.3 `GET /camisetas/{id}` con cliente inexistente

Objetivo: evidenciar `404` por `cliente_id` invalido.

Usar:

- `id = 1`
- `cliente_id = 999`

Captura sugerida:

- `18_precio_final_cliente_404.png`

## 5. Tallas

### 5.1 `GET /tallas`

Objetivo: mostrar tallas base del seeder.

Captura sugerida:

- `19_tallas_get_listado.png`

### 5.2 `POST /tallas`

Objetivo: crear una talla nueva para luego verla, actualizarla y finalmente eliminarla.

Body:

```json
{
  "nombre": "XXL"
}
```

Guardar el `id` retornado. En este plan se llamara `talla_nueva_id`.

Capturas sugeridas:

- `20_tallas_post_valido_00.png`
- `20_tallas_post_valido_01.png`

### 5.3 `GET /tallas/{id}`

Objetivo: consultar la talla creada en 5.2.

Usar:

- `id = talla_nueva_id`

Captura sugerida:

- `21_tallas_get_id.png`

### 5.4 `PUT /tallas/{id}`

Objetivo: actualizar la talla creada en 5.2.

Usar:

- `id = talla_nueva_id`

Body:

```json
{
  "nombre": "XXXL"
}
```

Capturas sugeridas:

- `22_tallas_put_00.png`
- `22_tallas_put_01.png`

### 5.5 `POST /tallas` invalido

Objetivo: evidenciar `422`.

Body:

```json
{}
```

Captura sugerida:

- `23_tallas_post_invalido.png`

### 5.6 `DELETE /tallas/{id}` inexistente

Objetivo: evidenciar `404`.

Usar:

- `id = 999`

Captura sugerida:

- `24_tallas_delete_404.png`

## 6. Asociacion camiseta-talla

### 6.1 `GET /camisetas/{id}/tallas`

Objetivo: listar tallas asociadas a una camiseta del seeder.

Usar:

- `id = 1`

Captura sugerida:

- `25_camiseta_tallas_get.png`

### 6.2 `POST /camisetas/{id}/tallas` valido

Objetivo: asociar a camiseta `1` la talla `1` (`S`), que al inicio no estaba asociada.

Usar:

- `id = 1`

Body:

```json
{
  "talla_id": 1
}
```

Capturas sugeridas:

- `26_camiseta_tallas_post_valido_00.png`
- `26_camiseta_tallas_post_valido_01.png`

### 6.3 `POST /camisetas/{id}/tallas` duplicado

Objetivo: repetir la misma asociacion para evidenciar `409`.

Usar:

- `id = 1`

Body:

```json
{
  "talla_id": 1
}
```

Captura sugerida:

- `27_camiseta_tallas_post_duplicado.png`

### 6.4 `DELETE /camisetas/{id}/tallas/{tallaId}`

Objetivo: desasociar la talla agregada en 6.2 para dejar el estado limpio.

Usar:

- `id = 1`
- `tallaId = 1`

Captura sugerida:

- `28_camiseta_tallas_delete.png`

### 6.5 `DELETE /tallas/{id}` asociada

Objetivo: intentar eliminar una talla que siga asociada a una camiseta y evidenciar `409`.

Usar:

- `id = 2`

Justificacion:

- la talla `2` (`M`) sigue asociada a camisetas del seeder.

Captura sugerida:

- `29_tallas_delete_409.png`

### 6.6 `DELETE /tallas/{id}` exitosa

Objetivo: eliminar la talla creada y actualizada en 5.2 y 5.4, ya que no esta asociada.

Usar:

- `id = talla_nueva_id`

Captura sugerida:

- `30_tallas_delete_ok.png`

## Cierre recomendado

Al terminar las capturas, puedes volver a dejar la base limpia con:

```bash
docker compose exec app php artisan migrate:fresh --seed
```

## Importar Swagger a Postman

Si quieres generar una coleccion base en Postman:

1. abrir `Postman`
2. elegir `Import`
3. usar `Link`
4. pegar:

```text
http://localhost:8080/docs/api-docs.json
```

Luego puedes ordenar la coleccion con el mismo orden de este documento.
