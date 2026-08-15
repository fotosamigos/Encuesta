#!/bin/sh
set -e

echo "==> Preparando la aplicación..."

# Salvaguarda: si por algún motivo el build no corrió composer install
# (ej. build cache corrupto), lo hacemos aquí antes de seguir — PERO solo
# si composer.json existe. Si no existe, no intentamos ejecutar composer.
if [ ! -d "vendor" ]; then
    if [ -f composer.json ]; then
        echo "==> vendor/ no existe, composer.json detectado — ejecutando composer install..."
        composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction || {
            echo "❌ composer install falló. Revisa composer.json / composer.lock y los logs.";
        }
    else
        echo "==> vendor/ no existe, pero no hay composer.json en la raíz: salto composer install en runtime"
    fi
fi

# Railway inyecta las variables de entorno en runtime (no en el build),
# así que cacheamos config/rutas recién aquí, con los valores reales.
php artisan config:clear

# APP_KEY es obligatoria y NO se genera sola: si cambiara en cada deploy,
# se invalidarían las sesiones y cookies de todos los usuarios activos.
# Falla fuerte y claro en vez de arrancar "roto en silencio".
if [ -z "$APP_KEY" ]; then
    echo "❌ ERROR: falta la variable de entorno APP_KEY."
    echo "   Genérala en tu máquina con: php artisan key:generate --show"
    echo "   y pégala como variable APP_KEY en Railway (Settings → Variables)."
    exit 1
fi

# Esperar a que la base de datos responda (si está configurada) antes de migrar.
# Evita que el container muera inmediatamente si la BD tarda en arrancar.
if [ -n "$DB_HOST" ] && [ -n "$DB_PORT" ] && [ -n "$DB_DATABASE" ]; then
    echo "==> Esperando a que la BD responda en $DB_HOST:$DB_PORT ..."
    MAX_WAIT=60
    until php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); echo 'OK'; } catch(Exception \$e) { exit(1); }" 2>/dev/null; do
        MAX_WAIT=$((MAX_WAIT-1))
        if [ "$MAX_WAIT" -le 0 ]; then
            echo "⚠️ La BD no respondió tras el timeout: saltando migraciones (revisa configuración)."
            break
        fi
        sleep 1
    done
fi

echo "==> Ejecutando migraciones..."
# Intentamos migrar, pero no hacemos que falle el arranque completamente si migrate falla:
if php artisan migrate --force; then
    echo "==> Migraciones ejecutadas correctamente."
else
    echo "⚠️ php artisan migrate falló — la app continuará arrancando para permitir healthchecks y depuración. Revisa logs para el error real."
fi

echo "==> Enlazando storage público..."
php artisan storage:link || true

echo "==> Cacheando configuración y rutas..."
php artisan config:cache || true
php artisan route:cache || true
php artisan view:cache || true

echo "==> Levantando servidor en el puerto ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
