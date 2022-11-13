<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
use \Slim\Routing\RouteCollectorProxy;

require __DIR__ . '/../vendor/autoload.php';
require_once '../scr/usuario.php';
require_once '../scr/auto.php';
require_once '../scr/accesoDatos.php';
require_once '../scr/mw.php';

$app = AppFactory::create();

//EL PRIMER MW EJECUTADO VA ABAJO DEL TODO
$app->post('/usuarios', \Usuario::class . ':AgregarUno')
->add(\MW::class . ":VerificarCorreoDuplicado")
->add(\MW::class . ":VerificarVacio")
->add(\MW::class . ":ValidarCorreoYClave");

$app->get('/', \Usuario::class . ':TraerTodos');

$app->post('/', \Auto::class . ':AgregarUno')
->add(\MW::class . ":VerificarPrecioYColor");

$app->get('/autos', \Auto::class . ':TraerTodos');

//EL PRIMER MW EJECUTADO VA ABAJO DEL TODO
$app->post('/login', \Usuario::class . ':VerificarUsuario')
->add(\MW::class . ":VerificarCorreoYClaveBD")
->add(\MW::class . ":VerificarVacio")
->add(\MW::class . ":ValidarCorreoYClave");

$app->get('/login', \Usuario::class . ':VerificarJWT');

$app->delete('/', \Auto::class . ':EliminarUno')
->add(\MW::class . ":VerificarPropietario")
->add(\MW::class . ":VerificarToken");

$app->put('/', \Auto::class . ':ModificarUno')
->add(\MW::class . ":VerificarEncargado")
->add(\MW::class . ":VerificarToken");

$app->group('/autos', function (RouteCollectorProxy $grupo) 
{
    $grupo->get('/', \Auto::class . ':TraerTodos')
      ->add(\MW::class . ':TodoMenosID')
      ->add(\MW::class . ':CantidadColores')
      ->add(\MW::class . ':MostrarTodo');
});

$app->run();