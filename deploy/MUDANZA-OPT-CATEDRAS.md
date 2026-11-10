# Mudanza de prod: `~/toba-desarrollo` → `/opt/catedras`

Prod pasa a un clon limpio del repo en `/opt/catedras` (al lado de
`/opt/quintral`), con **la misma base** (`toba_3_4`) y **la misma URL**
(`https://huayca.crub.uncoma.edu.ar/catedras/1.0/`): usuarios, Keycloak y
Quintral no notan nada. El desarrollo queda en la máquina de cada uno (copia de
la base con `deploy/toba-consulta dump`). `~/toba-desarrollo` deja de usarse y
se borra cuando prod lleve unas semanas andando: hasta entonces es la vuelta
atrás.

La aplicación no guarda archivos en disco (todo está en la base): no hay nada
que mudar fuera de `instalacion/`.

> **La trampa**: la base es una sola. En cuanto la base tiene registrado el
> contexto de ejecución nuevo (lo hace `regenerar` en el paso 6), el
> `toba-desarrollo` viejo se rompe, porque no tiene ese archivo. Por eso
> `regenerar` va **después** de pasar Apache a `/opt/catedras`, y volver atrás
> incluye limpiar ese campo (paso 8).

Todo se corre en huayca como `huayca`, salvo lo que dice `sudo`. Entrar con
`ssh -o PubkeyAuthentication=no huayca@huayca.crub.uncoma.edu.ar` (sin eso el
agente ofrece la clave de Claude y cae en el comando de solo lectura).

## 0. Antes, desde la máquina de desarrollo

Los commits tienen que estar en `diraacrub/toba` (el clon sale de ahí):

```bash
cd ~/toba && git push origin master
```

## 1. Inventario (no cambia nada)

```bash
cd ~/toba-desarrollo && git status --short && git log --oneline -1
crontab -l 2>/dev/null | grep -n toba; sudo crontab -l 2>/dev/null | grep -n toba
sudo grep -rln "toba-desarrollo" /etc/apache2/ /etc/cron* 2>/dev/null
ls -l /etc/apache2/sites-enabled/
which composer yarn
```

- `git status` tiene que salir vacío: si hay cambios hechos directo en prod,
  primero se commitean desde desarrollo (si no, se pierden con la mudanza).
- Si aparece algún cron con `toba-desarrollo`, se anota para cambiarle la ruta.
- El `grep` en `/etc/apache2` dice **qué archivo incluye hoy el `toba.conf`**
  (un symlink en `sites-available` o un `Include` en otro sitio). Ese es el que
  se cambia en el paso 6.

## 2. Clonar y dependencias

```bash
sudo mkdir /opt/catedras && sudo chown huayca:huayca /opt/catedras
git clone https://github.com/diraacrub/toba /opt/catedras
cd /opt/catedras && git log --oneline -3
composer install --no-dev --no-interaction
```

Si `composer install` termina con error en el paso de `yarn` (no instalado), las
dependencias PHP igual quedaron; los assets JS se completan con:

```bash
cd /opt/catedras && composer run-script post-install-cmd   # con yarn en el PATH
```

o, como último recurso, se copia el `vendor` de prod (mismo `composer.lock`):
`rm -rf vendor && cp -a ~/toba-desarrollo/vendor .`

## 3. Configuración de la instalación

```bash
cp -a ~/toba-desarrollo/instalacion /opt/catedras/instalacion
cd /opt/catedras/instalacion
sed -i 's#/home/huayca/toba-desarrollo#/opt/catedras#g' toba.conf entorno_toba.env i__desarrollo/instancia.ini
grep -rn "toba-desarrollo" --include=*.ini --include=*.conf --include=*.env .   # no tiene que mostrar nada
```

Misma `bases.ini` (misma base), mismas claves de `instalacion.ini`, misma
instancia `desarrollo`.

Sacar el editor y el proyecto de referencia de la parte pública (en prod no van):

