# Sistema de Encuestas — Guía de instalación

> ¿Vas a desplegar en **Railway**? Sigue primero esta guía para tener el proyecto
> corriendo en tu compu, y cuando quieras subirlo, ve a `README-RAILWAY.md`.

Este paquete contiene TODO el código de lógica (modelos, migraciones, controladores,
rutas, vistas) para el sistema de encuestas. Como yo no tengo acceso a Packagist/Composer
en mi entorno, tú vas a crear el esqueleto base de Laravel en tu máquina y luego
copiar estos archivos encima. Son ~10 minutos, sigue los pasos en orden.

## 1. Requisitos

- PHP 8.2+
- Composer
- Node.js + npm
- MySQL corriendo localmente (o XAMPP/Laragon, lo que uses)

## 2. Crear el proyecto base

```bash
composer create-project laravel/laravel encuestas-app
cd encuestas-app
composer require laravel/breeze --dev
php artisan breeze:install blade
composer require maatwebsite/excel
npm install
```

Cuando `breeze:install` te pregunte por Pest/PHPUnit, elige cualquiera (no afecta esto).

## 3. Copiar los archivos de este paquete

Copia (sobrescribiendo cuando se te pida) estas carpetas del ZIP que te di
DENTRO de tu carpeta `encuestas-app`:

```
app/Models/                    → sobrescribe (incluye User.php actualizado)
app/Http/Middleware/           → agrega EnsureUserHasRole.php
app/Http/Controllers/          → agrega carpetas Admin/, Encuestador/, Public/, Concerns/
app/Http/Requests/             → agrega StoreSurveyRequest.php
app/Services/                  → carpeta nueva completa
app/Exports/                   → carpeta nueva completa
database/migrations/           → agrega los 8 archivos nuevos (NO borres los que ya
                                  trae Laravel/Breeze, como create_users_table)
database/seeders/               → agrega AdminUserSeeder.php
routes/web.php                 → SOBRESCRIBE el que generó Breeze
resources/views/layouts/app.blade.php → sobrescribe el de Breeze
resources/views/components/    → (si tienes componentes de Breeze que uses en otros lados, no los borres)
resources/views/admin/         → carpeta nueva completa
resources/views/encuestador/   → carpeta nueva completa
resources/views/public/        → carpeta nueva completa
resources/views/results/       → carpeta nueva completa
resources/views/surveys/       → carpeta nueva completa
```

> ⚠️ Importante: Breeze ya trae su propio `resources/views/layouts/app.blade.php` y
> `app/Models/User.php`. Sobrescríbelos con los que te di — ya incluyen todo lo de Breeze
> más los campos/relaciones nuevas.

## 4. Registrar el middleware de roles

Abre `bootstrap/app.php` y agrega el alias `role` dentro de `->withMiddleware(...)`.
Mira el archivo `bootstrap-app-snippet.php` que te dejé — copia exactamente esa parte.

## 5. Configurar la base de datos (MySQL)

En tu `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=encuestas_db
DB_USERNAME=root
DB_PASSWORD=tu_password
```

Crea la base de datos vacía en MySQL:

```sql
CREATE DATABASE encuestas_db CHARACTER SET utf8mb4;
```

También agrega esto en tu `.env` para que todas las horas se guarden en huso horario de Perú:

```
APP_TIMEZONE=America/Lima
```

Y en `config/app.php`, cambia:
```php
'timezone' => env('APP_TIMEZONE', 'UTC'),
```

## 6. Migrar, sembrar el admin, y enlazar storage

```bash
php artisan migrate
php artisan db:seed --class=Database\\Seeders\\AdminUserSeeder
php artisan storage:link
```

Esto crea tu primer usuario administrador:
- **Correo:** admin@encuestas.pe
- **Contraseña:** CambiaEstaClave123!

**Cámbiala apenas entres**, desde `Mi perfil` (arriba a la derecha).

## 7. Levantar el proyecto

```bash
npm run dev      # en una terminal (compila Tailwind/JS)
php artisan serve  # en otra terminal
```

Entra a `http://127.0.0.1:8000`.

- **Persona natural:** entra directo, sin login, y ve las encuestas públicas.
- **Administrador:** inicia sesión con la cuenta de arriba → verás "Estadísticas",
  "Encuestas" y "Encuestadores" en el menú.
- **Encuestador:** el admin crea la cuenta desde `Encuestadores → + Nueva cuenta`.

## Notas importantes

- **Ubicación del respondiente:** el navegador solo pide permiso de ubicación si al
  menos una pregunta de la encuesta lo activa. Por política de los navegadores,
  esto **requiere HTTPS** en producción (en `localhost` funciona sin HTTPS).
- **Hora dual (Perú + otro país):** se resuelve automáticamente vía OpenStreetMap
  Nominatim (gratis, sin API key) al momento de guardar la respuesta. Si el servicio
  no responde, la respuesta se guarda igual, solo sin el dato de hora extra.
- **Colores de encuestador:** el admin puede elegir de una paleta fija o escribir
  un hex libre al crear/editar la cuenta.
- **Contador del mapa (1, 2, 3...):** se calcula automáticamente por encuestador
  y por encuesta — se reinicia solo en cada encuesta nueva, tal como pediste.
- **Tu mapa HTML propio:** cuando lo tengas listo, dime cómo quieres integrarlo
  (¿como una sección nueva del panel de encuestador, solo visible para ciertos
  encuestadores?) y te dejo la ruta y el control de acceso ya armado.
- **Excel:** el botón "Exportar a Excel" en resultados usa Maatwebsite/Excel
  (por eso el `composer require` del paso 2).

## Siguiente paso sugerido

Cuando tengas esto corriendo, prueba el flujo completo: crea una encuesta de prueba
con 2-3 preguntas de distintos tipos, créate una cuenta de encuestador, dale acceso,
y respóndela desde el navegador (o el celular) para ver el mapa funcionando con datos reales.
