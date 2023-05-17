<?php
include('../../../inc/includes.php');
header('Content-Type: text/javascript');
?>
"use strict";

let webDir = '<?php echo  PLUGIN_AUTOFILLFORM_WEB_DIR; ?>';

function getDadosUsuario() {
    $.get(webDir+"/ajax/getDadosUsuario.php", function(data) {
        if (data.message) {
            console.error(data.message);
        } else {
            if(data.cpf)
                $('label:contains("CPF *")').closest('div').find(':input').val(data.cpf).prop("disabled", true);
            if(data.email)
                $('label:contains("E-mail corporativo *")').closest('div').find(':input').val(data.email).prop("disabled", true);
            if(data.departamento)
                $('label:contains("Departamento *")').closest('div').find(':input').val(data.departamento).prop("disabled", true);;
            if(data.chefia)
                $('label:contains("Chefia *")').closest('div').find(':input').val(data.chefia).prop("disabled", true);
            if(data.telefone)
                $('label:contains("Telefone/Ramal *")').closest('div').find(':input').val(data.telefone).prop("disabled", true);
        }
    });
}

$(getDadosUsuario);