```bash
cd /opt/catedras && . instalacion/entorno_toba.env
bin/toba proyecto despublicar -p toba_editor
bin/toba proyecto despublicar -p toba_referencia
grep -n "Alias" instalacion/toba.conf    # quedan /toba_3.4, /toba_3.4_sp, /toba_usuarios/3.4 y /catedras/1.0
```

Login con la cuenta Huayca (ver `deploy/LOGIN-HUAYCA.md`):

```bash
cp deploy/keycloak.ini.dist instalacion/keycloak.ini
read -rsp 'Secreto: ' S && sed -i "s|^client_secret = .*|client_secret = \"$S\"|" instalacion/keycloak.ini && unset S && echo ok
```

(La línea del `read` sola: pega el secreto del cliente `catedras`, Keycloak →
Clients → catedras → Credentials.)

## 4. Permisos

Apache (`www-data`) lee todo y escribe logs y temporales:

```bash
cd /opt/catedras && mkdir -p temp
sudo chgrp -R www-data /opt/catedras
sudo chmod -R g+rX /opt/catedras
sudo chmod -R g+w temp instalacion/i__desarrollo vendor/siu-toba/framework/temp
sudo find temp instalacion/i__desarrollo vendor/siu-toba/framework/temp -type d -exec chmod g+s {} +
chmod 640 instalacion/bases.ini instalacion/keycloak.ini
```

## 5. Verificar sin tocar prod

```bash
cd /opt/catedras && . instalacion/entorno_toba.env
bin/toba instancia info -i desarrollo | tail -12     # conecta a toba_3_4 y lista catedras
```

## 6. El cambio (corte)

Con el archivo que encontró el paso 1. Si es un symlink en `sites-available`
(el nombre real puede ser otro):

```bash
ls -l /etc/apache2/sites-available/ | grep toba          # anotar el nombre y a dónde apunta hoy
sudo ln -sfn /opt/catedras/instalacion/toba.conf /etc/apache2/sites-available/<ese-nombre>.conf
sudo apache2ctl configtest && sudo systemctl reload apache2
```

(Si en cambio es un `Include .../toba-desarrollo/instalacion/toba.conf` dentro
de otro sitio, se cambia esa ruta con `sudoedit` y se hace el mismo
`configtest` + `reload`.)

En seguida, cargar los metadatos del repo en la base (lo de siempre al
desplegar; registra el contexto de ejecución del login nuevo):

```bash
cd /opt/catedras && . instalacion/entorno_toba.env && bin/toba proyecto regenerar -p catedras
```

## 7. Probar

En una ventana privada (Toba guarda la config en la sesión):
`https://huayca.crub.uncoma.edu.ar/catedras/1.0/`

- pantalla Huayca con el aviso de la transición;
- entrar con DNI + clave CRUB, y con usuario + clave de Toba;
- `/toba_editor/3.4` tiene que dar 404.

## 8. Volver atrás (si algo falla)

```bash
sudo ln -sfn /home/huayca/toba-desarrollo/instalacion/toba.conf /etc/apache2/sites-available/<ese-nombre>.conf
sudo apache2ctl configtest && sudo systemctl reload apache2
sudo -u postgres psql -d toba_3_4 -c "UPDATE desarrollo.apex_proyecto SET contexto_ejecucion_subclase = NULL, contexto_ejecucion_subclase_archivo = NULL WHERE proyecto = 'catedras'"
```

El `UPDATE` es necesario: el código viejo no tiene el contexto de ejecución
nuevo y sin él no levanta.

## 9. Después

- **Quintral**: el deploy de `deploy/deploy-produccion.txt` en lenga-crub, para
  que activar/restablecer vuelvan a catedras.
- **Acceso de solo lectura de Claude**: `deploy/toba-consulta` ya apunta a
  `/opt/catedras`; reinstalarlo (ver `deploy/INSTALAR-acceso-claude.md`,
  "Actualizar el script").
- **Desplegar de ahora en más**:
  ```bash
  cd /opt/catedras && git pull && . instalacion/entorno_toba.env && bin/toba proyecto regenerar -p catedras
  ```
- **En unas semanas**: borrar `~/toba-desarrollo` (solo la carpeta: la base
  `toba_3_4` es la de prod).
