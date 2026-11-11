#!/usr/bin/env bash
# Monta una copia de desarrollo de catedras en esta maquina, con la base de prod.
#
# Se corre desde la raiz de un clon recien hecho:
#
#   git clone https://diraacrub@github.com/diraacrub/toba && cd toba
#   deploy/montar-dev.sh ~/instalacion-dev.tgz     # todo (primera vez)
#   deploy/montar-dev.sh --solo-base               # despues: refrescar la base desde prod
#
# instalacion-dev.tgz es la carpeta instalacion/ de una maquina de desarrollo que ya
# anda (malinche), sin logs. Se arma alla con:
#
#   cd ~/toba && tar czf ~/instalacion-dev.tgz --exclude=logs_comandos --exclude='*/logs/*' instalacion
#
# Trae claves (keycloak.ini, instalacion.ini): pasarlo con scp, no por mail ni chat.
#
# La base viene de huayca por SSH con toba-consulta (pg_dump -Fc; la clave de la
# base de prod se lee alla y no viaja). Hace falta poder entrar como huayca.
#
# Pide sudo para: crear el rol de Postgres, ACLs para www-data y el sitio de Apache.

set -euo pipefail

HUAYCA="${HUAYCA:-huayca@huayca.crub.uncoma.edu.ar}"
BASE="toba_3_4"
RAIZ="$(cd "$(dirname "$0")/.." && pwd)"
DUMP="$RAIZ/temp/$BASE.dump"
cd "$RAIZ"

paso() { printf '\n\033[1m== %s\033[0m\n' "$*"; }
falta() { command -v "$1" >/dev/null || { echo "Falta $1 ($2)"; exit 1; }; }

traer_base() {
    paso "Base: dump de prod ($HUAYCA)"
    mkdir -p temp
    ssh "$HUAYCA" "SSH_ORIGINAL_COMMAND='dump $BASE' /usr/local/bin/toba-consulta" > "$DUMP.parcial"
    mv "$DUMP.parcial" "$DUMP"
    ls -lh "$DUMP"

    paso "Base: restaurar en Postgres local"
    if psql -d postgres -Atc "select 1 from pg_database where datname='$BASE'" | grep -q 1; then
        read -rp "La base local $BASE ya existe. Se borra y se vuelve a crear. ¿Seguir? [s/N] " r
        [[ "$r" == [sS] ]] || exit 1
        dropdb "$BASE"
    fi
    createdb -T template0 -E LATIN1 --locale=C "$BASE"
    pg_restore --no-owner --no-privileges -j "$(nproc)" -d "$BASE" "$DUMP"
    psql -d "$BASE" -Atc "select count(*) || ' tablas' from information_schema.tables where table_schema in ('huayca','desarrollo')"
}

regenerar() {
    paso "Metadatos del repo -> base (regenerar)"
    . instalacion/entorno_toba.env
    bin/toba proyecto regenerar -p catedras
}

if [[ "${1:-}" == "--solo-base" ]]; then
    [[ -d instalacion ]] || { echo "No hay instalacion/: correr primero el montaje completo."; exit 1; }
    traer_base
    regenerar
    echo; echo "Listo. Entrar con una sesion nueva (Toba cachea la config en la sesion)."
    exit 0
fi

TGZ="${1:?Uso: deploy/montar-dev.sh instalacion-dev.tgz | --solo-base}"
[[ -f "$TGZ" ]] || { echo "No existe $TGZ"; exit 1; }
[[ ! -e instalacion ]] || { echo "Ya hay instalacion/ aca. Para refrescar la base: --solo-base"; exit 1; }

paso "Requisitos"
falta php "apt install php php-pgsql php-curl php-gd php-mbstring php-xml php-zip php-intl"
falta composer "https://getcomposer.org/download/"
falta psql "apt install postgresql"
falta pg_restore "apt install postgresql-client"
falta apache2ctl "apt install apache2 libapache2-mod-php"
falta setfacl "apt install acl"
php -m | grep -qx pdo_pgsql || { echo "Falta la extension pdo_pgsql (apt install php-pgsql)"; exit 1; }

