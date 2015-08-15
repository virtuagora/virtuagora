<?php

class AnonimoTest extends LocalWebTestCase
{
    public function testShwIndex()
    {
        $this->client->get('/portal');
        $this->assertEquals(200, $this->client->response->status());
    }

    public function testRunCrearUsuario()
    {
        $this->client->post('/registro',
                           ['nombre' => 'Prueba',
                            'apellido' => 'Uno',
                            'email' => 'prueba1@dominio.com',
                            'password' => '12345678',
                            'password2' => '12345678']);
        $this->assertEquals(200, $this->client->response->status());
    }

    public function testRunLogin()
    {
        $this->client->post(
            '/login',
            ['email' => 'admin@virtuago.ra',
            'password' => '12345678']
        );
        /*var_dump($this->getSlimInstance()->session->login('admin@virtuago.ra', '12345678'),
                 $this->getSlimInstance()->urlFor('shwPortal'),
                 $this->getSlimInstance()->session->user());



        $env = \Slim\Environment::mock(['REQUEST_METHOD' => 'POST',
                                 'slim.input' => 'email=admin@virtuago.ra&password=12345678',
                                 'PATH_INFO' => '/login',
                                'CONTENT_TYPE' => 'application/x-www-form-urlencoded']);
        $s = $this->getSlimInstance();
        //$s->setName('default');
        $s->post('/bar', function () use ($s) { var_dump('la', $s->request->params()); });
        $s->request = new Slim\Http\Request($env);
        $s->call();
        //var_dump($s->request->params(), \Slim\Slim::getInstance()->request->params(), $s->response->getBody());
        var_dump($s->response->headers);
        //var_dump($this->client->response->getStatus(), $this->client->response->getBody());
        */
        $this->assertEquals(302, $this->client->response->status());
        $this->assertEquals('/portal', $this->client->response->headers->get('Location'));
    }

    public function testShwPropuesta()
    {
        $this->client->get('/propuesta/1');
        $this->assertEquals(200, $this->client->response->status());
    }

    public function testShwPartido()
    {
        $this->client->get('/partido');
        $this->assertEquals(200, $this->client->response->status());
    }

    public function testShwProblematica()
    {
        $this->client->get('/problematica/1');
        $this->assertEquals(200, $this->client->response->status());
    }

    public function testShwDocumento()
    {
        $this->client->get('/documento/1');
        $this->assertEquals(200, $this->client->response->status());
    }

    public function testShwUsuario()
    {
        $this->client->get('/usuario/1');
        $this->assertEquals(200, $this->client->response->status());
    }

    public function testShwOrganismo()
    {
        $this->client->get('/organismo');
        $this->assertEquals(200, $this->client->response->status());
    }

    public function testShwPortal()
    {
        $this->client->get('/');
        $this->assertEquals(200, $this->client->response->status());
    }

    public function testShwNovedad()
    {
        $this->client->get('/novedad/1');
        $this->assertEquals(200, $this->client->response->status());
    }

    public function testShwEvento()
    {
        $this->client->get('/novedad/1');
        $this->assertEquals(200, $this->client->response->status());
    }
}
