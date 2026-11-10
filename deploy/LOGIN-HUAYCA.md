# Login de catedras con la cuenta Huayca

La persona entra en la pantalla de catedras con su **DNI y la contraseña de su
cuenta Huayca** (Keycloak, realm `CRUB`), como en deptos. No hay redirección a
Keycloak. Toba abre la sesión con el **legajo**: el DNI se busca en
`apex_usuario.ciu` y el usuario de sesión es `apex_usuario.usuario`.

## Piezas

| Qué | Dónde | Viaja con git |
|---|---|---|
| Pantalla con estética Huayca + enlaces activar/recuperar | `php/login/pant_login.php` | sí |
| DNI → legajo antes de `login()`, etiquetas DNI/Contraseña | `php/login/ci_login.php` | sí |
| Validación contra Keycloak (`grant_type=password`) | `php/extension_toba/catedras_autenticacion_keycloak.php` | sí |
| Cruce DNI ↔ legajo | `php/extension_toba/catedras_usuarios.php` | sí |
| Alternativa SAML (redirige a Keycloak) | `php/extension_toba/catedras_autenticacion_saml.php` | sí |
| Elige el método según la config | `php/extension_toba/catedras_contexto_ejecucion.php` | sí |
| Contexto de ejecución registrado en el proyecto | `apex_proyecto` (base) / `metadatos/tablas/apex_proyecto.sql` | **no** (es la base) |
| URL, realm, cliente y secreto | `instalacion/keycloak.ini` (plantilla: `deploy/keycloak.ini.dist`) | **no** |

Nada de esto toca `vendor/`: sobrevive a `composer install`.

## Llevarlo a una instalación (prod)

1. `git pull`.
2. Registrar el contexto de ejecución en la base de la instancia (una vez):
   ```sql
   UPDATE desarrollo.apex_proyecto
      SET contexto_ejecucion_subclase = 'catedras_contexto_ejecucion',
          contexto_ejecucion_subclase_archivo = 'extension_toba/catedras_contexto_ejecucion.php'
    WHERE proyecto = 'catedras';
   ```
   (o desde toba_editor: Proyecto → Contexto de ejecución).
3. `cp deploy/keycloak.ini.dist instalacion/keycloak.ini` y completar
   `client_secret` (Keycloak → Clients → `catedras` → Credentials). Que lo pueda
   leer el usuario de Apache.
4. En `instalacion/instalacion.ini`, `autenticacion` tiene que quedar comentada
   (login común de Toba): con `saml_onelogin` se usa SAML en vez del formulario.
5. Si se usan metadatos compilados, recompilar.

Toba guarda la config de la instalación en la sesión: después de cambiarla,
probar con una sesión nueva (borrar la cookie `TOBA_SESSID`).

## Comportamiento

- DNI con usuario en Toba → se valida en Keycloak y entra con su legajo.
- DNI válido en Keycloak pero sin usuario en catedras → "Tu cuenta Huayca es
  válida, pero el DNI … no tiene acceso a Cátedras".
- DNI o contraseña incorrectos → el mismo mensaje en los dos casos (no revela
  quién tiene cuenta). Los fallos los cuenta Toba como siempre.
- Usuarios sin DNI cargado (ej. `toba`) → clave de Toba, si
  `permite_login_toba = 1`.
- "Activá tu cuenta" y "¿Olvidaste tu contraseña?" llevan a
  `/quintral/activate?app=catedras` y `/quintral/reset-password?app=catedras`.

## Keycloak (realm CRUB)

- `catedras`: OIDC confidencial, solo *Direct access grants*. Es el que usa el
  formulario.
- `http://localhost/catedras/siu-toba-catedras-local` (TOBA-LOCAL): SAML para
  probar la alternativa SAML en local. Copia de TOBA-DEV sin el scope
  `role_list` (con él, Keycloak manda atributos `Role` repetidos y la librería
  SAML rechaza la respuesta).

## Pendiente

- Quintral (lenga-crub) todavía no sabe volver a catedras al terminar de
  activar o restablecer: sumar `catedras` con `home`/`login` en
  `frontend/src/lib/apps.ts`, una fila en `RESET_DESTINOS` y la URL en los
  redirect URIs del cliente de reset. Ver `docs/PLAN_CUENTA_HUAYCA.md` allá.
