function showContent(value) {
	if (value) {
		$("#logoutForm").show();
		$("#consultaServicios").show();
	} else {
		$("#logoutForm").hide();
		$("#consultaServicios").hide();
	}
};

function switchLogin(value) {
	if (value) {
		$("#loginForm").show();
		$("#idCliente").prop('disabled', false);
		$("#pswd").prop('disabled', false);
		$("#logoutForm").hide();
	} else {
		$("#loginForm").hide();
		$("#logoutForm").show();
	}
};

function setUser(userName) {
	if(userName) {
		$("#nombreCliente").text(userName);
		switchLogin(false);
		showContent(true);
	} else {
		$("#nombreCliente").text("");
		$("#fechaInicial").val("");
		$("#fechaFinal").val("");
		$('#DatosServicioContainer').jtable('load');
		$('#DatosAutoContainer').jtable('load');
		$('#PresupuestoContainer').jtable('load');
		switchLogin(true);
		showContent(false);
	}
};

function doLogin(callback) {
	$.ajax({
		url: "login.php",
		data: {
			idCliente: $("#idCliente").val(),
			pswd: $("#pswd").val(),
			login: "login"
		},
		success: function(data){
			$("#idCliente").val("");
			$("#pswd").val("");
			callback(data);
		},
		dataType: "json"
	});
	$("#idCliente").prop('disabled', true);
	$("#pswd").prop('disabled', true);
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
	doLogin(function(data) {
		//$("#login").removeAttr("data-loading-text");
		setUser(data.nombreCliente);
		btn.button('reset');
	});
});

$("#logout").click(function() {
	var btn = $(this);
	btn.button('loading');
	doLogout(function(){
		setUser();
		btn.button('reset');
	});
});

$("#buscar").click(function() {
	var btn = $(this);
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
	$("#downloadGlobal").prop('disabled', false);
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
		maxDate: "0d"
	});
});

$(function() {
	$("#fechaFinal").datepicker({
		dateFormat: "yy-mm-dd",
		minDate: "-30d",
		maxDate: "0d"
	});
});

function checkLoginInicial() {
	doCheckLogin(function(data) {
		if (data.tieneSession) {
			setUser(data.nombreCliente);
		} else {
			setUser();
		}	
	});
}

$(document).ready(function () {
	checkLoginInicial();
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
				width: '13%'
			},
			refacciones: {
				title: 'Refacciones',
				width: '13%'
			},
			subTotal: {
				title: 'Sub total',
				width: '13%'
			},
			iva: {
				title: 'Iva',
				width: '13%'
			},
			total: {
				title: 'Total',
				width: '13%'
			},
		}
	});
});