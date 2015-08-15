<?php

class JefeTest extends LocalWebTestCase
{
    public function testRunModificarPartido()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/partido/1/modificar',
            ['nombre' => 'Grupo prueba modificado',
            'acronimo' => 'TesTMod',
            'descripcion' => 'Esta es una modificacion de descripcion de prueba.']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals(true, isset($_SESSION['slim.flash']['success']));
    }

    public function testRunEliminarPartido()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post('/partido/1/eliminar');
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals(true, isset($_SESSION['slim.flash']['success']));
    }

    public function testRunAgregarJefe()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/partido/1/cambiar-rol',
            ['idUsu' => '4',
            'jefe' => '1']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals(true, isset($_SESSION['slim.flash']['success']));
    }
}
