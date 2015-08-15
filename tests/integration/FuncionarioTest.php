<?php

class FuncionarioTest extends LocalWebTestCase
{
    public function testRunCrearPropuesta()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/propuesta/crear',
            ['titulo' => 'Propuesta de prueba 2',
            'categoria' => '1',
            'cuerpo' => 'Esta es una propuesta para testear la funcionalidad.',
            'tags' => '']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/propuesta/2', $this->client->response->headers->get('Location'));
    }

    public function testRunCrearProblematica()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/problematica/crear',
            ['titulo' => 'Problematica de prueba 2',
            'categoria' => '1',
            'cuerpo' => 'Esta es una problematica para testear la funcionalidad.',
            'tags' => 'test']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/problematica/2', $this->client->response->headers->get('Location'));
    }

    public function testRunCrearDocumento()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/documento/crear',
            ['titulo' => 'Documento de prueba 2',
            'categoria' => '1',
            'descripcion' => 'Esta es una descripcion de prueba.',
            'cuerpo' => 'Este es el primer parrafo de prueba.',
            'tags' => 'test']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/documento/2', $this->client->response->headers->get('Location'));
    }

    public function testRunModificarDocumento()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/documento/1/modificar',
            ['titulo' => 'Documento de prueba 1 modificado',
            'categoria' => '1',
            'descripcion' => 'Esta es una descripcion de prueba modificada.',
            'tags' => 'test']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/documento/1', $this->client->response->headers->get('Location'));
    }

    public function testRunNuevaVersionDocumento()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/documento/1/nueva-version',
            ['cuerpo' => 'Este es el nuevo párrafo del documento.']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/documento/1', $this->client->response->headers->get('Location'));
    }

    public function testRunModificarTagsPropuesta()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/propuesta/1/modificar',
            ['titulo' => 'Propuesta de prueba 1',
            'categoria' => '1',
            'cuerpo' => 'Esta es una propuesta para testear la funcionalidad.',
            'tags' => 'tag1,tag2']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/propuesta/1', $this->client->response->headers->get('Location'));
    }

    public function testRunEliminarDocumento()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post('/documento/1/eliminar');
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals(true, isset($_SESSION['slim.flash']['success']));
    }

    public function testRunEliminarPropuesta()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post('/propuesta/1/eliminar');
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals(true, isset($_SESSION['slim.flash']['success']));
    }

    public function testRunCrearNovedad()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/novedad/crear',
            ['titulo' => 'Novedad de prueba 2',
            'categoria' => '1',
            'cuerpo' => 'Esta es una novedad para testear la funcionalidad.',
            'tags' => 'test']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/novedad/2', $this->client->response->headers->get('Location'));
    }

    public function testRunModificarNovedad()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/novedad/1/modificar',
            ['titulo' => 'Novedad de prueba 1 modificada',
            'categoria' => '1',
            'cuerpo' => 'Esta es una novedad modificada.',
            'tags' => 'test']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/novedad/1', $this->client->response->headers->get('Location'));
    }

    public function testRunEliminarNovedad()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post('/novedad/1/eliminar');
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals(true, isset($_SESSION['slim.flash']['success']));
    }

    public function testRunCrearEvento()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/evento/crear',
            ['titulo' => 'Evento de prueba 2',
            'categoria' => '1',
            'cuerpo' => 'Este es un evento para testear la funcionalidad.',
            'fecha' => '2019-10-10 12:32:30',
            'lugar' => 'Lugar de prueba',
            'tags' => 'test']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/evento/2', $this->client->response->headers->get('Location'));
    }

    public function testRunModificarEvento()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/evento/1/modificar',
            ['titulo' => 'Evento de prueba 1 modificado',
            'categoria' => '1',
            'cuerpo' => 'Este es un evento modificado.',
            'fecha' => '2022-11-11 13:50:40',
            'lugar' => 'Lugar de prueba modificado',
            'tags' => 'test']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/evento/1', $this->client->response->headers->get('Location'));
    }

    public function testRunEliminarEvento()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post('/evento/1/eliminar');
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals(true, isset($_SESSION['slim.flash']['success']));
    }

    public function testRunModificarProblematica()
    {
        $this->client->app->session->login('admin@virtuago.ra', '12345678');
        $this->client->post(
            '/problematica/1/modificar',
            ['titulo' => 'Problematica de prueba 1 modificada',
            'categoria' => '1',
            'cuerpo' => 'Esta es una problematica modificada.',
            'tags' => 'test']
        );
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/problematica/1', $this->client->response->headers->get('Location'));
    }
}
