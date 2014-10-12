// Foundation JavaScript
// Documentation can be found at: http://foundation.zurb.com/docs
$(document).foundation({
    abide : {
        patterns: {
            nombre: /(^([A-Za-zñÑáéíóúÁÉÍÓÚ]+)$)|(^([A-Za-zñÑáéíóúÁÉÍÓÚ]+)\s([A-Za-zñÑáéíóúÁÉÍÓÚ]+)$)/,
            apellido: /(^([A-Za-zñÑáéíóúÁÉÍÓÚ]+)$)/,
            //Email Validation as per RFC2822 standards.
            email: /[a-z0-9!#$%&'*+/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&'*+/=?^_`{|}~-]+)*@(?:[a-z0-9](?:[a-z0-9-]*[a-z0-9])?\.)+[a-z0-9](?:[a-z0-9-]*[a-z0-9])?/
        }
    }
});

$(document).ready(function ( ) {
    $('#modalErrors').foundation('reveal', 'open');
});

