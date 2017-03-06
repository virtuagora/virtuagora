Virtuagora
==========
** Plataforma web de participacion ciudadana **

¿Que es?
--
Virtuágora es una plataforma web pensada para cualquier ámbito democrático, en la que los ciudadanos puedan expresar su opinión y participar frente a los anuncios y actividades de funcionarios públicos.

Además, se funda dentro de las buenas prácticas del concepto de Gobierno Abierto y aporta el potencial de las TICs para mejorar la comunicación entre representantes y representados. Buscando así, generar un círculo virtuoso de intercambio de opiniones para construir una mejor sociedad.

IMPORTANTE
--
Tenemos conocimientos de un problema de dependencias de la librerias que hay definidas en nuestro composer. Se ve que las librerias en `require-dev` ahora piden usar el Twig mas reciente. Les pedimos por favor que si quieren instalar la plataforma utilicen la opcion `--no-dev` de `composer` o sino eliminen las dependencias `require-dev` dentro de `composer.json`

``` $ composer install --no-dev ```
