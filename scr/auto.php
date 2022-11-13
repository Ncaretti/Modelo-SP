<?php
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class Auto
{
    public int $id;
    public string $color;
    public string $marca;
    public int $precio;
    public string $modelo;

    /*public function __construct(int $id = 0,string $color = "", string $marca = "",int $precio = 0,string $modelo = "")
    {
        $this->id = $id;
        $this->color = $color;
        $this->marca = $marca;
        $this->precio = $precio;
        $this->modelo = $modelo;
    }*/

    public function AgregarUno(Request $request, Response $response, array $args): Response
    {
        $jsonParam = $request->getParsedBody();
        $retorno = new stdClass();
        $retorno->exito = false;
        $retorno->mensaje = "Error al agregar el auto.";
        $retorno->status = 418;
        $auto = new Auto();

        if($jsonParam["auto"] != NULL)
        //if($jsonParam != NULL)
        {
            //caso de pasar json
            $obj = json_decode($jsonParam["auto"]);
            $auto->color = $obj->color;
            $auto->marca = $obj->marca;
            $auto->precio = $obj->precio;
            $auto->modelo = $obj->modelo;

            //pasando parametro a parametro
            /*$auto->color = $jsonParam['color'];
            $auto->marca = $jsonParam['marca'];
            $auto->precio = $jsonParam['precio'];
            $auto->modelo = $jsonParam['modelo'];*/
        }

        if($auto->AgregarAuto())
        {
            $retorno->exito = true;
            $retorno->mensaje = "Se agrego con exito el auto.";
            $retorno->status = 200;
        }

        $newResponse = $response->withStatus($retorno->status);
        $newResponse->getBody()->write(json_encode($retorno));
        return $newResponse->withHeader('Content-Type', 'application/json');
    }

    public function TraerTodos(Request $request, Response $response, array $args): Response
    {
        $retorno = new stdClass();
        $retorno->exito = false;
        $retorno->mensaje = "Error, no se encontraron autos.";
        $retorno->dato = "{}";
        $retorno->status = 424;

        $autos = Auto::TraerAutos();

        if(count($autos))
        {
            $retorno->exito = true;
            $retorno->mensaje = "Se encontraron 1 o mas autos.";
            $retorno->dato = json_encode($autos);
            $retorno->status = 200;
        }

        $newResponse = $response->withStatus($retorno->status);
        $newResponse->getBody()->write(json_encode($retorno));
        return $newResponse->withHeader('Content-Type', 'application/json');
    }

    public function EliminarUno(Request $request, Response $response, array $args): Response
    {
        $retorno = new stdClass();
        $retorno->exito = false;
        $retorno->mensaje = "Error, no se pudo eliminar el auto, verificar token y/o id";
        $retorno->status = 418;
        
        if (isset($request->getHeader("token")[0]) && isset($request->getHeader("id_auto")[0]))
        {
            $token = $request->getHeader("token")[0];
            $id = $request->getHeader("id_auto")[0];

            $datos_token = Autentificadora::obtenerPayLoad($token);
            $usuario_token = $datos_token->payload->data;
            $perfil_usuario = $usuario_token->perfil;

            if ($perfil_usuario == "propietario") 
            {
                if(Auto::EliminarAuto($id)) 
                {
                    $retorno->exito = true;
                    $retorno->mensaje = "Se elimino con exito el auto.";
                    $retorno->status = 200;
                } 
            } 
            else 
            {
                $retorno->mensaje = "Usuario no autorizado para realizar la accion. {$usuario_token->nombre} - {$usuario_token->apellido} - {$usuario_token->perfil}";
            }
        }

        $newResponse = $response->withStatus($retorno->status);
        $newResponse->getBody()->write(json_encode($retorno));
        return $newResponse->withHeader('Content-Type', 'application/json');
    }

    public function ModificarUno(Request $request, Response $response, array $args): Response
    {
        $retorno = new stdClass();
        $retorno->exito = false;
        $retorno->mensaje = "Error, no se pudo modificar el auto, verificar token, id y/o auto";
        $retorno->status = 418;
        
        if (isset($request->getHeader("token")[0]) && isset($request->getHeader("id_auto")[0])
            && isset($request->getHeader("auto")[0]))
        {
            $token = $request->getHeader("token")[0];
            $id = $request->getHeader("id_auto")[0];
            $auto = json_decode($request->getHeader("auto")[0]);

            $datos_token = Autentificadora::obtenerPayLoad($token);
            $usuario_token = $datos_token->payload->data;
            $perfil_usuario = $usuario_token->perfil;

            if ($perfil_usuario == "encargado" || $perfil_usuario == "propietario") 
            {
                $autoAModificar = Auto::TraerUnAuto($id);

                $autoAModificar->color = $auto->color;
                $autoAModificar->marca = $auto->marca;
                $autoAModificar->precio = $auto->precio;
                $autoAModificar->modelo = $auto->modelo;

                if($autoAModificar->ModificarAuto()) 
                {
                    $retorno->exito = true;
                    $retorno->mensaje = "Se modifico con exito el auto.";
                    $retorno->status = 200;
                } 
            } 
            else 
            {
                $retorno->mensaje = "Usuario no autorizado para realizar la accion. {$usuario_token->nombre} - {$usuario_token->apellido} - {$usuario_token->perfil}";
            }
        }

        $newResponse = $response->withStatus($retorno->status);
        $newResponse->getBody()->write(json_encode($retorno));
        return $newResponse->withHeader('Content-Type', 'application/json');
    }


//*********************************************************************************************//
/* FIN - AGREGO FUNCIONES PARA SLIM */
//*********************************************************************************************//

    public function AgregarAuto()
    {
        $retorno = false;
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta = $objetoAccesoDato->retornarConsulta("INSERT into autos (color, marca, precio, modelo)
        values(:color, :marca, :precio, :modelo)");
        $consulta->bindValue(':color', $this->color, PDO::PARAM_STR);
        $consulta->bindValue(':marca', $this->marca, PDO::PARAM_STR);
        $consulta->bindValue(':precio', $this->precio, PDO::PARAM_STR);
        $consulta->bindValue(':modelo', $this->modelo, PDO::PARAM_STR);
        if($consulta->execute())
        {
            $retorno = true;
        }		
        return $retorno;
    }

    public static function TraerAutos()
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta = $objetoAccesoDato->retornarConsulta("SELECT * FROM autos");
        $consulta->execute();			
        return $consulta->fetchAll(PDO::FETCH_CLASS, "Auto");		
    }

    public static function EliminarAuto($id)
    {
        $objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
        $consulta = $objetoAccesoDato->retornarConsulta("DELETE FROM autos WHERE id = :id");
        $consulta->bindValue(':id', $id, PDO::PARAM_INT);
        return $consulta->execute();			
    }

    public static function TraerUnAuto($id) 
	{
		$objetoAccesoDato = AccesoDatos::dameUnObjetoAcceso(); 
		$consulta = $objetoAccesoDato->retornarConsulta("SELECT * FROM usuarios 
        WHERE id = :id");
        $consulta->bindValue(":id", $id, PDO::PARAM_STR);
		$consulta->execute();
		$usuario= $consulta->fetchObject('Auto');
		return $usuario;		
	}

    public function ModificarAuto()
    {
        $accesoDatos = AccesoDatos::dameUnObjetoAcceso();

        $consulta = $accesoDatos->retornarConsulta(
            "UPDATE autos
             SET color = :color, marca = :marca, precio = :precio, modelo = :modelo
             WHERE id = :id"
        );

        $consulta->bindValue(":id", $this->id, PDO::PARAM_INT);
        $consulta->bindValue(":color", $this->color, PDO::PARAM_STR);
        $consulta->bindValue(":marca", $this->marca, PDO::PARAM_STR);
        $consulta->bindValue(":precio", $this->precio, PDO::PARAM_INT);
        $consulta->bindValue(":modelo", $this->modelo, PDO::PARAM_INT);
        return $consulta->execute();
    }
}