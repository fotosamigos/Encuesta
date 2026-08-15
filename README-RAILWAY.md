# Desplegar en Railway

## ⚠️ Si ya intentaste desplegar y falló: causa más probable

El error típico al desplegar Laravel en Docker es un **choque de versión de PHP**:
tu `composer.lock` se genera en TU máquina con TU versión de PHP local (probablemente
8.4, si instalaste algo reciente), pero si el `Dockerfile` pide una imagen `php:8.2`,
Composer se queja de que faltan requisitos de plataforma y el build truena.

**Ya corregí esto:** el `Dockerfile` ahora usa `php:8.4-cli-bookworm`. Antes de
reconstruir, confirma cuál es tu versión local con `php -v` — si no es 8.4.x, dime
cuál es y ajustamos el Dockerfile a esa versión exacta (deben coincidir sí o sí).

## Checklist antes de hacer push

- [ ] `php -v` local coincide con la versión del `Dockerfile` (por defecto: 8.4)
- [ ] `.gitignore` incluye `vendor/`, `.env`, `bootstrap/cache/*.php`, `node_modules/` (ya viene el `.gitignore` correcto en este paquete — no subas nada que él excluya)
- [ ] No subiste tu `.env` real a GitHub (usa `.env.example` si quieres, pero las variables reales van en Railway → Variables)
- [ ] `APP_KEY` está seteada en Railway (Settings → Variables) — **el entrypoint ahora falla el arranque si falta**, en vez de arrancar "roto en silencio" con una key temporal
- [ ] Corriste `docker build -t encuestas-app .` localmente al menos una vez antes de hacer push, para pescar errores ahí y no esperar a Railway

Ya viene todo listo: `Dockerfile`, `docker/entrypoint.sh`, `railway.json` y un workflow
de GitHub Actions (`.github/workflows/deploy-check.yml`) que valida composer y construye
la imagen en cada push — así te enteras de estos errores en 2 minutos, en GitHub, antes
de que Railway ni se entere.

## 1. Sube el proyecto a GitHub

Railway despliega desde un repo. Si aún no lo tienes en GitHub:

```bash
cd encuestas-app
git init
git add .
git commit -m "Sistema de encuestas"
gh repo create encuestas-app --private --source=. --push
```

(o sube el repo manualmente desde github.com si no usas `gh`)

## 2. Crea el proyecto en Railway

1. Entra a railway.app → **New Project → Deploy from GitHub repo** → selecciona tu repo.
2. Railway va a detectar el `Dockerfile` solo y empezar a construir. **Va a fallar la primera vez** porque falta la base de datos y las variables — es normal, sigue con los pasos de abajo.

## 3. Agrega MySQL

En el mismo proyecto de Railway: **+ New → Database → Add MySQL**.
Railway crea un servicio de MySQL con sus propias variables (`MYSQLHOST`, `MYSQLPORT`, `MYSQLUSER`, `MYSQLPASSWORD`, `MYSQLDATABASE`).

## 4. Configura las variables de entorno de tu servicio web

Ve a tu servicio (el que corre el Dockerfile) → pestaña **Variables** → agrega:

```
APP_NAME=Sistema de Encuestas
APP_ENV=production
APP_DEBUG=false
APP_URL=https://TU-DOMINIO.up.railway.app
APP_TIMEZONE=America/Lima

# Referencian las variables del servicio MySQL que creaste en el paso 3
# (Railway autocompleta estas referencias cuando escribes "${{" en el campo)
DB_CONNECTION=mysql
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}

SESSION_DRIVER=cookie
CACHE_STORE=database
QUEUE_CONNECTION=sync
```

(Uso `cookie` para sesiones porque es cero-configuración — con `database` tendrías que
crear antes la tabla `sessions` con `php artisan session:table`. `sync` para colas porque
este proyecto no usa jobs en background, así que no hace falta nada más.)

### Genera y pega tu APP_KEY (importante)

No dejes que se genere sola en cada deploy — sería un problema con las sesiones/cookies.
Genera una localmente:

```bash
php artisan key:generate --show
```

Copia el valor (`base64:...`) y agrégalo como variable `APP_KEY` en Railway.

## 5. Puerto

Railway inyecta automáticamente la variable `PORT`. Nuestro `entrypoint.sh` ya la usa
(`php artisan serve --port=$PORT`), así que no tienes que tocar nada aquí.

## 6. Redeploy

Con las variables puestas, dispara un nuevo deploy (Railway lo hace solo al guardar
variables, o dale clic a "Redeploy"). El `entrypoint.sh` automáticamente:
- corre las migraciones (`migrate --force`)
- enlaza el storage público
- cachea config/rutas/vistas
- levanta el servidor

## 7. Crea tu usuario administrador

Railway te da una terminal remota. Desde el dashboard del servicio → pestaña **Shell** (o con la CLI: `railway run bash`):

```bash
php artisan db:seed --class="Database\Seeders\AdminUserSeeder"
```

Entra con `admin@encuestas.pe` / `CambiaEstaClave123!` y cámbiala de inmediato desde "Mi perfil".

## ⚠️ Muy importante: almacenamiento de imágenes

El filesystem de Railway **es efímero** — cada vez que redespliegas, cualquier imagen
subida como archivo (portadas de encuesta, imágenes de opciones) **se borra**, porque vive
dentro del contenedor y no en un disco persistente.

Tienes 2 opciones (elige una):

**Opción A — Railway Volume (más simple):**
En tu servicio → **Settings → Volumes → + New Volume** → móntalo en:
```
/app/storage/app/public
```
Así las imágenes sobreviven a los redeploys. Esta es la opción que recomiendo para
empezar, ya tienes todo lo demás listo para usarla tal cual.

**Opción B — Usar solo URLs de imagen (cero configuración extra):**
El sistema ya soporta poner una **URL externa** en vez de subir archivo, tanto para la
portada de la encuesta como para las opciones de pregunta (ej. subes la imagen a imgur,
Google Drive con enlace público, etc. y pegas el link). Si prefieres no lidiar con
Volumes, simplemente usa siempre el campo de URL en vez de "subir archivo".

## Notas finales

- El `Dockerfile` usa `php artisan serve` (servidor de desarrollo de Laravel). Para el
  volumen de tráfico de un sistema de encuestas institucional está perfectamente bien;
  si más adelante quieres algo más robusto (nginx + php-fpm), me avisas y lo cambiamos.
- Cada vez que hagas `git push`, Railway redespliega solo.
- Los logs en vivo están en la pestaña **Deployments** de tu servicio en Railway.
