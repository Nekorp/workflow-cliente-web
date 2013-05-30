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
 
	include_once "cliente_config.php";
	include_once "libraries/RESTClient/restclient.php";
	class WorkflowAPIClient {
	
		private $rest_client;
		private $link_descarga_pdf = "reportes/servicio.php";
		
		public function __construct() {
			$this->rest_client = new RestClient(array(
				'base_url' => ApiConfig::$api_base_url,
				'format' => ApiConfig::$api_format,
				'username' => ApiConfig::$api_username,
				'password' => ApiConfig::$api_password
			));
		}
		
		public function validateLogin($idCliente, $pswd) {
			//todo hacer un servicio para validar al cliente
			//se podria tener una tabla en el hostweb para relacionar id de clientes, un alias y su password
			//esa tabla podria editarse directamente con el jTable CRUD
			//validar un 404 etc
			$result = $this->rest_client->get('clientes/'.$idCliente);
			$cliente = json_decode($result->response);
			return $cliente->nombre;
		}
		
		private function getServicios($idCliente, $fechaInicialRaw, $fechaFinalRaw) {
			//TODO resolver manejo de fechas y zona horaria
			$fechaInicial = $fechaInicialRaw.'T00:00:00.000-05:00';
			$fechaFinal = $fechaFinalRaw.'T16:59:59.999-05:00';
			//TODO optimizar la consulta para traer mas datos de un solo golpe
			//se disminuyen las vueltas al servidor por el momento son 3xNumReg = sad
			$paramBuscarServicios = array(
				"idCliente" => $idCliente,
				"fechaInicial" => $fechaInicial,
				"fechaFinal" => $fechaFinal,
			);
			$result = $this->rest_client->get('servicios', $paramBuscarServicios);
			$servicios = json_decode($result->response);
			return $servicios;
		}
		
		public function getDatosCompletos($idCliente, $fechaInicialRaw, $fechaFinalRaw) {
			$servicios = $this->getServicios($idCliente, $fechaInicialRaw, $fechaFinalRaw);
			$response = array();
			foreach ($servicios->items as $servicio) {
				$result = $this->rest_client->get('/reportes/global/renglones/servicio/'.$servicio->id);
				$renglon = json_decode($result->response);
				$response[] = $renglon;
			}
			return $response;
		}
		
		//TODO incluir logica de paginacion
		public function getDatosServicio($idCliente, $fechaInicialRaw, $fechaFinalRaw) {
			$servicios = $this->getServicios($idCliente, $fechaInicialRaw, $fechaFinalRaw);
			$response = array();
			foreach ($servicios->items as $servicio) {
				$result = $this->rest_client->get('/reportes/global/renglones/servicio/'.$servicio->id);
				$renglonRaw = json_decode($result->response);
				$renglon = array();
				$renglon['folio'] = '<a href="'.$this->link_descarga_pdf.'">'.$renglonRaw->datosServicio->folio.'</a>';
				$renglon['programado'] = $renglonRaw->datosServicio->programado;
				if (property_exists($renglonRaw->datosBitacora ,'fechaIngresoAuto')) {
					$fechaIngresoAuto = strtotime($renglonRaw->datosBitacora->fechaIngresoAuto);
					$renglon['fechaIngresoAuto'] = date('Y-m-d H:i:s', $fechaIngresoAuto);
				} else {
					$renglon['fechaIngresoAuto'] = '';
				}
				if (property_exists($renglonRaw->datosBitacora ,'fechaEntregaAuto')) {
					$fechaEntregaAuto = strtotime($renglonRaw->datosBitacora->fechaEntregaAuto);
					$renglon['fechaEntregaAuto'] = date('Y-m-d H:i:s', $fechaEntregaAuto);
				} else {
					$renglon['fechaEntregaAuto'] = '';
				}
				$renglon['falla'] = $renglonRaw->datosServicio->falla;
				$renglon['diagnostico'] = $renglonRaw->datosBitacora->diagnostico;
				$renglon['recomendaciones'] = $renglonRaw->datosBitacora->recomendaciones;
				$response[] = $renglon;
			}
			return $response;
		}
		
		public function getDatosAuto($idCliente, $fechaInicialRaw, $fechaFinalRaw) {
			$servicios = $this->getServicios($idCliente, $fechaInicialRaw, $fechaFinalRaw);
			$response = array();
			foreach ($servicios->items as $servicio) {
				$result = $this->rest_client->get('/reportes/global/renglones/servicio/'.$servicio->id);
				$renglonRaw = json_decode($result->response);
				$renglon = array();
				$renglon['folio'] = '<a href="'.$this->link_descarga_pdf.'">'.$renglonRaw->datosServicio->folio.'</a>';
				$renglon['marca'] = $renglonRaw->datosAuto->marca;
				$renglon['tipo'] = $renglonRaw->datosAuto->tipo;
				$renglon['version'] = $renglonRaw->datosAuto->version;
				$renglon['serie'] = $renglonRaw->datosAuto->serie;
				$renglon['modelo'] = $renglonRaw->datosAuto->modelo;
				$renglon['color'] = $renglonRaw->datosAuto->color;
				$renglon['placas'] = $renglonRaw->datosAuto->placas;
				$renglon['kilotraje'] = $renglonRaw->datosServicio->kilometraje;
				$response[] = $renglon;
			}
			return $response;
		}
		
		public function getDatosPresupuesto($idCliente, $fechaInicialRaw, $fechaFinalRaw) {
			$servicios = $this->getServicios($idCliente, $fechaInicialRaw, $fechaFinalRaw);
			$response = array();
			foreach ($servicios->items as $servicio) {
				$result = $this->rest_client->get('/reportes/global/renglones/servicio/'.$servicio->id);
				$renglonRaw = json_decode($result->response);
				$renglon = array();
				$renglon['folio'] = '<a href="'.$this->link_descarga_pdf.'">'.$renglonRaw->datosServicio->folio.'</a>';
				$renglon['trabajoRealizado'] = $renglonRaw->datosCosto->manoDeObra;
				$renglon['manoDeObra'] = $renglonRaw->datosCosto->manoDeObraFacturado;
				$renglon['refacciones'] = $renglonRaw->datosCosto->refaccionesFacturado;
				$subtotalFacturado = $renglonRaw->datosCosto->manoDeObraFacturado + $renglonRaw->datosCosto->refaccionesFacturado;
				$renglon['subTotal'] = $subtotalFacturado;
				$renglon['iva'] = $renglonRaw->datosCosto->ivaFacturado;
				$totalFacturado = $subtotalFacturado + $renglonRaw->datosCosto->ivaFacturado;
				$renglon['total'] = $totalFacturado;
				$response[] = $renglon;
			}
			return $response;
		}
	}
?>