# Login de catedras con la cuenta Huayca

Cátedras usa la **entrada única Huayca**, igual que Quintral y deptos. La
contraseña de la cuenta Huayca (Keycloak, realm `CRUB`) no se escribe en
cátedras: el botón **"Entrar con cuenta Huayca"** manda a la pantalla de
Keycloak, y Keycloak devuelve a la persona ya identificada. Como Keycloak
guarda su propia sesión en el navegador, quien ya entró a Quintral o a deptos
vuelve sin escribir nada, y al revés.

Toba abre la sesión con el **legajo**: el DNI (username de Keycloak) se busca en
`apex_usuario.ciu` y el usuario de sesión es `apex_usuario.usuario`.

## Piezas

| Qué | Dónde | Viaja con git |
|---|---|---|
| Ida y vuelta a Keycloak (OIDC, código con PKCE), cierre de sesión | `php/extension_toba/catedras_huayca_sso.php` | sí |
| Autenticación: deja pasar lo que validó Keycloak; legajo + clave de Toba | `php/extension_toba/catedras_autenticacion_keycloak.php` | sí |
| Llama a la entrada única desde la pantalla de login; etiqueta "Legajo" | `php/login/ci_login.php` | sí |
| Pantalla: botón Huayca, y debajo legajo y clave en la transición | `php/login/pant_login.php` | sí |
| Cruce DNI ↔ legajo | `php/extension_toba/catedras_usuarios.php` | sí |
| Alternativa SAML (no se usa) | `php/extension_toba/catedras_autenticacion_saml.php` | sí |
| Elige el método según la config | `php/extension_toba/catedras_contexto_ejecucion.php` | sí |
| Contexto de ejecución registrado en el proyecto | `apex_proyecto` (base) / `metadatos/tablas/apex_proyecto.sql` | **no** (es la base) |
| URL, realm, cliente, secreto, `url_sistema` | `instalacion/keycloak.ini` (plantilla: `deploy/keycloak.ini.dist`) | **no** |

Nada de esto toca `vendor/`: sobrevive a `composer install`.

## Cómo funciona

`ci_login::ini()` llama a `catedras_huayca_sso::procesar()` en cada pedido a la
pantalla de login:

| Pedido | Qué pasa |
|---|---|
| `?huayca=entrar` (el botón) | Redirige a Keycloak. |
| `?huayca=vuelta&code=…&state=…` | Vuelve de Keycloak: canjea el código (con el secreto del cliente), controla el id_token (emisor, destinatario, nonce, vencimiento), busca el legajo por DNI y abre la sesión. |
| `?huayca=salio` | Volvió de cerrar la sesión de Keycloak: muestra "Cerraste la sesión". |
| `?huayca=toba` | Muestra el formulario de usuario y clave de Toba sin redirigir. |
| Sin parámetro, en la transición | Pregunta **en silencio** a Keycloak (`prompt=none`) si ya hay sesión Huayca: si la hay, entra directo; si no, muestra la pantalla. Si Keycloak no contesta (3 s), muestra la pantalla con un aviso. |
| Sin parámetro, después de la transición | Redirige directo a Keycloak. |
| `?fs=1` (recién apretó "Salir") | Si había entrado con la cuenta Huayca, cierra la sesión de Keycloak (vuelve a `?huayca=salio`). Si no, muestra la pantalla sin volver a entrar solo. |

**Salir**: Toba procesa "Salir" antes de instalar la autenticación del proyecto
(`toba_nucleo.php`: `manejador_sesiones()->iniciar()` corre antes que
`conf__inicial()`), así que el `logout()` de la clase no llega a correr. El
cierre de Keycloak lo hace la pantalla de login que viene después (`?fs=1`).
Si la sesión se abrió con la cuenta Huayca, también se cierra la de
Keycloak (así no se vuelve a entrar solo, ni acá ni en Quintral o deptos) y
Keycloak devuelve a `?huayca=salio`. Las sesiones ya abiertas en Quintral y deptos
siguen hasta que vencen. Si se entró con legajo y clave, se sale como siempre.

