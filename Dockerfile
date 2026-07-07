FROM dunglas/frankenphp

# On installe les outils PHP dont Symfony a besoin
RUN install-php-extensions \
    pdo_pgsql \
    intl \
    zip \
    opcache

# On va chercher l'outil Composer dans une maison spécialisée et on le ramène ici
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# On copie notre code Symfony dans la maison
COPY . /app

WORKDIR /app

# On installe TOUS les outils (dev + prod) car on est en mode développement
RUN composer install --optimize-autoloader