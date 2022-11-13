<?php

use Firebase\JWT\JWT;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as ResponseMW;

require_once "autentificadora.php";
require_once "usuario.php";

class MW
{
    public function ValidarCorreoYClave(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";
        $jsonParam = $request->getParsedBody();
        $retorno = new stdClass();
        $retorno->status = 403;
        $retorno->mensaje = "No se paso el JSON.";
        $contenidoAPI = json_encode($retorno);
        $obj = new Usuario();

        if(isset($jsonParam["user"])) 
        {
            $obj = json_decode(($jsonParam["user"]));
        }
        else if(isset($jsonParam["usuario"]))
        {
            $obj = json_decode(($jsonParam["usuario"]));
        }

        if($obj)
        {
            if (isset($obj->correo) && isset($obj->clave)) 
            {
                //esto es lo importante y no entiendo
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
                $api_respuesta = json_decode($contenidoAPI);
                $retorno->status = $api_respuesta->status;
                $retorno->mensaje = $api_respuesta->mensaje;
            }
            else
            {
                $retorno->mensaje = "El correo y/o la clave no estan seteados.";
                $contenidoAPI = json_encode($retorno);
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus($retorno->status);
        $response->getBody()->write($contenidoAPI);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function VerificarVacio(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";
        $jsonParam = $request->getParsedBody();
        $retorno = new stdClass();
        $retorno->status = 409;
        $retorno->mensaje = "El correo y/o la clave estan vacios.";
        $contenidoAPI = json_encode($retorno);
        $obj = new Usuario();

        if (isset($jsonParam["user"])) 
        {
            $obj = json_decode(($jsonParam["user"]));
        }
        else if(isset($jsonParam["usuario"]))
        {
            $obj = json_decode(($jsonParam["usuario"]));
        }

        if($obj)
        {
            if ($obj->correo != "" && $obj->clave != "")  
            {
                //esto es lo importante y no entiendo
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
                $api_respuesta = json_decode($contenidoAPI);
                $retorno->status = $api_respuesta->status;
                $retorno->mensaje = $api_respuesta->mensaje;
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus($retorno->status);
        $response->getBody()->write($contenidoAPI);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function VerificarCorreoYClaveBD(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";
        $jsonParam = $request->getParsedBody();
        $retorno = new stdClass();
        $retorno->status = 403;
        $retorno->mensaje = "El correo y/o la clave no existen en la base de datos.";
        $contenidoAPI = json_encode($retorno);
        $obj = new Usuario();

        if (isset($jsonParam["user"])) 
        {
            $objUser = json_decode(($jsonParam["user"]));
            
            $usuarioUser = Usuario::TraerUsuario($objUser);

            if (isset($usuarioUser->correo) && isset($usuarioUser->clave)) 
            {
                //esto es lo importante y no entiendo
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
                $api_respuesta = json_decode($contenidoAPI);
                $retorno->status = $api_respuesta->status;
                $retorno->mensaje = $api_respuesta->mensaje;
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus($retorno->status);
        $response->getBody()->write($contenidoAPI);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public static function VerificarCorreoDuplicado(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";
        $jsonParam = $request->getParsedBody();
        $retorno = new stdClass();
        $retorno->status = 403;
        $retorno->mensaje = "El correo YA existe en la base de datos.";
        $contenidoAPI = json_encode($retorno);
        $obj = new Usuario();

        if (isset($jsonParam["usuario"])) 
        {
            $obj = json_decode(($jsonParam["usuario"]));

            if (!Usuario::TraerCorreo($obj->correo)) 
            {
                //esto es lo importante y no entiendo
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
                $api_respuesta = json_decode($contenidoAPI);
                $retorno->status = $api_respuesta->status;
                $retorno->mensaje = $api_respuesta->mensaje;
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus($retorno->status);
        $response->getBody()->write($contenidoAPI);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function VerificarPrecioYColor(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";
        $jsonParam = $request->getParsedBody();
        $retorno = new stdClass();
        $retorno->status = 409;
        $retorno->mensaje = "El auto no debe ser azul y debe valer entre 50.000 y 600.000.";
        $contenidoAPI = json_encode($retorno);
        $auto = new Auto();

        if (isset($jsonParam["auto"])) 
        {
            $auto = json_decode(($jsonParam["auto"]));

            if ($auto->color != "azul" && $auto->precio >= 50000 && $auto->precio <= 600000) 
            {
                //esto es lo importante y no entiendo
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
                $api_respuesta = json_decode($contenidoAPI);
                $retorno->status = $api_respuesta->status;
                $retorno->mensaje = $api_respuesta->mensaje;
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus($retorno->status);
        $response->getBody()->write($contenidoAPI);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function VerificarToken(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";
        $retorno = new stdClass();
        $retorno->status = 403;
        $retorno->mensaje = "El Token no es valido.";
        $contenidoAPI = json_encode($retorno);

        if (isset($request->getHeader("token")[0])) 
        {
            $token = $request->getHeader("token")[0];
            $obj = Autentificadora::verificarJWT($token);

            if($obj->status == 200) 
            {
                //esto es lo importante y no entiendo
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
                $api_respuesta = json_decode($contenidoAPI);
                $retorno->status = $api_respuesta->status;
                $retorno->mensaje = $api_respuesta->mensaje;
            }
            else
            {
                $retorno->mensaje = $obj->mensaje;
                $contenidoAPI = json_encode($retorno);
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus($retorno->status);
        $response->getBody()->write($contenidoAPI);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function VerificarPropietario(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";
        $retorno = new stdClass();
        $retorno->propietario = false;
        $retorno->status = 409;
        $retorno->mensaje = "El usuario no es propietario.";
        $contenidoAPI = json_encode($retorno);

        if (isset($request->getHeader("token")[0])) 
        {
            $token = $request->getHeader("token")[0];
            //obtengo los datos del jwt
            $obj = Autentificadora::obtenerPayLoad($token);
            $auto = $obj->payload->data;
            $perfil_usuario = $auto->perfil;

            if($perfil_usuario == "propietario") 
            {
                //esto es lo importante y no entiendo
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
                $api_respuesta = json_decode($contenidoAPI);
                $retorno->status = $api_respuesta->status;
                $retorno->mensaje = $api_respuesta->mensaje;
                $retorno->propietario = true;
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus($retorno->status);
        $response->getBody()->write($contenidoAPI);
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function VerificarEncargado(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";
        $retorno = new stdClass();
        $retorno->propietario = false;
        $retorno->status = 409;
        $retorno->mensaje = "El usuario no es encargado.";
        $contenidoAPI = json_encode($retorno);

        if (isset($request->getHeader("token")[0])) 
        {
            $token = $request->getHeader("token")[0];
            //obtengo los datos del jwt
            $obj = Autentificadora::obtenerPayLoad($token);
            $auto = $obj->payload->data;
            $perfil_usuario = $auto->perfil;

            if($perfil_usuario == "encargado") 
            {
                //esto es lo importante y no entiendo
                $response = $handler->handle($request);
                $contenidoAPI = (string) $response->getBody();
                $api_respuesta = json_decode($contenidoAPI);
                $retorno->status = $api_respuesta->status;
                $retorno->mensaje = $api_respuesta->mensaje;
                $retorno->propietario = true;
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus($retorno->status);
        $response->getBody()->write($contenidoAPI);
        return $response->withHeader('Content-Type', 'application/json');
    }

    //1.- Si el que accede al listado de autos es un ‘encargado’, retorne todos los datos, menos el ID.
    //(clase MW - método de instancia).

    public function TodoMenosID(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";

        if (isset($request->getHeader("token")[0])) 
        {
            $token = $request->getHeader("token")[0];

            $datos_token = Autentificadora::obtenerPayLoad($token);
            $usuario_token = $datos_token->payload->data;
            $perfil_usuario = $usuario_token->perfil;

            $response = $handler->handle($request);
            $contenidoAPI = (string) $response->getBody();

            if ($perfil_usuario == "encargado") 
            {
                $api_respuesta = json_decode($contenidoAPI);
                $array_autos = json_decode($api_respuesta->dato);

                foreach ($array_autos as $auto) 
                {
                    unset($auto->id);
                }

                $contenidoAPI = json_encode($array_autos);
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus(200);
        $response->getBody()->write($contenidoAPI);
        return $response->withHeader('Content-Type', 'application/json');
    }

    //2.- Si es un ‘empleado’, muestre la cantidad de colores (distintos) que se tiene. (clase MW -
    //método de instancia).
    public function CantidadColores(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";

        if (isset($request->getHeader("token")[0])) 
        {
            $token = $request->getHeader("token")[0];

            $datos_token = Autentificadora::obtenerPayLoad($token);
            $usuario_token = $datos_token->payload->data;
            $perfil_usuario = $usuario_token->perfil;

            $response = $handler->handle($request);
            $contenidoAPI = (string) $response->getBody();

            if ($perfil_usuario == "empleado") 
            {
                $api_respuesta = json_decode($contenidoAPI);
                $array_autos = json_decode($api_respuesta->dato);

                $colores = [];

                foreach ($array_autos as $item) 
                {
                    //falta diferenciar que sean solo colores distintos
                    array_push($colores, $item->color);
                }

                $cantColores = array_count_values($colores);

                $obj_respuesta = new stdClass();
                $obj_respuesta->mensaje = "Hay " . count($cantColores) . " colores distintos en el listado de autos.";
                $obj_respuesta->colores = $cantColores;

                $contenidoAPI = json_encode($obj_respuesta);
            }
        }
        
        $response = new ResponseMW();
        $response = $response->withStatus(200);
        $response->getBody()->write($contenidoAPI);
        return $response->withHeader('Content-Type', 'application/json');
    }

    //3.- Si es un ‘propietario’, muestre todos los datos de los autos (si el ID está vacío o indefinido) o
    //el auto (cuyo ID fue pasado como parámetro). (clase MW - método de clase).

    public function MostrarTodo(Request $request, RequestHandler $handler): ResponseMW
    {
        $contenidoAPI = "";
        $id = isset($request->getHeader("id_auto")[0]) ? $request->getHeader("id_auto")[0] : null;

        if (isset($request->getHeader("token")[0])) 
        {
            $token = $request->getHeader("token")[0];

            $datos_token = Autentificadora::obtenerPayLoad($token);
            $usuario_token = $datos_token->payload->data;
            $perfil_usuario = $usuario_token->perfil;

            $response = $handler->handle($request);
            $contenidoAPI = (string) $response->getBody();

            if ($perfil_usuario == "propietario") 
            {
                $api_respuesta = json_decode($contenidoAPI);
                $array_autos = json_decode($api_respuesta->dato);

                if ($id != null) 
                {
                    foreach ($array_autos as $auto) 
                    {
                        if ($auto->id == $id) 
                        {
                            $array_autos = $auto; // el array pasa a ser un solo obj json
                            break;
                        }
                    }
                }

                $contenidoAPI = json_encode($array_autos);
            }
        }

        $response = new ResponseMW();
        $response = $response->withStatus(200);
        $response->getBody()->write($contenidoAPI);
        return $response->withHeader('Content-Type', 'application/json');
    }
}