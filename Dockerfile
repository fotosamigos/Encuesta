# ---------- Etapa 1: compilar assets (Tailwind/JS) ----------
FROM node:20-alpine AS assets
WORKDIR /app

# Copiamos todo el repo al contexto de assets para poder detectar si existen
# package.json / vite.config.js sin que COPY falle. Si no existen, evitamos
# romper el build y creamos un public/build vacío para que el COPY final funcione.
COPY . .

# Si hay package.json, instalamos y build. Si no, creamos el directorio de salida.
RUN if [ -f package.json ]; then \
      npm ci && npm run build; \
    else \
      echo "No hay package.json: salto del build de assets" && mkdir -p public/build; \
    fi

# ---------- Etapa 2: app PHP ----------
FROM php:8.4-cli-bookworm AS app
WORKDIR /app

# Extensiones necesarias para Laravel + MySQL + Excel (GD para imágenes)
RUN apt-get update && apt-get install -y \
        git unzip libzip-dev libpng-dev libonig-dev libxml2-dev libjpeg-dev libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring zip gd bcmath exif pcntl \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Copiamos todo el código (pierde la caché fina de composer.json, pero evita
# fallos de COPY cuando composer.json no esté presente). Si composer.json existe,
# instalamos dependencias; si no, lo dejamos para el entrypoint (ver docker/entrypoint.sh).
COPY . .

# Traemos los assets compilados (públicos) desde la etapa assets. Esa etapa
# garantiza que /app/public/build exista (aunque esté vacío) para que esto no falle.
COPY --from=assets /app/public/build ./public/build

# Si hay composer.json, intentamos instalar dependencias de forma optimizada.
# Si no existe, el entrypoint hará composer install en runtime si hace falta.
RUN if [ -f composer.json ]; then \
      composer install --no-dev --no-scripts --no-autoloader --prefer-dist; \
    else \
      echo "No hay composer.json: salto de composer install en build"; \
    fi && \
    if [ -f composer.json ]; then \
      composer dump-autoload --optimize; \
    else \
      echo "No hay composer.json: salto de dump-autoload"; \
    fi && \
    mkdir -p storage/framework/{sessions,views,cache} storage/logs bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache || true

# Copiamos el entrypoint y lo hacemos ejecutable
COPY docker/entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

EXPOSE 8080
ENTRYPOINT ["/entrypoint.sh"]
