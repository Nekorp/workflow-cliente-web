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
 
require_once('../libraries/tcpdf/tcpdf.php');
include_once "../WorkflowAPIClient.php";
date_default_timezone_set('America/Mexico_City');
session_start();
if (isset($_SESSION['idCliente'])) {
	$idCliente = $_SESSION['idCliente'];
	$idServicio = $_GET['idServicio'];
	$api = new WorkflowAPIClient();
	$datos = $api->getDatosReporteCliente($idCliente, $idServicio);
	$pdf = new TCPDF("L", PDF_UNIT, "LETTER", true, 'UTF-8', false);

	// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetAuthor('ACE-México');
	$pdf->SetTitle('Reporte Cliente');
	//$pdf->SetSubject('');
	//$pdf->SetKeywords('');

	// remove default header/footer
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);

	// set default header data
	//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' 048', PDF_HEADER_STRING);

	// set header and footer fonts
	//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
	//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

	// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	// set margins
	//$pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
	$pdf->SetMargins(12.7, 12.7, 12.7);
	//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
	//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

	// set auto page breaks
	//$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
	$pdf->SetAutoPageBreak(TRUE, 12.7);

	// set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

	// set some language-dependent strings (optional)
	//if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
	//    require_once(dirname(__FILE__).'/lang/eng.php');
	//    $pdf->setLanguageArray($l);
	//}

	// ---------------------------------------------------------

	// set font
	$pdf->SetFont('helvetica', '', 9);

	// add a page
	$pdf->AddPage();

	//---------la tabla del encabezado -----------------------------------------------
	$tbl = <<<EOD
<table cellspacing="0" cellpadding="1" border="0" width="650">
	<tr>
		<td>No de servicio:</td>
		<td>$datos->numeroDeServicio</td>
		<td>Tiempo de reparacion:</td>
		<td>$datos->tiempoReparacion</td>
	</tr>
	<tr>
		<td>Nombre del cliente:</td>
		<td>$datos->nombreDelCliente</td>
		<td></td>
		<td></td>
	</tr>
	<tr>
		<td>Descripción del servicio:</td>
		<td>$datos->descripcionServicio</td>
		<td></td>
		<td></td>
	</tr>
</table>
EOD;
	$pdf->writeHTML($tbl, true, false, false, false, '');
	// ------------------------La parte del auto------------------------------------
	$auto = $datos->auto;
	$tbl = <<<EOD
<style>
	td.titulo {
		text-align: center;
		color: #ffffff;
		background-color: #538DD5;
	}
	td.subTitulo {
		text-align: left;
		color: #ffffff;
		background-color: #538DD5;
	}
	td.cantidad {
		text-align: right;
	}
	td.texto {
		text-align: left;
	}
</style>
<table cellspacing="0" cellpadding="2" border="1" width="100%">
	<tr>
		<td class="titulo" colspan="8"><b>Auto</b></td>
	</tr>
	<tr>
		<td class="subTitulo" width="15%"><b>Marca</b></td>
		<td class="subTitulo" width="10%"><b>Tipo</b></td>
		<td class="subTitulo" width="10%"><b>Versión</b></td>
		<td class="subTitulo" width="20%"><b>Serie</b></td>
		<td class="subTitulo" width="10%"><b>Modelo</b></td>
		<td class="subTitulo" width="10%"><b>Color</b></td>
		<td class="subTitulo" width="10%"><b>Placas</b></td>
		<td class="subTitulo" width="15%"><b>Kilometraje</b></td>
	</tr>
	<tr>
		<td class="texto">$auto->marca</td>
		<td class="texto">$auto->tipo</td>
		<td class="texto">$auto->version</td>
		<td class="texto">$auto->serie</td>
		<td class="cantidad">$auto->modelo</td>
		<td class="texto">$auto->color</td>
		<td class="texto">$auto->placas</td>
		<td class="cantidad">$auto->kilometraje</td>
	</tr>
</table>
EOD;
	$pdf->writeHTML($tbl, true, false, false, false, '');
	// -----------------------La parte del presupuesto ----------------------------------------------
	$tbl = <<<EOD
<style>
	td.titulo {
		text-align: center;
		color: #ffffff;
		background-color: #538DD5;
		border-top-style:solid;
		border-right-style:solid;
		border-bottom-style:solid;
		border-left-style:solid;
		border-width:1px;
		border-color:black;
	}
	td.cantidad {
		text-align: right;
	}
	td.texto {
		text-align: left;
	}
	td.extremo-superior {
		border-top-style:solid;
	}
	td.extremo-derecha {
		border-right-style:solid;
	}
	td.extremo-izquierda {
		border-left-style:solid;
	}
	td.extremo-inferior {
		border-bottom-style:solid;
	}
</style>
<table cellspacing="0" cellpadding="2" border="0" width="100%">
	<thead>
		<tr>
			<td class="titulo" colspan="3"><b>Mecánica</b></td>
			<td class="titulo" colspan="3"><b>Hojalatería y pintura</b></td>
		</tr>
	</thead>
