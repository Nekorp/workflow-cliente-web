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
		try {
			if (!isset($_GET['usuario']) || strlen($_GET['usuario']) == 0) {
				throw new CredencialesNoValidasException();
			}
			if (!isset($_GET['pswd']) || strlen($_GET['pswd']) == 0) {
				throw new CredencialesNoValidasException();
			}
			$usuario = $_GET['usuario'];
			$pswd = $_GET['pswd'];
			$api = new WorkflowAPIClient();
			$login = $api->validateLogin($usuario, $pswd);
			session_start();
			$_SESSION['loginDisplay'] = $login['display'];
			$_SESSION['idCliente'] = $login['idCliente'];
			$_SESSION['nombreCliente'] = $login['nombreCliente'];
			$respuesta['Result'] = "OK";
			$respuesta['loginDisplay'] = $login['display'];
		} catch (CredencialesNoValidasException $e) {
			$respuesta['Result'] = "ERROR";
		}
	} else {
		$respuesta['Result'] = "ERROR";
	}
	print json_encode($respuesta);
?>