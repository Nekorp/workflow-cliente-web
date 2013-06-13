<?php
/**
 *   Copyright 2013 Nekorp
 *
 *Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *      http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *  limitations under the License
 */
 
	include_once "../cliente_config.php";
	include_once "../util/restclient.php";
	
	class AliasRepetidoException extends Exception {}
	class UsuarioNoEncontradoException extends Exception {}
	
	class AdminAPIService {
		private $rest_client;
		private $fn = "test.txt";
		public function __construct() {
			$this->rest_client = new RestClient(array(
				'base_url' => ApiConfig::$api_base_url,
				'username' => ApiConfig::$api_username,
				'password' => ApiConfig::$api_password
			));
		}
		
		public function listaUsuarios() {
			$rawData = $this->rest_client->get('cliente/web/usuarios');
			$page_usuarios = json_decode($rawData->response);
			//TODO si es necesario paginar
			$usuarios = $page_usuarios->items;
			return $usuarios;
		}
		
		public function listaClientes() {
			$rawData = $this->rest_client->get('clientes');
			$page_usuarios = json_decode($rawData->response);
			$clientes = $page_usuarios->items;
			$respuesta = Array();
			foreach ($clientes as $cliente) {
				$clienteRespuesta = array();
				$clienteRespuesta['DisplayText'] = $cliente->nombre;
				$clienteRespuesta['Value'] = $cliente->id;
				$respuesta[] = $clienteRespuesta;
			}
			return $respuesta;
		}
		
		public function consultaUsuario($alias) {
			$rawData = $this->rest_client->get('cliente/web/usuarios/'.$alias);
			if ($rawData->info->http_code == 404) {
				throw new UsuarioNoEncontradoException();
			}
			$result = json_decode($rawData->response);
			return $result;
		}
		
		public function crearUsuario($usuario) {
			$headers = array();
			$headers['Content-Type'] = 'application/json;charset=UTF-8';
			$rawData = $this->rest_client->post('cliente/web/usuarios', json_encode($usuario), $headers);
			if ($rawData->info->http_code == 400) {
				throw new AliasRepetidoException();
			}
			return $this->consultaUsuario($usuario['alias']);
		}
		
		public function actualizarUsuario($usuario) {
			$headers = array();
			$headers['Content-Type'] = 'application/json;charset=UTF-8';
			$rawData = $this->rest_client->post('cliente/web/usuarios/'.$usuario['alias'], json_encode($usuario), $headers);
			if ($rawData->info->http_code == 404) {
				throw new UsuarioNoEncontradoException();
			}
		}
		
		public function borrarUsuario($alias) {
			$headers = array();
			$headers['Content-Type'] = 'application/json;charset=UTF-8';
			$rawData = $this->rest_client->delete('cliente/web/usuarios/'.$alias, $headers);
			if ($rawData->info->http_code != 202) {
				throw new UsuarioNoEncontradoException();
			}
		}
	}
?>