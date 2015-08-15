<?php

class UsuarioTest extends LocalWebTestCase
{
    public function testRunLogout()
    {
        $this->client->app->session->login('user@virtuago.ra', '12345678');
        $this->client->post('/logout');
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/', $this->client->response->headers->get('Location'));
    }

    public function testRunVotarPropuesta()
    {
        $this->client->app->session->login('user@virtuago.ra', '12345678');
        $this->client->post(
            '/propuesta/1/votar',
            ['postura' => '1',
            'publico' => 'on']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/propuesta/1', $this->client->response->headers->get('Location'));
    }

    public function testRunCrearPartido()
    {
        $this->client->app->session->login('user@virtuago.ra', '12345678');
        $this->client->post(
            '/partido/crear',
            ['nombre' => 'Grupo prueba',
            'acronimo' => 'TesT',
            'descripcion' => 'Esta es una descripcion de prueba.',
            'fundador' => 'Juan Perez',
            'url' => 'http://www.grupofalso.com',
            'email' => 'admin@grupofalso.com']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/partido', $this->client->response->headers->get('Location'));
    }

    public function testRunUnirsePartido()
    {
        $this->client->app->session->login('delete@virtuago.ra', '12345678');
        $this->client->post('/partido/1/unirse');
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/partido', $this->client->response->headers->get('Location'));
    }

    public function testRunDejarPartido()
    {
        $this->client->app->session->login('extra@virtuago.ra', '12345678');
        $this->client->post('/partido/dejar');
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/partido', $this->client->response->headers->get('Location'));
    }

    public function testRunVotarProblematica()
    {
        $this->client->app->session->login('user@virtuago.ra', '12345678');
        $this->client->post(
            '/problematica/1/votar',
            ['postura' => '1']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/problematica/1', $this->client->response->headers->get('Location'));
    }

    public function testRunComentarContenido()
    {
        $this->client->app->session->login('user@virtuago.ra', '12345678');
        $this->client->post(
            '/comentario/comentar/propuesta/1',
            ['cuerpo' => 'Este es un comentario para testear la funcionalidad']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals(true, isset($_SESSION['slim.flash']['success']));
    }

    public function testRunCambiarClave()
    {
        $this->client->app->session->login('user@virtuago.ra', '12345678');
        $this->client->post(
            '/perfil/cambiar-clave',
            ['pass-old' => '12345678',
            'pass-new' => '87654321',
            'pass-verif' => '87654321']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals(true, isset($_SESSION['slim.flash']['success']));
    }

    public function testRunComentarComentario()
    {
        $this->client->app->session->login('user@virtuago.ra', '12345678');
        $this->client->post(
            '/comentario/comentar/comentario/1',
            ['cuerpo' => 'Esta es una respuesta para testear la funcionalidad']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals(true, isset($_SESSION['slim.flash']['success']));
    }

    public function testRunComentarDocumento()
    {
        $this->client->app->session->login('user@virtuago.ra', '12345678');
        $this->client->post(
            '/comentario/comentar/ParrafoDocumento/1',
            ['cuerpo' => 'Este es un comentario para testear comentar párrafos de documentos.']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals(true, isset($_SESSION['slim.flash']['success']));
    }

    public function testRunModificarUsuario()
    {
        $this->client->app->session->login('user@virtuago.ra', '12345678');
        $this->client->post(
            '/perfil/modificar',
            ['nombre' => 'Nuevo Nombre',
            'apellido' => 'Apellido']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals(true, isset($_SESSION['slim.flash']['success']));
    }

    public function testRunEliminarUsuario()
    {
        $this->client->app->session->login('delete@virtuago.ra', '12345678');
        $this->client->post(
            '/perfil/eliminar',
            ['password' => '12345678']);
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/', $this->client->response->headers->get('Location'));
    }

    public function testRunVotarComentario()
    {
        $this->client->app->session->login('user@virtuago.ra', '12345678');
        $this->client->post(
            '/comentario/1/votar',
            ['valor' => '1']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals(true, isset($_SESSION['slim.flash']['success']));
    }

    public function testShwNotificacion()
    {
        $this->client->app->session->login('user@virtuago.ra', '12345678');
        $this->client->get('/notificacion');
        $this->assertEquals(200, $this->client->response->status());
    }

    public function testRunParticiparEvento()
    {
        $this->client->app->session->login('user@virtuago.ra', '12345678');
        $this->client->post(
            '/evento/1/participar',
            ['presente' => 'on',
            'publico' => 'on']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/evento/1', $this->client->response->headers->get('Location'));
    }

}
