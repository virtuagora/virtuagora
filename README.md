Virtuagora
==========
** Plataforma web de participacion ciudadana **

¿Que es?
--
Virtuágora es una plataforma web pensada para cualquier ámbito democrático, en la que los ciudadanos puedan expresar su opinión y participar frente a los anuncios y actividades de funcionarios públicos.

Además, se funda dentro de las buenas prácticas del concepto de Gobierno Abierto y aporta el potencial de las TICs para mejorar la comunicación entre representantes y representados. Buscando así, generar un círculo virtuoso de intercambio de opiniones para construir una mejor sociedad.

⚠️ WARNING
--
**No se recomienda levantar esta plataforma en producción.** Debido a las vulnerabilidades conocidas en las dependencias del proyecto, así como en los lenguajes base de programación utilizados, el uso en entornos productivos representa un riesgo de seguridad.

IMPORTANTE
--
Tenemos conocimientos de un problema de dependencias de la librerias que hay definidas en nuestro composer. Se ve que las librerias en `require-dev` ahora piden usar el Twig mas reciente. Les pedimos por favor que si quieren instalar la plataforma utilicen la opcion `--no-dev` de `composer` o sino eliminen las dependencias `require-dev` dentro de `composer.json`

``` $ composer install --no-dev ```

Modernización de Virtuágora
--
Estamos trabajando en modernizar Virtuágora ✌️

Próximamente se proveerá un Dockerfile junto con algunos fixes para asegurar que la plataforma pueda ser utilizada correctamente. A la fecha, el mayor desafío se encuentra en el frontend y los sandboxes modernos que hacen incompatibles algunos Fetch/XHR con el backend, debido a un problema de content negotiation.

Por el momento, en nuestros tiempos libres, estamos trabajando en hacer las adaptaciones necesarias. Agradecemos enormemente la colaboración y el interés de la comunidad, y muy pronto proveeremos fixes para nuestro Virtuágora histórico y, con suerte y con esfuerzo, una versión nueva y moderna para los tiempos venideros ✌️
