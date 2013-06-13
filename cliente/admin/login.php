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
	require_once('../cliente_config.php');
	$respuesta = array();
	if (isset($_GET['login'])) {
		$usuario = $_GET['usuario'];
		$pswd = $_GET['pswd'];
		if ($pswd == AdminConfig::$admin_password && $usuario == AdminConfig::$admin_user) {
			session_start();
			$_SESSION['admin_cliente_web'] = $usuario;
			$respuesta['Result'] = "OK";
			$respuesta['nombre'] = $_SESSION['admin_cliente_web'];
		} else {
			$respuesta['Result'] = "ERROR";
			$respuesta['Message'] = "Login Incorrecto";
		}
	} else {
		$respuesta['Result'] = "ERROR";
		$respuesta['Message'] = "Login Incorrecto";
	}
	print json_encode($respuesta);
?>