<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

require_once "accesoDatos.php";
require_once "autentificadora.php";

class Usuario
{
    public $id;
    public string $correo;
    public string $clave;
    public string $nombre;
    public string $apellido;
    public string $perfil;
    public string $foto;

    /*public function __construct($id = NULL,string $correo = "", string $clave = "",string $nombre = "",string $apellido = "",string $perfil = "",
    string $foto = "",)
    {
        $this->id = $id;
        $this->correo = $correo;
        $this->clave = $clave;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->perfil = $perfil;
        $this->foto = $foto;
    }*/

    public function AgregarUno(Request $request, Response $response, array $args): Response
    {
        $jsonParam = $request->getParsedBody();
        $retorno = new stdClass();
        $retorno->exito = false;
        $retorno->mensaje = "Error al agregar el usuario.";
        $retorno->status = 418;

        if($jsonParam['usuario'] != NULL)
        //if($jsonParam != NULL)
        {
            $obj = json_decode($jsonParam["usuario"]);

            $usuario = new Usuario();
            $usuario->correo = $obj->correo;
            $usuario->clave = $obj->clave;
            $usuario->nombre = $obj->nombre;
            $usuario->apellido = $obj->apellido;
            $usuario->perfil = $obj->perfil;
            // mandado como json

            //CONSULTAR EL ULTIMO ID PARA MODIFICAR EL PATH DE LA FOTO
            // mandado parametro a parametro
            //$usuario->correo = $jsonParam['correo'];
            //$usuario->clave = $jsonParam['clave'];
            //$usuario->nombre = $jsonParam['nombre'];
            //$usuario->apellido = $jsonParam['apellido'];
            //$usuario->perfil = $jsonParam['perfil'];
            //$id_agregado = $usuario->AgregarUsuario();
            //$usuario->id = $id_agregado;

            $archivos = $request->getUploadedFiles();
            $destino = __DIR__ ."./fotos/";

            $nombreAnterior = $archivos['foto']->getClientFilename();
            $extension = explode(".", $nombreAnterior);
            $extension = array_reverse($extension);

            $foto = $destino . $usuario->correo . "_3" . "." . $extension[0];
            $archivos['foto']->moveTo($foto);
            $usuario->foto = $foto;
        }

        if($usuario->AgregarUsuario()/*$usuario->ModificarUsuario()*/)//agregar el modificar usuario)
        {
            $retorno->exito = true;
            $retorno->mensaje = "El usuario se agrego con exito.";
            $retorno->status = 200;
        }

        echo $retorno->mensaje;

        $newResponse = $response->withStatus($retorno->status);
        $newResponse->getBody()->write(json_encode($retorno));
        return $newResponse->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos(Request $request, Response $response, array $args): Response
    {
        $retorno = new stdClass();
        $retorno->exito = false;
        $retorno->mensaje = "Error, no se encontraron usuario.";
        $retorno->dato = "{}";
        $retorno->status = 424;

        $usuarios = Usuario::TraerUsuarios();

        if(count($usuarios))
        {
            $retorno->exito = true;
            $retorno->mensaje = "Se encontraron 1 o mas usuarios.";
            $retorno->dato = json_encode($usuarios);
            $retorno->status = 200;
        }

        $newResponse = $response->withStatus($retorno->status);
        $newResponse->getBody()->write(json_encode($retorno));
        return $newResponse->withHeader('Content-Type', 'application/json');
    }

    public function VerificarUsuario(Request $request, Response $response, array $args): Response
    {
        $jsonParam = $request->getParsedBody();
        $retorno = new stdClass();
        $retorno->exito = false;
        $retorno->mensaje = "Error al verificar usuario.";
        $retorno->jwt = NULL;
        $retorno->status = 418;

        if($jsonParam['user'] != NULL)
        {
            $obj = json_decode($jsonParam['user']);
            if($usuario = Usuario::TraerUsuario($obj))
            {
                $jwt = new Usuario();
                $jwt->id = $usuario->id;
                $jwt->correo = $usuario->correo;
                $jwt->nombre = $usuario->nombre;
                $jwt->apellido = $usuario->apellido;
                $jwt->perfil = $usuario->perfil;
                $jwt->foto = $usuario->foto;

                $retorno = new stdClass();
                $retorno->exito = true;
                $retorno->mensaje = "Se ha verificado al usuario con exito.";
                $retorno->jwt = Autentificadora::crearJWT($jwt, 30);
                $retorno->status = 200;
            }

            $newResponse = $response->withStatus($retorno->status);
            $newResponse->getBody()->write(json_encode($retorno));
            return $newResponse->withHeader('Content-Type', 'application/json');
        }
    }

    public function VerificarJWT(Request $request, Response $response, array $args): Response
    {
        $retorno = new stdClass();

        if (isset($request->getHeader("token")[0])) {
            $token = $request->getHeader("token")[0];

            $obj = Autentificadora::verificarJWT($token);

            $retorno->mensaje = $obj->mensaje;
            $retorno->status = $obj->status;
        }

        $response = $response->withStatus($retorno->status);
        $response->getBody()->write(json_encode($retorno));
        return $response->withHeader('Content-Type', 'application/json');
    }

//*********************************************************************************************//
/* FIN - AGREGO FUNCIONES PARA SLIM */
//*********************************************************************************************//

    public function AgregarUsuario()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta = $objetoAccesoDato->retornarConsulta("INSERT into usuarios (correo, clave, nombre, apellido, perfil, foto)
        values(:correo, :clave, :nombre, :apellido, :perfil, :foto)");
        $consulta->bindValue(':correo', $this->correo, PDO::PARAM_STR);
        $consulta->bindValue(':clave', $this->clave, PDO::PARAM_STR);
        $consulta->bindValue(':nombre', $this->nombre, PDO::PARAM_STR);
        $consulta->bindValue(':apellido', $this->apellido, PDO::PARAM_STR);
        $consulta->bindValue(':perfil', $this->perfil, PDO::PARAM_STR);
        $consulta->bindValue(':foto', $this->foto, PDO::PARAM_STR);
        $consulta->execute();		
        return $objetoAccesoDato->retornarUltimoIdInsertado();
    }

    public static function TraerUsuarios()
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->retornarConsulta("SELECT * FROM usuarios");
		$consulta->execute();			
		return $consulta->fetchAll(PDO::FETCH_CLASS, "Usuario");		
	}

    public static function TraerUsuario($obj) 
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->retornarConsulta("SELECT * FROM usuarios 
        WHERE correo = :correo AND clave = :clave");
        $consulta->bindValue(":correo", $obj->correo, PDO::PARAM_STR);
        $consulta->bindValue(":clave", $obj->clave, PDO::PARAM_STR);
		$consulta->execute();
		$usuario= $consulta->fetchObject('Usuario');
		return $usuario;		
	}

    public static function TraerCorreo($correo) 
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->retornarConsulta("SELECT * FROM usuarios 
        WHERE correo = :correo");
        $consulta->bindValue(":correo", $correo, PDO::PARAM_STR);
		$consulta->execute();
		$usuario= $consulta->fetchObject('Usuario');
		return $usuario;		
	}
}