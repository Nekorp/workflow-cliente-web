function showContent(value) {
	if (value) {
		$("#logoutForm").show();
		$("#consultaServicios").show();
		$("#downloadGlobal").prop('disabled', true);
	} else {
		$("#logoutForm").hide();
		$("#consultaServicios").hide();
	}
};

function switchLogin(value) {
	if (value) {
		$("#loginForm").show();
		$("#usuario").prop('disabled', false);
		$("#pswd").prop('disabled', false);
		$("#logoutForm").hide();
	} else {
		$("#loginForm").hide();
		$("#logoutForm").show();
	}
};

function iniciaBusqueda(userName) {
	$("#errorReport").html("");
	$("#loginDisplay").text(userName);
	crearTablas();
	switchLogin(false);
	showContent(true);
};

function doLogin(callback) {
	$.ajax({
		url: "login.php",
		data: {
			usuario: $("#usuario").val(),
			pswd: $("#pswd").val(),
			login: "login"
		},
		success: function(data){
			$("#usuario").val("");
			$("#pswd").val("");
			callback(data);
		},
		dataType: "json"
	});
};

function doLogout(callback) {
	$.ajax({
		url: "logout.php",
		success: function(data) {
			callback(data);
		},
		dataType: "json"
	});
}

function doCheckLogin(callback) {
	$.ajax({
		url: "login/alive.php",
		success: function(data) {
			callback(data);
		},
		dataType: "json"
	});
};

$("#login").click(function() {
	var btn = $(this);
	btn.button('loading');
	$("#usuario").prop('disabled', true);
	$("#pswd").prop('disabled', true);
	doLogin(function(data) {
		if (data.Result == 'ERROR') {
			$("#errorReport").html(
				'<div id="error1" class="alert alert-block alert-error fade in">' +
					'<a class="close" data-dismiss="alert" href="#">&times;</a>' +
					'<p>Usuario o password incorrectos</p>' +
				'</div>'
			);
		} else {
			iniciaBusqueda(data.loginDisplay);
		}
		btn.button('reset');
		$("#usuario").prop('disabled', false);
		$("#pswd").prop('disabled', false);
	});
});

$("#logout").click(function() {
	var btn = $(this);
	btn.button('loading');
	doLogout(function(){
		destruirTablas();
		$("#errorReport").html("");
		$("#loginDisplay").text("");
		$("#fechaInicial").val("");
		$("#fechaFinal").val("");
		switchLogin(true);
		showContent(false);
		btn.button('reset');
	});
});

$("#buscar").click(function() {
	var btn = $(this);
	var fechaInicial = $("#fechaInicial" ).val();
	var fechaFinal = $("#fechaFinal").val();
	if (!fechaInicial || !fechaFinal) {
		$("#errorReport").html(
			'<div id="error1" class="alert alert-block alert-error fade in">' +
				'<a class="close" data-dismiss="alert" href="#">&times;</a>' +
				'<p>Seleccione una fecha inicial y una fecha final para su busqueda</p>' +
			'</div>'
		);
		return;
	}
	btn.button('loading');
	$("#downloadGlobal").prop('disabled', true);
	var buscandoServicios = true;
	var buscandoAutos = true;
	var buscandoPresupuesto = true;
	$('#DatosServicioContainer').jtable(
		'load',
		{
			fechaInicial: $("#fechaInicial" ).val(), 
			fechaFinal: $("#fechaFinal").val()
		},
		function() {
			buscandoServicios = false;
			if (!buscandoServicios && !buscandoAutos && !buscandoPresupuesto) {
				terminoDeBuscar();
			}
		}
	);
	$('#DatosAutoContainer').jtable(
		'load',
		{
			fechaInicial: $("#fechaInicial" ).val(), 
			fechaFinal: $("#fechaFinal").val()
		},
		function() {
			buscandoAutos = false;
			if (!buscandoServicios && !buscandoAutos && !buscandoPresupuesto) {
				terminoDeBuscar();
			}
		}
	);
	$('#PresupuestoContainer').jtable(
		'load',
		{
			fechaInicial: $("#fechaInicial" ).val(), 
			fechaFinal: $("#fechaFinal").val()
		},
		function() {
			buscandoPresupuesto = false;
			if (!buscandoServicios && !buscandoAutos && !buscandoPresupuesto) {
				terminoDeBuscar();
			}
		}
	);
});

