# [1.2.0](https://github.com/Universidad-de-Navojoa-AC/spx-connect/compare/v1.1.1...v1.2.0) (2025-11-16)


### Features

* **AuthService:** Añadir el parámetro userId a los métodos TokenManager para el almacenamiento en caché específico del usuario. ([90cfbf4](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/90cfbf4bf6826617578da868222bc844da67fc8f))
* **BaseApiService:** Añadir la propiedad userId y el método setter para la gestión de tokens. ([b219605](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/b2196057ff9ba5486b084ab34f49fa0f62bdfb9f))
* **TokenManager:** Añadir el parámetro userId a los métodos token y credentials para el almacenamiento en caché específico del usuario. ([0b036a0](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/0b036a0213ef2472db27d26ae033984d16bc4c64))

## [1.1.1](https://github.com/Universidad-de-Navojoa-AC/spx-connect/compare/v1.1.0...v1.1.1) (2025-11-06)


### Bug Fixes

* **api:** Renombrar propiedad 'fileType' a 'printFormat' en el payload para la API del downloadJournalFile ([fcde435](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/fcde43576eda82fe70b95b85cbfc35134f65647e))

# [1.1.0](https://github.com/Universidad-de-Navojoa-AC/spx-connect/compare/v1.0.0...v1.1.0) (2025-10-29)


### Features

* **api:** Agregar método para obtener los tipos de diarios ([d5e0965](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/d5e096553c2a41c1b243a4eb3283355ac8cbf99d))

# 1.0.0 (2025-10-29)


### Bug Fixes

* corrección en método educationLevels() que causaba loop ([a5d1996](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/a5d19967df6b907c82d6641846690f3a40368f84))
* Corregir la ruta de la API para obtener clientes ([f93d88e](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/f93d88e952733cfa3142e3abd93a73d872587e5d))
* Corregir la ruta de la solicitud de impresión de 'journal/file' a 'journal/print' ([fbdffc0](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/fbdffc02d251f0f4ca094eae12f80ec7160b3e20))
* Corregir la ruta de la solicitud de impresión de 'journal/file' a 'journal/print' ([00a5857](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/00a5857ebda8a1a3654fdb27fe5eda96c32bc0f2))
* Devuelve el objeto de respuesta completo en lugar de solo los datos JSON. ([30a7bde](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/30a7bde1ae751f1e88904e1f0953e96fc96e98c6))
* se añadió la propiedad EducationLevelService en el cliente ([055334c](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/055334c67445a200a0a84a23d7e82f072cfb2313))
* se corrigió endpoint de método getAll() en `EducationLevelService` ([579dafb](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/579dafbe6e27944714d257d42d5639db8f7d0c27))


### Features

* Agregar enum JournalFileType con etiquetas asociadas ([0834fba](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/0834fba83932c78d33b55177d23b94a60968348f))
* Agregar interfaz HasLabel para obtener etiquetas ([8d54560](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/8d54560a1448a3b7639de916062abf989a945e99))
* Agregar JournalService a SpxClient y SpxClientInterface ([fc61611](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/fc61611af294d711491625a0c2d2bb8fa7f55b41))
* Agregar método estático para gestionar clientes en SpxConnect ([39b332b](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/39b332bc7332b2a2952de807e38089223ed84887))
* Agregar método para gestionar clientes en SpxClientInterface ([0d5ccb7](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/0d5ccb77c8ffe7973afede55f783b3c94750dfda))
* Agregar método para gestionar clientes en SpxClientInterface ([bf4dd2c](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/bf4dd2cde9582069dbbaa316a87cc426c26b11a7))
* Agregar método para gestionar clientes en SpxClientInterface ([0529f1a](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/0529f1a87ec378425149e19b5914e50714fd7245))
* Agregar publicación del archivo de configuración spxconnect.php desde el service provider ([7afe9b2](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/7afe9b2f71013dcc15741b121f9f1d0da41382db))
* Implementación de CacheManager para respuestas cacheadas por usuario ([65059f2](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/65059f2f88aac977c8702a8ec776ea8276c0a90c))
* Implementar JournalService para gestionar entradas y descarga de archivos del diario ([ee23de5](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/ee23de55387a2995248feb67a19ff1825f6ea412))
* Implementar la gestión de tokens y credenciales mediante cifrado y almacenamiento en caché ([d2fa46f](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/d2fa46f4af6fe3cf9a22a46e792e830a74f36a29))
* se añadió el servicio para listado de niveles educativos `EducationLevelService` ([b5111b3](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/b5111b30e05e4d39573927e8e2e3ce2e92a7adef))
* se añadió el servicio para listado y búsqueda de productos `ProductService` ([15ef43d](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/15ef43d517d717451873997499d2176ff5426f1f))
* se añadió el servicio para listado y búsqueda de productos `ProductService` ([3c02343](https://github.com/Universidad-de-Navojoa-AC/spx-connect/commit/3c023432c61cf4025255359d41f01ac7da3c818f))

# Changelog
