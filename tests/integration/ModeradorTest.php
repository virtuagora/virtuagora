<?php

class ModeradorTest extends LocalWebTestCase
{
    public function testRunEliminarOrganismo()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post('/admin/organismo/2/eliminar');
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/admin/organismo', $this->client->response->headers->get('Location'));
    }

    public function testRunCrearOrganismo()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/admin/organismo/crear',
            ['nombre' => 'Organismo Testing',
            'descripcion' => 'Organismo creado en una prueba.',
            'cupo' => '5']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/admin/organismo', $this->client->response->headers->get('Location'));
    }

    public function testRunCrearFuncionario()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/admin/organismo/1/funcionario',
            ['entrantes' => '[2]',
            'salientes' => '[]']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/admin/organismo', $this->client->response->headers->get('Location'));
    }

    public function testRunSancionarUsuario()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/admin/sancionar/2',
            ['tipo' => 'Quita',
            'cantidad' => '20',
            'mensaje' => 'Sancionado por 20 puntos.']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals(true, isset($_SESSION['slim.flash']['success']));
    }

    public function testRunModificarOrganismo()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/admin/organismo/1/modificar',
            ['nombre' => 'Organismo Testing Modificado',
            'descripcion' => 'Organismo modificado en una prueba.',
            'cupo' => '10']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/admin/organismo', $this->client->response->headers->get('Location'));
    }

    public function testRunVerificarUsuario()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/admin/verificar',
            ['entrantes' => '[2]']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/admin/verificar', $this->client->response->headers->get('Location'));
    }

    public function testRunModificarAjustes()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/admin/ajustes',
            ['tos' => 'Terminos y condificiones modificados.']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/admin/ajustes', $this->client->response->headers->get('Location'));
    }

    public function testRunModificarPatrulla()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/admin/patrulla/2/modificar',
            ['nombre' => 'Patrulla modificada',
            'descripcion' => 'Descripcion de la patrulla modificada.']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/admin/patrulla', $this->client->response->headers->get('Location'));
    }

    public function testRunCambiarPoderesPatrulla()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/admin/patrulla/2/cambiar-poder',
            ['poderes' => '[2,3]']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/admin/patrulla', $this->client->response->headers->get('Location'));
    }

    public function testRunEliminarPatrulla()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post('/admin/patrulla/3/eliminar');
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/admin/patrulla', $this->client->response->headers->get('Location'));
    }

    public function testRunCrearPatrulla()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/admin/patrulla/crear',
            ['nombre' => 'Patrulla nueva',
            'descripcion' => 'Descripcion de la patrulla nueva.']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/admin/patrulla', $this->client->response->headers->get('Location'));
    }

    public function testRunEliminarModerador()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/admin/patrulla/2/moderador',
            ['salientes' => '[4]']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/admin/patrulla/2', $this->client->response->headers->get('Location'));
    }

    public function testRunCrearModerador()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/admin/moderador/crear',
            ['entrantes' => '[{"usr":"2","pat":"2"}]']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/admin/moderador/crear', $this->client->response->headers->get('Location'));
    }
}
