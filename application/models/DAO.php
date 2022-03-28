<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class DAO extends CI_Model {

  function insert_modificar_entidad($entidad, $datos = array(), $filtro = array()){
    if($filtro){
      $this->db->where($filtro);
      $this->db->update($entidad,$datos);
    }else{
      $this->db->insert($entidad,$datos);
    }
    if($this->db->error()['message']!=""){
      return array(
        "status" => "0",
        "mensaje" => $this->db->error()['message'],
        "codigo" => $this->db->error()['code']
      );
    }else{
      return array(
        "status" => "1",
        "mensaje" => "información guardada correctamente",
      );
    }
  }

  function seleccionar_entidad($entidad, $filtro =  array(),  $unico = FALSE){
      if($filtro){
        $this->db->where($filtro);
      }

      $query =  $this->db->get($entidad);
      
      if($this->db->error()['message']!=''){
  	  return array(
  			"status"=>"0",
  			"mensaje"=>$this->db->error()['message'],
  			"data"=>null
  		);
      } else {
        return $unico ? $query->row() : $query->result();
      }
  }

  function eliminar_entidad($entidad, $filtro = null) {
    if ($filtro) {
        $this->db->where('id', $filtro);
        $this->db->delete($entidad);
    } else {
        return array(
            "status" => "0",
            "mensaje" => "Debes pasar un id",
            "codigo" => $this->db->error()['code']
        );
    }

    if ($this->db->error()['message'] != "") {
        return array(
            "status" => "0",
            "mensaje" => $this->db->error()['message'],
            "codigo" => $this->db->error()['code']
        );
    } else {
        return array(
            "status" => "1",
            "mensaje" => "Informacion procesada correctamente"
        );
    }
  }

  function ejecutar_consulta_sql($sql, $parametros = array(),$unico =  FALSE){
    $query =  $this->db->query($sql,$parametros ? $parametros : null);
    if($this->db->error()['message']!=''){
  	  return array(
  			"status"=>"0",
  			"mensaje"=>$this->db->error()['message'],
  			"data"=>null
  		);
	  }else{
  		return array(
  			"status"=>"1",
  			"mensaje"=>"Información cargada correctamente",
  			"data"=> $unico ?  $query->row() : $query->result()
  		);
	  }
  }

  function iniciar_transaccion(){
    $this->db->trans_begin();
  }
  function validad_terminar_transaccion(){
    if($this->db->trans_status()){
        $this->db->trans_commit();
        return array(
            "status" => "success",
            "message" => "Operacición completada correctamente",
            "data" => null
        );
    }else{
        $this->db->trans_rollback();
        return  array(
            "status" => "1",
            "mensaje" => "Error al completar la Operacición",
            "data" => null
        );
    }
  }

}
