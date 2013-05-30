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
 
	include_once "WorkflowAPIClient.php";
	$respuesta = array();
	if (isset($_GET['login'])) {
		//TODO el id del cliente podria ser cualquier cosa
		$idCliente = $_GET['idCliente'];
		$pswd = $_GET['pswd'];
		$api = new WorkflowAPIClient();
		$nombreCliente = $api->validateLogin($idCliente, $pswd);
		//happy path
		session_start();
		$_SESSION['nombreCliente'] = $nombreCliente;
		$_SESSION['idCliente'] = $idCliente;
		//todo verificar el password contra algo
		$respuesta['Result'] = "OK";
		$respuesta['nombreCliente'] = "$nombreCliente";
	} else {
		$respuesta['Result'] = "ERROR";
	}
	print json_encode($respuesta);
?>