EOD;
	//body de la tabla
	$totalRegistrosMecanica = count($datos->registroMecanica);
	$totalregistrosHojalateria = count($datos->registroHojalateriaPintura);
	if ($totalRegistrosMecanica >= $totalregistrosHojalateria) {
		$numRenglones = $totalRegistrosMecanica;
	} else {
		$numRenglones = $totalregistrosHojalateria;
	}
	for ($index = 0; $index < $numRenglones; $index++) {
		//datos
		if ($index < $totalRegistrosMecanica) {
			$mecaTipo = $datos->registroMecanica[$index]->tipo;
			$mecaDesc = $datos->registroMecanica[$index]->descripcion;
			$mecaCosto = '$'.number_format($datos->registroMecanica[$index]->costo, 2);
		} else {
			$mecaTipo = '';
			$mecaDesc = '';
			$mecaCosto = '';
		}
		if ($index < $totalregistrosHojalateria) {
			$hojaTipo = $datos->registroHojalateriaPintura[$index]->tipo;
			$hojaDesc = $datos->registroHojalateriaPintura[$index]->descripcion;
			$hojaCosto = '$'.number_format($datos->registroHojalateriaPintura[$index]->costo, 2);
		} else {
			$hojaTipo = '';
			$hojaDesc = '';
			$hojaCosto = '';
		}
		//estilos
		$mecaTipoClass = "texto extremo-izquierda";
		$mecaDescClass = "texto";
		$mecaCostoClass = "cantidad extremo-derecha";
		$hojaTipoClass = "texto extremo-izquierda";
		$hojaDescClass = "texto";
		$hojaCostoClass = "cantidad extremo-derecha";
		if ($index == 0) {
			$mecaTipoClass = $mecaTipoClass.' extremo-superior';
			$mecaDescClass = $mecaDescClass.' extremo-superior';
			$mecaCostoClass = $mecaCostoClass.' extremo-superior';
			$hojaTipoClass = $hojaTipoClass.' extremo-superior';
			$hojaDescClass = $hojaDescClass.' extremo-superior';
			$hojaCostoClass = $hojaCostoClass.' extremo-superior';
		}
		if ($index == ($numRenglones - 1)) {
			$mecaTipoClass = $mecaTipoClass.' extremo-inferior';
			$mecaDescClass = $mecaDescClass.' extremo-inferior';
			$mecaCostoClass = $mecaCostoClass.' extremo-inferior';
			$hojaTipoClass = $hojaTipoClass.' extremo-inferior';
			$hojaDescClass = $hojaDescClass.' extremo-inferior';
			$hojaCostoClass = $hojaCostoClass.' extremo-inferior';
		}
		
		$tbl = $tbl.<<<EOD
	<tr>
		<td class="$mecaTipoClass">$mecaTipo</td>
		<td class="$mecaDescClass">$mecaDesc</td>
		<td class="$mecaCostoClass">$mecaCosto</td>
		<td class="$hojaTipoClass">$hojaTipo</td>
		<td class="$hojaDescClass">$hojaDesc</td>
		<td class="$hojaCostoClass">$hojaCosto</td>
	</tr>
EOD;
	}
	
	//cerrar tabla
	$totalMecanica = number_format($datos->totalMecanica, 2);
	$totalHojalateria = number_format($datos->totalHojalateria, 2);
	$tbl = $tbl.<<<EOD
	<tr>
		<td></td>
		<td class="texto"><b>Total</b></td>
		<td class="cantidad"><b>$$totalMecanica</b></td>
		<td></td>
		<td class="texto"><b>Total</b></td>
		<td class="cantidad"><b>$$totalHojalateria</b></td>
	</tr>
</table>
EOD;
	
	$pdf->writeHTML($tbl, true, false, false, false, '');
	//TOTAL
	$totalServicio = number_format($datos->totalServicio,2);
	$tbl = <<<EOD
<style>
	td.cantidad {
		text-align: right;
	}
	td.texto {
		text-align: left;
	}
</style>
<table cellspacing="0" cellpadding="1" border="0" width="300">
	<tr>
		<td class="texto">Total de Servicio:</td>
		<td class="cantidad"><b>$$totalServicio</b></td>
	</tr>
</table>
EOD;
	$pdf->writeHTML($tbl, true, false, false, false, '');
	
	// ------------------------La parte de la bitacora------------------------------------
	$tbl = <<<EOD
<style>
	td.titulo {
		text-align: center;
		color: #ffffff;
		background-color: #538DD5;
	}
	td.subTitulo {
		text-align: left;
		color: #ffffff;
		background-color: #538DD5;
	}
	td.derecha {
		text-align: right;
	}
	td.izquierda {
		text-align: left;
	}
</style>
<table cellspacing="0" cellpadding="2" border="1">
	<thead>
		<tr>
			<td class="titulo" colspan="4"><b>Bitácora</b></td>
		</tr>
	</thead>
EOD;
	//contenido
	$numRenglones = count($datos->bitacora);
	for ($index = 0; $index < $numRenglones; $index++) {
		$nombreEvento = $datos->bitacora[$index]->nombreEvento;
		$detalle = $datos->bitacora[$index]->detalle;
		$fechaRaw = strtotime($datos->bitacora[$index]->fecha);
		$fecha = date('Y-m-d H:i:s', $fechaRaw);
		$etiqueta = $datos->bitacora[$index]->etiqueta;
		$tbl = $tbl.<<<EOD
	<tr>
		<td class="izquierda" width="30%">$nombreEvento</td>
		<td class="izquierda" width="30%">$detalle</td>
		<td class="derecha" width="15%">$fecha</td>
		<td class="izquierda" width="25%">$etiqueta</td>
	</tr>
EOD;
	}
	//cerrar tabla
	$tbl = $tbl.<<<EOD
</table>
EOD;
	$pdf->writeHTML($tbl, true, false, false, false, '');
	
	$pdf->Output('ReporteCliente-'.$idCliente.'.pdf', 'I');
} else {
	die('El caos');
}
?>