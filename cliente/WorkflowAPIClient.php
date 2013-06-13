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
	include_once "util/restclient.php";
	
	class CredencialesNoValidasException extends Exception {}
	class ReporteNoEncontradoException extends Exception {}
	class FechaFueraDeRangoException extends Exception {}
	class FechaInvalidaException extends Exception {}
	
	class WorkflowAPIClient {
	
		private $rest_client;
		private $link_descarga_pdf = "reportes/servicio.php?idServicio=";
		
		public function __construct() {
			$this->rest_client = new RestClient(array(
				'base_url' => ApiConfig::$api_base_url,
				'username' => ApiConfig::$api_username,
				'password' => ApiConfig::$api_password
			));
		}
		
		public function validateLogin($usuario, $pswd) {
			$rawData = $this->rest_client->get('cliente/web/usuarios/'.$usuario);
			if ($rawData->info->http_code == 404) {
				throw new CredencialesNoValidasException();
			}
			$usuarioWeb = json_decode($rawData->response);
			if ($usuarioWeb->password != $pswd) {
				throw new CredencialesNoValidasException();
			}
			if ($usuarioWeb->status != 'activo') {
				throw new CredencialesNoValidasException();
			}
			$result = $this->rest_client->get('clientes/'.$usuarioWeb->idCliente);
			$cliente = json_decode($result->response);
			$respuesta = array();
			$respuesta['display'] = $cliente->nombre.' / '.$usuarioWeb->alias;
			$respuesta['idCliente'] = $usuarioWeb->idCliente;
			$respuesta['nombreCliente'] = $cliente->nombre;
			return $respuesta;
		}
		
		private function getServicios($idCliente, $fechaInicialRaw, $fechaFinalRaw) {
			try {
				$fechaInicial = new DateTime($fechaInicialRaw);
				$fechaFinal = new DateTime($fechaFinalRaw);
				$intervaloFinDia = new DateInterval('PT23H59M59S');
				$fechaFinal->add($intervaloFinDia);
				$fechaActual = new DateTime();
			} catch (Exception $e) {
				//suponemos que no son fechas validas
				throw new FechaInvalidaException();
			}
			if ($fechaInicial > $fechaActual) {
				throw new FechaFueraDeRangoException();
			}
			if ($fechaFinal > $fechaActual) {
				if ($fechaFinal->diff($fechaActual)->days > 0) {
					throw new FechaFueraDeRangoException();
				}
			}
			if ($fechaInicial->diff($fechaActual)->days > 30 || $fechaFinal->diff($fechaActual)->days > 30) {
				throw new FechaFueraDeRangoException();
			}
			//TODO optimizar la consulta para traer mas datos en cada consulta
			//hay que disminuir las vueltas al servidor por el momento son 3xNumReg = muy horrible
			$paramBuscarServicios = array(
				"idCliente" => $idCliente,
				"fechaInicial" => $fechaInicial->format(DateTime::ISO8601),
				"fechaFinal" => $fechaFinal->format(DateTime::ISO8601),
			);
			$result = $this->rest_client->get('servicios', $paramBuscarServicios);
			$servicios = json_decode($result->response);
			return $servicios;
		}
		
		public function getDatosCompletos($idCliente, $fechaInicialRaw, $fechaFinalRaw) {
			$servicios = $this->getServicios($idCliente, $fechaInicialRaw, $fechaFinalRaw);
			$response = array();
			foreach ($servicios->items as $servicio) {
				$result = $this->rest_client->get('reportes/global/renglones/servicio/'.$servicio->id);
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
				$result = $this->rest_client->get('reportes/global/renglones/servicio/'.$servicio->id);
				$renglonRaw = json_decode($result->response);
				$renglon = array();
				$renglon['folio'] = '<a href="'.$this->link_descarga_pdf.$servicio->id.'" target="_blank">'.$renglonRaw->datosServicio->folio.'</a>';
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
				$result = $this->rest_client->get('reportes/global/renglones/servicio/'.$servicio->id);
				$renglonRaw = json_decode($result->response);
				$renglon = array();
				$renglon['folio'] = '<a href="'.$this->link_descarga_pdf.$servicio->id.'" target="_blank">'.$renglonRaw->datosServicio->folio.'</a>';
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
				$result = $this->rest_client->get('reportes/global/renglones/servicio/'.$servicio->id);
				$renglonRaw = json_decode($result->response);
				$renglon = array();
				$renglon['folio'] = '<a href="'.$this->link_descarga_pdf.$servicio->id.'" target="_blank">'.$renglonRaw->datosServicio->folio.'</a>';
				$renglon['trabajoRealizado'] = $renglonRaw->datosCosto->manoDeObra;
				$renglon['manoDeObra'] = '$'.number_format($renglonRaw->datosCosto->manoDeObraFacturado, 2);
				$renglon['refacciones'] = '$'.number_format($renglonRaw->datosCosto->refaccionesFacturado, 2);
				$subtotalFacturado = $renglonRaw->datosCosto->manoDeObraFacturado + $renglonRaw->datosCosto->refaccionesFacturado;
				$renglon['subTotal'] = '$'.number_format($subtotalFacturado, 2);
				$renglon['iva'] = '$'.number_format($renglonRaw->datosCosto->ivaFacturado, 2);
				$totalFacturado = $subtotalFacturado + $renglonRaw->datosCosto->ivaFacturado;
				$renglon['total'] = '$'.number_format($totalFacturado, 2);
				$response[] = $renglon;
			}
			return $response;
		}
		
		public function getDatosReporteCliente($idCliente, $idServicio) {
			$rawData = $this->rest_client->get('reportes/cliente/'.$idCliente.'/'.$idServicio);
			if ($rawData->info->http_code == 404) {
				throw new ReporteNoEncontradoException();
			}
			$datos = json_decode($rawData->response);
			return $datos;
		}
	}
?>