paso "Dependencias PHP (composer install)"
if [[ -d vendor/siu-toba/framework ]]; then
    echo "vendor/ ya esta, no se reinstala."
else
    composer install --no-interaction || {
        echo "composer fallo (si fue en el paso de yarn, las dependencias PHP igual quedaron:"
        echo "instalar yarn y correr 'composer run-script post-install-cmd')."; exit 1; }
fi

paso "Rol de Postgres para $USER"
if ! psql -d postgres -Atc 'select 1' >/dev/null 2>&1; then
    sudo -u postgres createuser -s "$USER"
fi
read -rsp "Clave para el rol $USER de Postgres local (Toba entra por TCP con usuario y clave): " PGCLAVE; echo
[[ -n "$PGCLAVE" && "$PGCLAVE" != *'"'* ]] || { echo "La clave no puede estar vacia ni tener comillas dobles (va en bases.ini)."; exit 1; }
# Por stdin (con -c psql no reemplaza las variables :"u" y :'c')
sudo -u postgres psql -q -v ON_ERROR_STOP=1 -v u="$USER" -v c="$PGCLAVE" <<'SQL'
ALTER ROLE :"u" WITH SUPERUSER PASSWORD :'c';
SQL

paso "instalacion/ desde $TGZ"
tar xzf "$TGZ"
VIEJA="$(sed -n 's/^path *= *"\(.*\)"/\1/p' instalacion/i__desarrollo/instancia.ini | head -1)"
echo "Ruta de origen: $VIEJA -> $RAIZ"
grep -rlF "$VIEJA" instalacion --include=*.ini --include=*.conf | xargs -r sed -i "s#$VIEJA#$RAIZ#g"
U="$USER" C="$PGCLAVE" awk '
    /^usuario *=/ { print "usuario = \"" ENVIRON["U"] "\""; next }
    /^clave *=/   { print "clave = \"" ENVIRON["C"] "\""; next }
    { print }' instalacion/bases.ini > instalacion/bases.ini.nuevo
mv instalacion/bases.ini.nuevo instalacion/bases.ini
chmod 640 instalacion/bases.ini instalacion/keycloak.ini 2>/dev/null || true
unset PGCLAVE
sed -i "s/^nombre *=.*/nombre = \"local $(hostname -s)\"/" instalacion/instalacion.ini
cat > instalacion/entorno_toba.env <<EOF
export TOBA_DIR=$RAIZ/vendor/siu-toba/framework
export TOBA_INSTANCIA=desarrollo
export TOBA_INSTALACION_DIR=$RAIZ/instalacion
export PATH="\$TOBA_DIR/bin:\$PATH"
EOF
mkdir -p temp www/temp instalacion/i__desarrollo/p__catedras/logs instalacion/logs_comandos

traer_base

paso "Permisos para Apache (www-data, por ACL)"
d="$RAIZ"
while d="$(dirname "$d")"; [[ "$d" != / ]]; do
    sudo setfacl -m u:www-data:x "$d"
done
sudo setfacl -R -m u:www-data:rX -m d:u:www-data:rX "$RAIZ"
for w in instalacion temp www/temp vendor/siu-toba/framework/temp vendor/siu-toba/framework/www/temp; do
    mkdir -p "$w"
    sudo setfacl -R -m u:www-data:rwX -m d:u:www-data:rwX -m d:u:"$USER":rwX "$w"
done

paso "Apache"
sudo ln -sfn "$RAIZ/instalacion/toba.conf" /etc/apache2/sites-available/toba.conf
sudo a2ensite -q toba
sudo a2enmod -q rewrite
sudo apache2ctl configtest
sudo systemctl reload apache2

regenerar

echo
echo "Listo: http://localhost/catedras  (editor: http://localhost/toba_editor/3.4)"
echo "Para bin/toba en otra terminal:  . instalacion/entorno_toba.env"
echo "Para refrescar la base mas adelante:  deploy/montar-dev.sh --solo-base"
