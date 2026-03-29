# Arquitectura de SPX Connect

## Descripción General

SPX Connect es un SDK para Laravel que facilita la integración con el sistema SunPlusXtra. Este documento describe la arquitectura mejorada que implementa el patrón de diseño basado en interfaces (Dependency Inversion Principle).

## Estructura de Directorios

```
src/
├── Contracts/              # Interfaces de todos los servicios
│   ├── AuthServiceInterface.php
│   ├── CacheManagerInterface.php
│   ├── CfdiServiceInterface.php
│   ├── ClientServiceInterface.php
│   ├── EducationLevelServiceInterface.php
│   ├── JournalServiceInterface.php
│   ├── ProductServiceInterface.php
│   ├── SpxClientInterface.php
│   ├── SunPlusAccountServiceInterface.php
│   ├── SunPlusDimensionServiceInterface.php
│   └── TokenManagerInterface.php
├── Services/               # Implementaciones concretas de los servicios
│   ├── AuthService.php
│   ├── CacheManager.php
│   ├── CfdiService.php
│   ├── ClientService.php
│   ├── EducationLevelService.php
│   ├── JournalService.php
│   ├── ProductService.php
│   ├── SunPlusAccountService.php
│   ├── SunPlusDimensionService.php
│   └── TokenManager.php
├── Facades/                # Facade de Laravel
│   └── SpxConnect.php
├── Enums/                  # Enumeraciones
├── BaseApiService.php      # Clase base para servicios API
├── SpxClient.php           # Cliente principal
└── SpxConnectServiceProvider.php  # Service Provider de Laravel
```

## Principios de Diseño

### 1. Dependency Inversion Principle (DIP)

Todos los servicios implementan interfaces, lo que permite:
- **Desacoplamiento**: Las clases dependen de abstracciones, no de implementaciones concretas
- **Testabilidad**: Facilita la creación de mocks y stubs para pruebas unitarias
- **Flexibilidad**: Permite cambiar implementaciones sin afectar el código cliente
- **Mantenibilidad**: El código es más fácil de entender y modificar

### 2. Interface Segregation

Cada servicio tiene su propia interfaz con métodos específicos a su responsabilidad:
- `AuthServiceInterface`: Manejo de autenticación y tokens
- `CacheManagerInterface`: Gestión de caché
- `CfdiServiceInterface`: Operaciones relacionadas con CFDIs
- `ClientServiceInterface`: Búsqueda de clientes
- `ProductServiceInterface`: Búsqueda de productos
- `JournalServiceInterface`: Operaciones de diario contable
- Y más...

## Servicios Principales

### AuthService
Maneja la autenticación con SunPlusXtra:
- Login de usuarios
- Validación de tokens
- Refrescamiento automático de tokens

### CfdiService
Gestión de comprobantes fiscales digitales:
- Timbrado de facturas
- Verificación de UUIDs
- Vinculación con pólizas
- Subida y descarga de XMLs

### JournalService
Operaciones contables:
- Creación de pólizas
- Obtención de tipos de diario
- Generación de reportes

### CacheManager
Sistema de caché con soporte para usuarios:
- Almacenamiento por usuario
- TTL configurable
- Limpieza de caché

## Uso del Sistema

### Inyección de Dependencias

```php
use Unav\SpxConnect\Contracts\AuthServiceInterface;
use Unav\SpxConnect\Contracts\CfdiServiceInterface;

class MiControlador
{
    public function __construct(
        private AuthServiceInterface $auth,
        private CfdiServiceInterface $cfdi
    ) {}

    public function timbrar()
    {
        if ($this->auth->hasValidToken()) {
            $result = $this->cfdi->stamp($xml, $emails);
        }
    }
}
```

### Uso del Facade

```php
use Unav\SpxConnect\Facades\SpxConnect;

// Autenticación
SpxConnect::auth()->login($user, $pass, $email);

// Timbrar factura
$xml = SpxConnect::cfdi()->stamp($xmlContent, $emails);

// Buscar productos
$productos = SpxConnect::products()->search('laptop');

// Buscar clientes
$clientes = SpxConnect::clients()->search('Juan');
```

### Service Provider

El `SpxConnectServiceProvider` registra todas las interfaces con sus implementaciones:

```php
// Automáticamente registrado en config/app.php
'providers' => [
    Unav\SpxConnect\SpxConnectServiceProvider::class,
],
```

## Testing

### Mocking de Servicios

```php
use Unav\SpxConnect\Contracts\CfdiServiceInterface;
use Tests\TestCase;

class CfdiTest extends TestCase
{
    public function test_puede_timbrar_factura()
    {
        // Mock del servicio
        $mock = $this->mock(CfdiServiceInterface::class);
        $mock->shouldReceive('stamp')
             ->once()
             ->andReturn('UUID-123-456');

        $result = $mock->stamp($xml, ['test@example.com']);
        
        $this->assertEquals('UUID-123-456', $result);
    }
}
```

## Beneficios de la Arquitectura

1. **Mantenibilidad**: Código más limpio y organizado
2. **Testabilidad**: Fácil creación de pruebas unitarias con mocks
3. **Escalabilidad**: Agregar nuevos servicios siguiendo el mismo patrón
4. **Documentación**: Las interfaces sirven como contratos claros
5. **IDE Support**: Mejor autocompletado y análisis estático
6. **Flexibilidad**: Cambiar implementaciones sin afectar consumidores

## Convenciones

- Todas las interfaces se ubican en `src/Contracts/`
- Todas las implementaciones se ubican en `src/Services/`
- Los nombres de interfaces terminan en `Interface`
- Cada servicio implementa exactamente una interfaz
- El Service Provider registra todas las bindings de interfaces

## Próximos Pasos

Posibles mejoras futuras:
1. Agregar tests unitarios para cada servicio
2. Implementar circuit breakers para manejo de fallos
3. Agregar retry logic para llamadas API
4. Implementar logging estructurado
5. Agregar métricas y monitoreo
