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
 
	//configuracion hardcode del locale seria mejor cambiarlo, pero luego
	date_default_timezone_set('America/Mexico_City');
	//configuracion para acceso a los servicios rest del backend
	class ApiConfig {
		//local
		public static $api_base_url = 'http://localhost:8080/api/v1/';
		public static $api_format = 'jason';
		public static $api_username = 'user';
		public static $api_password = 'user';
	}
?>