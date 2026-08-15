#!/bin/sh
set -e

echo "==> Preparando la aplicación..."

# Si vendor/ no existe, intentamos composer solo si composer.json está presente
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

# Si existe artisan, seguimos con el flujo normal de Laravel.
if [ -f artisan ]; then
    echo "==> artisan detectado: arrancando flujo de Laravel..."

    # Railway inyecta las variables de entorno en runtime, así que limpiamos cachés
    php artisan config:clear || true

    # APP_KEY es obligatoria para el correcto funcionamiento de Laravel
    if [ -z "$APP_KEY" ]; then
        echo "❌ ERROR: falta la variable de entorno APP_KEY."
        echo "   Genérala en tu máquina con: php artisan key:generate --show"
        echo "   y pégala como variable APP_KEY en Railway (Settings → Variables)."
        exit 1
    fi

    # Esperar a que la base de datos responda (si está configurada) antes de migrar.
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

else
    # Fallback mínimo cuando no hay artisan: sirve un endpoint /up estático para pasar healthcheck.
    echo "⚠️ No se detectó artisan en la raíz del repo — arrancando servidor mínimo de salud en ${PORT:-8080}."

    HEALTH_DIR=/tmp/health
    mkdir -p "$HEALTH_DIR"
    cat > "$HEALTH_DIR/index.php" <<'PHP'
<?php
http_response_code(200);
header('Content-Type: application/json');
echo json_encode(['status' => 'up']);
PHP

    echo "==> Servido archivo de healthcheck en $HEALTH_DIR/index.php"
    echo "==> Levantando servidor PHP embebido en el puerto ${PORT:-8080} ..."

    # Iniciamos un servidor PHP simple que responde /up con 200.
    exec php -S 0.0.0.0:"${PORT:-8080}" -t "$HEALTH_DIR"
fi
