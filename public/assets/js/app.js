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

// Para porcentajes
// foo([13.626332, 47.989636, 9.596008, 28.788024], 100) // => [48, 29, 14, 9]
$.fn.showResultadosPropuesta = function (l, target) {
    var $sum = _.reduce(l, function(memo, num){ return memo + num; }, 0);
    if ($sum > 0) {
        var $off = target - _.reduce(l, function(acc, x) { return acc + Math.round(x) }, 0);
        var $porcentajes = _.chain(l).
        //sortBy(function(x) { return Math.round(x) - x }).
        map(function(x, i) { return Math.round(x) + ($off > i) - (i >= (l.length + $off)) }).
        value();
        $("#porc_favor").html($porcentajes[0] + "%");
        $("#porc_neutro").html($porcentajes[1] + "%");
        $("#porc_contra").html($porcentajes[2] + "%");
        $("#barra_favor").css("width",($porcentajes[0]).toString() + "%");
        $("#barra_neutro").css("width",($porcentajes[1]).toString() + "%");
        $("#barra_contra").css("width",($porcentajes[2]).toString() + "%");
    }
    $("#propuesta-resultado").show(500);
}

$.fn.hideResultadosPropuesta = function (){
    $("#propuesta-resultado").hide(500);
}
