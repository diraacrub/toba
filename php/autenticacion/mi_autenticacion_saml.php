<?php
use SIU\Toba\lib\autenticacion\autenticacion_saml_onelogin;
use SIU\Toba\lib\toba_error_usuario;

class mi_autenticacion_saml extends autenticacion_saml_onelogin
{
    function autenticar($id_usuario, $clave, $datos_adicionales = null)
    {
        // La librería SAML ya se ejecutó. $id_usuario contiene el valor de 'atributo_usuario' (el DNI).
        // Lo usamos directamente para más claridad.
        $dni = $id_usuario;
        
        if (!empty($dni)) {
            
            // Buscamos en la base de datos el 'usuario' que corresponde a ese DNI ('cui').
            $sql = "SELECT usuario FROM apex_usuario WHERE cui = " . toba::db()->quote($dni);
            $datos = toba::db()->consultar_fila($sql);
            
            if ($datos && isset($datos['usuario'])) {
                // ¡Encontramos al usuario! El usuario de Toba es, por ej, 'ibasti'.
                $id_usuario_toba = $datos['usuario'];
                
                // Llamamos al método original de Toba, pero con el ID de usuario correcto.
                // Esto completará el proceso de login.
                return parent::autenticar($id_usuario_toba, $clave, $datos_adicionales);
                
            } else {
                // Si no encontramos un usuario con ese DNI, lanzamos un error claro.
                throw new toba_error_usuario("El DNI '$dni' recibido no corresponde a ningún usuario en el sistema.");
            }
        }
        
        // Si por alguna razón el DNI viene vacío, dejamos que el proceso original falle.
        return parent::autenticar($id_usuario, $clave, $datos_adicionales);
    }
}
?>
