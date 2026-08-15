#!/bin/sh
set -e

echo "==> Preparando la aplicación..."

# Salvaguarda: si por algún motivo el build no corrió composer install
# (ej. build cache corrupto), lo hacemos aquí antes de seguir.
if [ ! -d "vendor" ]; then
    echo "==> vendor/ no existe, ejecutando composer install..."
    composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
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

echo "==> Ejecutando migraciones..."
php artisan migrate --force

echo "==> Enlazando storage público..."
php artisan storage:link || true

echo "==> Cacheando configuración y rutas..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Levantando servidor en el puerto ${PORT:-8080}..."
exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"