El estado del pedido (state, nonce, verificador PKCE) y el id_token viven en
`$_SESSION['catedras_huayca']`, fuera de lo de Toba.

## Transición (hasta el 31/01/2027 inclusive)

`exclusivo_desde = "2027-02-01"` en `keycloak.ini` (el primer día solo Huayca).
Hasta el día anterior la pantalla muestra, debajo del botón, el formulario de
**legajo + clave de Toba** con un aviso de la fecha de corte.

- El formulario es para el legajo. Si alguien escribe su DNI, se le dice que use
  el botón: la clave Huayca se escribe en Keycloak para que la sesión quede
  compartida.
- Entrar con legajo y clave no abre sesión Huayca (los otros sistemas la siguen
  pidiendo).

Desde el 01/02/2027 el formulario desaparece y cátedras redirige directo a
Keycloak, sin tocar nada. Los usuarios sin DNI cargado (ej. `toba`) entran por
`url_sistema?huayca=toba` si `permite_login_toba = 1`.

## Errores que ve la persona

- DNI con cuenta Huayca pero sin usuario en cátedras → "Tu cuenta Huayca es
  válida, pero el DNI … no tiene acceso a Cátedras". La sesión de Keycloak queda
  abierta con esa cuenta: la pantalla ofrece "Entrar con otra cuenta Huayca"
  (cierra la de Keycloak).
- DNI cargado en más de un usuario de Toba → aviso para Secretaría Académica.
- La clave equivocada la maneja la pantalla de Keycloak (con su protección
  contra fuerza bruta).
- "Activá tu cuenta" y "¿Olvidaste tu contraseña?" son de Quintral:
  `/quintral/activate?app=catedras` y `/quintral/reset-password?app=catedras`,
  en la pantalla de cátedras y en la de Keycloak (tema `huayca`).

## Llevarlo a producción

El orden importa: si cátedras redirige antes de que Keycloak acepte la vuelta,
Keycloak muestra un error en vez de devolver a la persona, y nadie entra.

1. **Keycloak primero** (desde lenga-crub, con las credenciales de admin de su
   `.env`): `venv/bin/python scripts/keycloak_entrada_unica.py` para ver, y con
   `--aplicar` para hacerlo. Prende *Standard flow* y PKCE en el cliente
   `catedras` y le agrega las vueltas
   `https://huayca.crub.uncoma.edu.ar/catedras/1.0/*` y
   `http://localhost/catedras/*`.
2. En `instalacion/keycloak.ini` de prod, agregar
   `url_sistema = "https://huayca.crub.uncoma.edu.ar/catedras/1.0/"` (ver
   `deploy/keycloak.ini.dist`).
3. `git pull` y regenerar, como siempre.
4. Probar con una sesión nueva (borrar la cookie `TOBA_SESSID`): Toba guarda la
   config de la instalación en la sesión.

Una vez que cátedras, Quintral y deptos usen la entrada única, apagar *Direct
access grants* en el cliente `catedras`.

## Keycloak (realm CRUB)

- `catedras`: OIDC confidencial, *Standard flow* con PKCE S256. Valid redirect
  URIs y Valid post logout redirect URIs (`+`) como arriba.
- `http://localhost/catedras/siu-toba-catedras-local` (TOBA-LOCAL): SAML para
  probar la alternativa SAML en local. Copia de TOBA-DEV sin el scope
  `role_list` (con él, Keycloak manda atributos `Role` repetidos y la librería
  SAML rechaza la respuesta).

## Pendiente

- Cierre de sesión en todos los sistemas a la vez (*back-channel logout*): hoy
  salir de uno cierra Keycloak, pero las sesiones ya abiertas en los otros siguen
  hasta que vencen.
