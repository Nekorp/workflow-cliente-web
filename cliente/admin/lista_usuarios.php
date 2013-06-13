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
 
	include_once "AdminAPIService.php";
	session_start();
	$jTableResult = array();
	if (isset($_SESSION['admin_cliente_web'])) {
		$api = new AdminAPIService();
		$rows = $api->listaUsuarios();
		$jTableResult['Result'] = "OK";
		$jTableResult['Records'] = $rows;
	} else {
		$rows = array();
		$jTableResult['Result'] = "OK";
		$jTableResult['Records'] = $rows;
	}
	print json_encode($jTableResult);
?>