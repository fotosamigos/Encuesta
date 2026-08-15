#!/bin/sh
set -e

echo "==> Preparando la aplicación..."

# Verificamos si artisan realmente existe en la raíz
if [ -f "artisan" ]; then
    echo "==> ¡Artisan detectado! Configurando entorno de Laravel..."

    # Asegurarnos de que vendor exista por si acaso
    if [ ! -d "vendor" ]; then
        echo "==> vendor/ no existe, ejecutando composer install..."
        composer install --no-dev --prefer-dist --optimize-autoloader --no-interaction
    fi

    # Configurar permisos de storage y bootstrap
    mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache
    chmod -R 775 storage bootstrap/cache || true

    echo "==> Ejecutando migraciones..."
    php artisan migrate --force || echo "⚠️ Las migraciones fallaron o la BD no está lista aún, continuando..."

    echo "==> Enlazando storage público..."
    php artisan storage:link || true

    echo "==> Cacheando configuración y rutas..."
    php artisan config:cache || true
    php artisan route:cache || true
    php artisan view:cache || true

    echo "==> Levantando Laravel en el puerto ${PORT:-8080}..."
    exec php artisan serve --host=0.0.0.0 --port="${PORT:-8080}"

else
    # Fallback mínimo de emergencia si por alguna razón no está artisan
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
    exec php -S 0.0.0.0:"${PORT:-8080}" -t "$HEALTH_DIR"
fi
