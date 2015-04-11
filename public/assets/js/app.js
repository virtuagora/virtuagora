// Foundation JavaScript
// Documentation can be found at: http://foundation.zurb.com/docs
$(document).foundation();

$( ".bt-logout" ).click(function() {
    $('#form-salir').submit();
});

function parseLinkHeader(header) {
    var links = {};
    if (header) {
        var parts = header.split(', ');
        for (var i=0; i<parts.length; i++) {
            var section = parts[i].split('; ');
            if (section.length == 2) {
                var url = section[0].replace(/<(.*)>/, '$1').trim();
                var name = section[1].replace(/rel="(.*)"/, '$1').trim();
                links[name] = url;
            }
        }
    }
    return links;
}

function refreshPaginator(links) {
    $('#nav-first').hide().off("click");
    $('#nav-prev').hide().off("click");
    $('#nav-next').hide().off("click");
    $('#nav-last').hide().off("click");
    for (var nav in links) {
        $('#nav-'+nav).show().on("click", {url: links[nav]}, startGetRequest);
    }
}
