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
 
/** Error reporting */
error_reporting(E_ALL);
ini_set('display_errors', TRUE);
ini_set('display_startup_errors', TRUE);
date_default_timezone_set('America/Mexico_City');
if (PHP_SAPI == 'cli')
	die('nooooo!');

/** Include PHPExcel */
require_once '../libraries/PHPExcel/PHPExcel.php';
require_once '../WorkflowAPIClient.php';
session_start();
if (isset($_SESSION['idCliente'])) {
	$nombreCliente = $_SESSION['nombreCliente'];
	$idCliente = $_SESSION['idCliente'];
	// Create new PHPExcel object
	$objPHPExcel = new PHPExcel();

	// Set document properties
	$objPHPExcel->getProperties()->setCreator("ACE México")
								 ->setLastModifiedBy("ACE México")
								 ->setTitle("Reporte cliente")
								 ->setDescription("Reporte cliente ACE México");
	// Add some data
	$objPHPExcel->setActiveSheetIndex(0)
				->setCellValue('A1', $nombreCliente);
	$headers = array('Folio', 'Servicio Programado', 'Marca', 'Tipo', 'Version', 'Serie', 'Modelo', 'Color', 'Placas',
		'Kilometraje', 'Falla que presenta el auto', 'Diagnostico', 'Trabajo realizado', 'Fecha de Ingreso Auto',
		'Fecha de entrega auto', 'Mano de Obra facturado', 'Costo de refacciones facturado', 'Sub total Facturado', 
		'Iva facturado', 'Total facturado', 'Recomendaciones'
	);
	$column_offset = 1;
	$row_index = 2;
	$column_index = $column_offset;
	foreach($headers as $header) {
		$objPHPExcel->getActiveSheet()
					->setCellValueByColumnAndRow($column_index, $row_index, $header);
		$column_index++;
	}
	$api = new WorkflowAPIClient();
	$fechaInicial = $_GET['fechaInicial'];
	$fechaFinal = $_GET['fechaFinal'];
	$renglones = $api->getDatosCompletos($idCliente, $fechaInicial, $fechaFinal);
	foreach ($renglones as $renglon) {
		$row_index++;
		$column_index = $column_offset;
		$fechaIngreso = '';
		if (property_exists($renglon->datosBitacora ,'fechaIngresoAuto')) {
			$fechaIngreso = strtotime($renglon->datosBitacora->fechaIngresoAuto);
		}
		$fechaEntregaAuto = '';
		if (property_exists($renglon->datosBitacora ,'fechaEntregaAuto')) {
			$fechaEntregaAuto = strtotime($renglon->datosBitacora->fechaEntregaAuto);
		}
		$subtotalFacturado = $renglon->datosCosto->manoDeObraFacturado + $renglon->datosCosto->refaccionesFacturado;
		$totalFacturado = $subtotalFacturado + $renglon->datosCosto->ivaFacturado;
		$objPHPExcel->getActiveSheet()
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosServicio->folio)
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosServicio->programado)
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosAuto->marca)
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosAuto->tipo)
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosAuto->version)
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosAuto->serie)
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosAuto->modelo)
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosAuto->color)
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosAuto->placas)
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosServicio->kilometraje)
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosServicio->falla)
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosBitacora->diagnostico)
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosCosto->manoDeObra)
					->setCellValueByColumnAndRow($column_index++, $row_index, PHPExcel_Shared_Date::PHPToExcelWithoutUTC($fechaIngreso))
					->setCellValueByColumnAndRow($column_index++, $row_index, PHPExcel_Shared_Date::PHPToExcelWithoutUTC($fechaEntregaAuto))
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosCosto->manoDeObraFacturado)
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosCosto->refaccionesFacturado)
					->setCellValueByColumnAndRow($column_index++, $row_index, $subtotalFacturado)
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosCosto->ivaFacturado)
					->setCellValueByColumnAndRow($column_index++, $row_index, $totalFacturado)
					->setCellValueByColumnAndRow($column_index++, $row_index, $renglon->datosBitacora->recomendaciones);
		if (property_exists($renglon->datosBitacora ,'fechaIngresoAuto')) {
			$objPHPExcel->getActiveSheet()->getStyle('O'.$row_index)
						->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME);
		}
		if (property_exists($renglon->datosBitacora ,'fechaEntregaAuto')) {
			$objPHPExcel->getActiveSheet()->getStyle('P'.$row_index)
						->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME);
		}
	}
	//autosize
	for ($col = 'A'; $col != 'W'; $col++) {
		$objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
	}
	// Rename worksheet
	$objPHPExcel->getActiveSheet()->setTitle('Hoja1');

	// Set active sheet index to the first sheet, so Excel opens this as the first sheet
	$objPHPExcel->setActiveSheetIndex(0);

	// Redirect output to a client’s web browser (Excel2007)
	header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
	header('Content-Disposition: attachment;filename="ReporteCliente.xlsx"');
	header('Cache-Control: max-age=0');

	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	$objWriter->save('php://output');
	exit;
} else {
	die('El caos');
}

