function showContent(value) {
	if (value) {
		$("#logoutForm").show();
		$("#consultaUsuarios").show();
	} else {
		$("#logoutForm").hide();
		$("#consultaUsuarios").hide();
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

function iniciaAdmin(userName) {
	$("#errorReport").html("");
	$("#nombre").text(userName);
	crearTablas();
	switchLogin(false);
	showContent(true);
	$('#UsuariosContainer').jtable('load',
		function() {
		
		}
	);
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
			iniciaAdmin(data.loginDisplay);
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
		switchLogin(true);
		showContent(false);
		btn.button('reset');
		btn.button('reset');
	});
});

function checkLoginInicial() {
	doCheckLogin(function(data) {
		if (data.tieneSession) {
			iniciaAdmin(data.nombre);
		}	
	});
}

function crearTablas() {
	$('#UsuariosContainer').jtable({
		title: 'Usuarios cliente web',
		ajaxSettings: {
			type: 'GET',
			dataType: 'json'
		},
		actions: {
			listAction: 'lista_usuarios.php',
			createAction: 'crea_usuario.php',
			updateAction: 'actualiza_usuario.php',
			deleteAction: 'borra_usuario.php'
		},
		fields: {
			alias: {
				title: 'Nombre',
				width: '34%',
				create: true,
				key: true,
				listClass: 'texto-tabla'
			},
			password: {
				title: 'Password',
				type: 'password',
				list: false
			},
			idCliente: {
				title: 'Cliente',
				options: 'lista_clientes.php',
				width: '33%',
				listClass: 'texto-tabla'
			},
			status: {
				title: 'Status',
				options: {'activo': 'Activo', 'inactivo': 'Inactivo'},
				width: '33%',
				listClass: 'texto-tabla'
			}
		}
	});
}

function destruirTablas() {
	try {
		$('#UsuariosContainer').jtable('destroy');
	} catch(err) {
		//no hacer nada
	}
}

$(document).ready(function () {
	checkLoginInicial();
});