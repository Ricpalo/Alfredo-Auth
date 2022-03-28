<?php

defined('BASEPATH') OR exit('No direct script access allowed');

use RestServer\RestController;
require APPPATH . '/libraries/RestController.php';
require APPPATH . '/libraries/Format.php';


class Api extends RestController {
  function __construct(){
    parent::__construct();
    $this->load->model('DAO');
  }

  function login_post(){
    $this->form_validation->set_data($this->post() ? $this->post() : array());
    
    $this->form_validation->set_rules("pEmail", "Email", "required");
    $this->form_validation->set_rules("pPassword", "Password", "required");
    
    if ($this->form_validation->run()) {
      $filtro = array(
        "correo_usuario" => $this->post('pEmail')
      );

      $usuario_existe = $this->DAO->seleccionar_entidad('tb_usuarios', $filtro, TRUE);
      
      if ($usuario_existe['data']) {
        $this->load->library('bcrypt');

        if ($this->bcrypt->check_password($this->post('pPassword'),$usuario_existe['data']->password_usuario)) {
          if($usuario_existe['data']->status_usuario == "Activo"){
            $session = array(
              "key" => $usuario_existe['data']->id_usuario,
              "nombre_completo" => $usuario_existe['data']->nombre_usuario . $usuario_existe['data']->apellidos_usuario,
              "genero" => $usuario_existe['data']->genero_usuario,
              "email" => $usuario_existe['data']->correo_usuario,
              "rol" => $usuario_existe['data']->rol_usuario,
              "foto" => foto_usuario . "/" . $usuario_existe['data']->foto_usuario
            );

            $respuesta = array(
              "status" => 1,
              "mensaje" => "Usuario localizado",
              "errors" => array(),
              "datos" => $session
            );
          }else{
            $status = $usuario_existe['data']->status_usuario == "Bloqueado" ? "bloqueada" : 'inactiva';
            $respuesta = array(
              "status" => 0,
              "mensaje" => "Tu cuenta tiene estatus de [ ".$status." ], contacta al administrador para mayor informacion",
              "errors" => array() 
            );
          }
        }else{
          $respuesta = array(
            "status" => 0,
            "mensaje" => "La clave ingresada no es correcta",
            "errors" => array() 
          );
        }
      }else{
        $respuesta = array(
          "status" => 0,
          "mensaje" => "El correo ingresado no existe",
          "errors" => array()
        );
      }
    }else{
      $respuesta = array(
        "status" => 0,
        "mensaje" => "Todos los campos son requeridos",
        "errors" =>$this->form_validation->error_array()
      );
    }
    $this->response($respuesta,200);
  }

  function usuario_post () {
    $this->form_validation->set_data($this->post());
    $this->form_validation->set_rules('email', 'E-mail', 'required|is_unique[tb_usuarios.correo_usuario]');
    $this->form_validation->set_rules('nombres', 'Nombres', 'required');
    $this->form_validation->set_rules('apellidos', 'Apellidos', 'required');
    $this->form_validation->set_rules('genero', 'Genero', 'required');
    $this->form_validation->set_rules('password', 'Password', 'required');
  
    if ( $this->form_validation->run() ) {
      $password = $this->bcrypt->hash_password($this->post('password'));

      $datos = array(
        "correo_usuario" => $this->post('email'),
        "nombre_usuario" => $this->post('nombres'),
        "apellidos_usuario" => $this->post('apellidos'),
        "genero_usuario" => $this->post('genero'),
        "password_usuario" => $password
      );

      $respuesta = $this->DAO->insert_modificar_entidad('tb_usuarios', $datos);

      if ($respuesta['status'] == '1') {
        $respuesta = array(
          "status" => "1",
          "mensaje" => "Registro Correcto",
          "datos" => array(),
          "errores" => array()
        );
      } else {
          $respuesta = array(
              "status" => "0",
              "errores" => array(),
              "mensaje" => "Error al registrar",
              "datos" => array()
          );
      }

    } else {
      $respuesta = array(
        "status" => "0",
        "errores" => $this->form_validation->error_array(),
        "mensaje" => "Error al procesar la información",
        "datos" => array()
      );
    }

    $this->response($respuesta, 200);
  }

}