function terminoDeBuscar() {
	$("#buscar").button('reset');
	$("#fechaInicialDownloadGlobal").val($("#fechaInicial").val());
	$("#fechaFinalDownloadGlobal").val($("#fechaFinal").val());
}

$(function() {
	$( "#tabs" ).tabs();
});

$(function() {
	$("#fechaInicial").datepicker({
		dateFormat: "yy-mm-dd",
		minDate: "-30d",
		maxDate: "0d",
		constrainInput: true
	});
});

$(function() {
	$("#fechaFinal").datepicker({
		dateFormat: "yy-mm-dd",
		minDate: "-30d",
		maxDate: "0d",
		constrainInput: true
	});
});

function checkLoginInicial() {
	doCheckLogin(function(data) {
		if (data.tieneSession) {
			iniciaBusqueda(data.loginDisplay);
		}
	});
}

function crearTablas() {
	$('#DatosServicioContainer').jtable({
		title: 'Datos del Servicio',
		ajaxSettings: {
			type: 'GET',
			dataType: 'json'
		},
		actions: {
			listAction: 'servicios.php'
		},
		fields: {
			folio: {
				title: 'Folio',
				width: '5%',
				key: true
			},
			programado: {
				title: 'Programado',
				width: '5%'
			},
			fechaIngresoAuto: {
				title: 'Ingreso Auto',
				width: '15%'
			},
			fechaEntregaAuto: {
				title: 'Entrega Auto',
				width: '15%'
			},
			falla: {
				title: 'Falla',
				width: '20%'
			},
			diagnostico: {
				title: 'Diagnostico',
				width: '20%'
			},
			recomendaciones: {
				title: 'Recomendaciones',
				width: '20%'
			},
		},
		recordsLoaded: function(event,data) {
			if (data.serverResponse.mensajeError) {
				$("#errorReport").html(
					'<div id="error1" class="alert alert-block alert-error fade in">' +
						'<a class="close" data-dismiss="alert" href="#">&times;</a>' +
						'<p>' + data.serverResponse.mensajeError + '</p>' +
					'</div>'
				);
			} else {
				$("#errorReport").html("");
			}
			if (data.records.length > 0) {
				$("#downloadGlobal").prop('disabled', false);
			}
		}
	});
	$('#DatosAutoContainer').jtable({
		title: 'Datos del Auto',
		ajaxSettings: {
			type: 'GET',
			dataType: 'json'
		},
		actions: {
			listAction: 'autos.php'
		},
		fields: {
			folio: {
				title: 'Folio',
				width: '5%',
				key: true
			},
			marca: {
				title: 'Marca',
				width: '10%'
			},
			tipo: {
				title: 'Tipo',
				width: '10%'
			},
			version: {
				title: 'Version',
				width: '10%'
			},
			serie: {
				title: 'Serie',
				width: '15%'
			},
			modelo: {
				title: 'Modelo',
				width: '10%'
			},
			color: {
				title: 'Color',
				width: '10%'
			},
			placas: {
				title: 'Placas',
				width: '15%'
			},
			kilotraje: {
				title: 'Kilometraje',
				width: '15%'
			},
		}
	});
	$('#PresupuestoContainer').jtable({
		title: 'Presupuesto',
		ajaxSettings: {
			type: 'GET',
			dataType: 'json'
		},
		actions: {
			listAction: 'presupuestos.php'
		},
		fields: {
			folio: {
				title: 'Folio',
				width: '5%',
				key: true
			},
			trabajoRealizado: {
				title: 'Trabajo realizado',
				width: '30%'
			},
			manoDeObra: {
				title: 'Mano de Obra',
				width: '13%',
				listClass: 'cantidad'
			},
			refacciones: {
				title: 'Refacciones',
				width: '13%',
				listClass: 'cantidad'
			},
			subTotal: {
				title: 'Sub total',
				width: '13%',
				listClass: 'cantidad'
			},
			iva: {
				title: 'Iva',
				width: '13%',
				listClass: 'cantidad'
			},
			total: {
				title: 'Total',
				width: '13%',
				listClass: 'cantidad'
			},
		}
	});
}
function destruirTablas() {
	try {
		$('#DatosServicioContainer').jtable('destroy');
		$('#DatosAutoContainer').jtable('destroy');
		$('#PresupuestoContainer').jtable('destroy');
	} catch(err) {
		//no hacer nada
	}
}
$(document).ready(function () {
	checkLoginInicial();
});