# Acceso de solo lectura para Claude Code (toba catedras en huayca)

`deploy/toba-consulta` es lo único que puede ejecutar la clave
`~/.ssh/claude_huayca_toba`: listar los `bases.ini` de Toba (con la clave
tapada), hacer `pg_dump` de una base que figure en ellos, y listar o leer
archivos dentro de `/opt/catedras` (sin `bases.ini` ni archivos
de llaves; tapa llaves privadas y valores de clave/password/secret fuera del
código PHP). No da shell ni escribe nada.

Es una clave distinta de `claude_huayca` (deptos-crub): sshd usa solo la primera
línea de `authorized_keys` que coincide con una clave, así que una clave no
puede estar atada a dos comandos.

## Instalar

Desde la máquina donde está la clave (pide la contraseña de `huayca`):

```bash
# El script, de root, para que no se pueda editar con el usuario huayca
scp deploy/toba-consulta huayca@huayca.crub.uncoma.edu.ar:/tmp/
ssh -t huayca@huayca.crub.uncoma.edu.ar \
  'sudo install -o root -g root -m 755 /tmp/toba-consulta /usr/local/bin/toba-consulta && rm /tmp/toba-consulta'

# La clave, atada a ese único comando
printf 'command="/usr/local/bin/toba-consulta",restrict %s claude_huayca_toba\n' \
  "$(cut -d' ' -f1,2 ~/.ssh/claude_huayca_toba.pub)" |
  ssh huayca@huayca.crub.uncoma.edu.ar 'cat >> ~/.ssh/authorized_keys'
```

## Usar

```bash
K="ssh -i $HOME/.ssh/claude_huayca_toba -o IdentitiesOnly=yes huayca@huayca.crub.uncoma.edu.ar"
$K bases                                  # ver bases.ini (clave tapada)
$K dump toba_3_4 > prod.dump              # base completa
$K dump toba_3_4 huayca > prod.dump       # solo un schema
$K ls instalacion                         # listar (relativo a /opt/catedras)  
$K ver instalacion/instalacion.ini        # leer, con secretos tapados
```

## Actualizar el script

Si cambia `deploy/toba-consulta`, se reinstala igual (la línea de
`authorized_keys` no cambia):

```bash
scp deploy/toba-consulta huayca@huayca.crub.uncoma.edu.ar:/tmp/
ssh -t huayca@huayca.crub.uncoma.edu.ar \
  'sudo install -o root -g root -m 755 /tmp/toba-consulta /usr/local/bin/toba-consulta && rm /tmp/toba-consulta'
```

## Cortar el acceso

Borrar la línea que termina en `claude_huayca_toba` de `~/.ssh/authorized_keys`
en huayca.
