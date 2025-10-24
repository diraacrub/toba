<?php
class ci_base_operaciones extends catedras_ci
{


//***********************************************************************************************
	
	// botones flotantes a la derecha y volver a la izquierda
	function ini_en_suspenso()
{
	static $ejecutado = false;
	if ($ejecutado) return;
	$ejecutado = true;

	echo '<script type="text/javascript">
	(function() {

		// --- CSS para botones flotantes ---
		var css = `
		.btn-flotante {
			position: fixed !important;
			min-width: 120px;
			text-align: center;
			padding: 8px 14px !important;
			border-radius: 5px;
			font-weight: bold;
			color: black !important;
			cursor: pointer;
			z-index: 9999;
		}

		/* Botón VOLVER a la izquierda */
		.btn-flotante.volver {
			left: 20px;
			bottom: 100px;
			background-color: #666 !important;
		}

		/* Botones a la derecha */
		.btn-flotante.guardar  { right: 20px; bottom: 100px; background-color: #28a745 !important; }
		.btn-flotante.imprimir { right: 20px; bottom: 60px;  background-color: #17a2b8 !important; }
		.btn-flotante.eliminar { right: 20px; bottom: 20px;  background-color: #dc3545 !important; }
		`;

		var style = document.createElement("style");
		style.type = "text/css";
		style.appendChild(document.createTextNode(css));
		document.head.appendChild(style);

		window.addEventListener("load", function() {
			// Mapeo de clases originales a clases de flotante
			var map = {
				"ei-boton-volver":   "volver",
				"ei-boton-guardar":  "guardar",
				"ei-boton-imprimir": "imprimir",
				"ei-boton-eliminar": "eliminar"
			};

			Object.keys(map).forEach(function(cls) {
				var original = document.querySelector("." + cls);
				if (!original) return;

				// Ocultar el botón original
				original.style.display = "none";

				// Crear botón flotante clonando contenido
				var clone = document.createElement("button");
				clone.className = "btn-flotante " + map[cls];
				clone.innerHTML = original.innerHTML; // copia texto + ícono

				// Clon dispara el click del original
				clone.addEventListener("click", function() {
					original.click();
				});

				document.body.appendChild(clone);
			});
		});
	})();
	</script>';
}

	
	
// todos a la derecha abajo    
	function ini_derecha()
{
	static $ejecutado = false;
	if ($ejecutado) return;
	$ejecutado = true;

	echo '<script type="text/javascript">
	(function() {

		// --- CSS para botones flotantes ---
		var css = `
		.btn-flotante {
			position: fixed !important;
			right: 20px;
			min-width: 120px;
			text-align: center;
			padding: 8px 14px !important;
			border-radius: 5px;
			font-weight: bold;
			color: black !important;
			cursor: pointer;
			z-index: 9999;
		}
		.btn-flotante.volver   { bottom: 140px; background-color: #666 !important; }
		.btn-flotante.guardar  { bottom: 100px; background-color: #28a745 !important; }
		.btn-flotante.imprimir { bottom:  60px; background-color: #17a2b8 !important; }
		.btn-flotante.eliminar { bottom:  20px; background-color: #dc3545 !important; }
		`;
		var style = document.createElement("style");
		style.type = "text/css";
		style.appendChild(document.createTextNode(css));
		document.head.appendChild(style);

		window.addEventListener("load", function() {
			// Mapeo de clases originales a clases de flotante
			var map = {
				"ei-boton-volver":   "volver",
				"ei-boton-guardar":  "guardar",
				"ei-boton-imprimir": "imprimir",
				"ei-boton-eliminar": "eliminar"
			};

			Object.keys(map).forEach(function(cls) {
				var original = document.querySelector("." + cls);
				if (!original) return;

				// Ocultar el botón original
				original.style.display = "none";

				// Crear botón flotante clonando contenido
				var clone = document.createElement("button");
				clone.className = "btn-flotante " + map[cls];
				clone.innerHTML = original.innerHTML; // copia texto + ícono

				// Clon dispara el click del original
				clone.addEventListener("click", function() {
					original.click();
				});

				document.body.appendChild(clone);
			});
		});
	})();
	</script>';
}
	
	
	
	// esta función es la última versión pero no me cierra
	
		function ini_botones_y_hamburguesa_ultima()
{
	static $ejecutado = false;
	if ($ejecutado) return;
	$ejecutado = true;

	echo '<script type="text/javascript">
	(function() {

		// --- CSS ---
		var css = `
		/* Botones flotantes */
		.btn-flotante {
			position: fixed !important;
			right: 20px;
			min-width: 120px;
			text-align: center;
			padding: 8px 14px !important;
			border-radius: 5px;
			font-weight: bold;
			color: black !important;
			cursor: pointer;
			z-index: 9999;
		}
		.btn-flotante.volver   { bottom: 140px; background-color: #666 !important; }
		.btn-flotante.guardar  { bottom: 100px; background-color: #28a745 !important; }
		.btn-flotante.imprimir { bottom:  60px; background-color: #17a2b8 !important; }
		.btn-flotante.eliminar { bottom:  20px; background-color: #dc3545 !important; }

		/* Menú original visible solo en PC */
		@media (min-width: 992px) {
			#menu-h { display: flex !important; }
			#menu-toggle { display: none !important; }
		}

		/* Menú hamburguesa visible solo en móvil */

@media (max-width: 991px) {
	#menu-h {
		display: none;
		flex-direction: column;
		background-color: #244268; /* fondo del menú principal */
		padding: 10px;
		margin: 0;
	}
	#menu-h.active {
		display: flex !important;
	}
	#menu-toggle {
		display: block !important;
		font-size: 60px;
		color: white;
		background: none;
		border: none;
		cursor: pointer;
		margin: 8px 0;
	}
	#menu-h li a {
		color: white !important;
		font-size: 50px !important;
		padding: 10px 15px !important;
		display: block;
	}

	/* --- Submenú con fondo claro y letras oscuras --- */
	#menu-h li ul {
		margin-left: 80px;             /* &#128313; más desplazado a la derecha */
		padding-left: 0;
		border-left: none;
		background-color: #f4f4f4;     /* fondo clarito */
		border-radius: 8px;
		margin-top: 5px;
		box-shadow: 2px 2px 6px rgba(0,0,0,0.1); /* leve separación visual */
	}

	#menu-h li ul li a {
		font-size: 45px !important;
		color: #222 !important;         /* texto oscuro */
		padding: 10px 25px !important;
	}

	#menu-h li ul li a:hover {
		background-color: #e0e0e0;      /* leve sombreado al pasar */
		text-decoration: none;
	}

	/* --- Ítem principal con submenú (hover azul oscuro) --- */
	#menu-h > li:hover > a {
		background-color: rgba(255,255,255,0.15);
		color: #001f3f !important;      /* &#128313; azul oscuro */
		font-weight: bold;
	}
}

		
		
		

		`;
		var style = document.createElement("style");
		style.type = "text/css";
		style.appendChild(document.createTextNode(css));
		document.head.appendChild(style);

		// --- Función para inicializar menú hamburguesa ---
		function initMenuHamburguesa() {
			if (window.innerWidth >= 992) return; // solo móviles/tablets

			var menu = document.querySelector("#menu-h");
			if (!menu) return;

			// Evitar duplicar botón
			if (!document.querySelector("#menu-toggle")) {
				var toggle = document.createElement("button");
				toggle.id = "menu-toggle";
				toggle.innerHTML = "&#9776; MENU";
				menu.parentNode.insertBefore(toggle, menu);

				toggle.addEventListener("click", function() {
					menu.classList.toggle("active");
				});
			}

			// Submenús
			document.querySelectorAll("#menu-h > li > a").forEach(function(link){
				link.addEventListener("click", function(e){
					var parentLi = link.parentNode;
					var submenu = parentLi.querySelector("ul");
					if (submenu) {
						e.preventDefault();
						document.querySelectorAll("#menu-h li.active").forEach(function(openLi){
							if (openLi !== parentLi) openLi.classList.remove("active");
						});
						parentLi.classList.toggle("active");
					}
				});
			});
		}

		// --- Botones flotantes ---
		function initBotones() {
			var map = {
				"ei-boton-volver":   "volver",
				"ei-boton-guardar":  "guardar",
				"ei-boton-imprimir": "imprimir",
				"ei-boton-eliminar": "eliminar"
			};
			Object.keys(map).forEach(function(cls) {
				var original = document.querySelector("." + cls);
				if (!original) return;
				original.style.display = "none";
				var clone = document.createElement("button");
				clone.className = "btn-flotante " + map[cls];
				clone.innerHTML = original.innerHTML;
				clone.addEventListener("click", function() {
					original.click();
				});
				document.body.appendChild(clone);
			});
		}

		// --- Ejecutar cuando el DOM está listo ---
		function ready(fn) {
			if (document.readyState != "loading") fn();
			else document.addEventListener("DOMContentLoaded", fn);
		}

		ready(function() {
			var tries = 0;
			var interval = setInterval(function() {
				var menu = document.querySelector("#menu-h");
				if (menu || tries > 20) {
					initMenuHamburguesa();
					initBotones();
					clearInterval(interval);
				}
				tries++;
			}, 200);
		});

	})();
	</script>';
	
	
	echo "<!-- base_operaciones " . date('YmdHis') . " -->";

}

	
	// con esta función llama al custom css de /home/ignaciobasti/toba-desarrollo/www/css/custom.css
	function ini_custom()
{
	static $ejecutado = false;
	if ($ejecutado) return;
	$ejecutado = true;

	echo '<link rel="stylesheet" type="text/css" href="/catedras/1.0/css/custom.css">';
}

	
	
	
	
	function ini_no() //si está en previsualizacion no corre
{
	static $ejecutado = false;
	if ($ejecutado) return;
	$ejecutado = true;

	echo '<script type="text/javascript">
	(function() {

		// &#128683; No ejecutar si estamos en modo previsualización de Toba
		if (window.location.href.includes("tcm=previsualizacion")) return;

		// --- CSS ---
		var css = `
		/* Botones flotantes */
		.btn-flotante {
			position: fixed !important;
			right: 20px;
			min-width: 120px;
			text-align: center;
			padding: 8px 14px !important;
			border-radius: 5px;
			font-weight: bold;
			color: black !important;
			cursor: pointer;
			z-index: 9999;
		}
		.btn-flotante.volver   { bottom: 140px; background-color: #666 !important; }
		.btn-flotante.guardar  { bottom: 100px; background-color: #28a745 !important; }
		.btn-flotante.imprimir { bottom:  60px; background-color: #17a2b8 !important; }
		.btn-flotante.eliminar { bottom:  20px; background-color: #dc3545 !important; }

		/* Menú original visible solo en PC */
		@media (min-width: 992px) {
			#menu-h { display: flex !important; }
			#menu-toggle { display: none !important; }
		}

		/* Menú hamburguesa visible solo en móvil */
		@media (max-width: 991px) {
			#menu-h {
				display: none;
				flex-direction: column;
				background-color: #244268;
				padding: 10px;
				margin: 0;
			}
			#menu-h.active {
				display: flex !important;
			}
			#menu-toggle {
				display: block !important;
				font-size: 60px;
				color: white;
				background: none;
				border: none;
				cursor: pointer;
				margin: 8px 0;
			}
			#menu-h li a {
				color: white !important;
				font-size: 50px !important;
				padding: 10px 15px !important;
			}
		}
		`;
		var style = document.createElement("style");
		style.type = "text/css";
		style.appendChild(document.createTextNode(css));
		document.head.appendChild(style);

		// --- Función para inicializar menú hamburguesa ---
		function initMenuHamburguesa() {
			if (window.innerWidth >= 992) return; // solo móviles/tablets

			var menu = document.querySelector("#menu-h");
			if (!menu) return;

			// Evitar duplicar botón
			if (!document.querySelector("#menu-toggle")) {
				var toggle = document.createElement("button");
				toggle.id = "menu-toggle";
				toggle.innerHTML = "&#9776; MENU";
				menu.parentNode.insertBefore(toggle, menu);

				toggle.addEventListener("click", function() {
					menu.classList.toggle("active");
				});
			}

			// Submenús
			document.querySelectorAll("#menu-h > li > a").forEach(function(link){
				link.addEventListener("click", function(e){
					var parentLi = link.parentNode;
					var submenu = parentLi.querySelector("ul");
					if (submenu) {
						e.preventDefault();
						document.querySelectorAll("#menu-h li.active").forEach(function(openLi){
							if (openLi !== parentLi) openLi.classList.remove("active");
						});
						parentLi.classList.toggle("active");
					}
				});
			});
		}

		// --- Botones flotantes ---
		function initBotones() {
			var map = {
				"ei-boton-volver":   "volver",
				"ei-boton-guardar":  "guardar",
				"ei-boton-imprimir": "imprimir",
				"ei-boton-eliminar": "eliminar"
			};
			Object.keys(map).forEach(function(cls) {
				var original = document.querySelector("." + cls);
				if (!original) return;
				original.style.display = "none";
				var clone = document.createElement("button");
				clone.className = "btn-flotante " + map[cls];
				clone.innerHTML = original.innerHTML;
				clone.addEventListener("click", function() {
					original.click();
				});
				document.body.appendChild(clone);
			});
		}

		// --- Ejecutar cuando el DOM está listo ---
		function ready(fn) {
			if (document.readyState != "loading") fn();
			else document.addEventListener("DOMContentLoaded", fn);
		}

		ready(function() {
			var tries = 0;
			var interval = setInterval(function() {
				var menu = document.querySelector("#menu-h");
				if (menu || tries > 20) {
					initMenuHamburguesa();
					initBotones();
					clearInterval(interval);
				}
				tries++;
			}, 200);
		});

	})();
	</script>';
}

	
	

function ini_tampoco()
{
	static $ejecutado = false;
	if ($ejecutado) return;
	$ejecutado = true;

	echo '<script type="text/javascript">
	(function() {

		// --- CSS ---
		var css = `
		/* Botones flotantes */
		.btn-flotante {
			position: fixed !important;
			right: 20px;
			min-width: 120px;
			text-align: center;
			padding: 8px 14px !important;
			border-radius: 5px;
			font-weight: bold;
			color: black !important;
			cursor: pointer;
			z-index: 9999;
		}
		.btn-flotante.volver   { bottom: 140px; background-color: #666 !important; }
		.btn-flotante.guardar  { bottom: 100px; background-color: #28a745 !important; }
		.btn-flotante.imprimir { bottom:  60px; background-color: #17a2b8 !important; }
		.btn-flotante.eliminar { bottom:  20px; background-color: #dc3545 !important; }

		/* Botón hamburguesa */
		#menu-toggle {
			font-size: 50px !important;
			padding: 10px 15px !important;
			display: inline-block;
			color: white;
			background: none;
			border: none;
			cursor: pointer;
			margin-bottom: 5px;
		}

		/* Menú hamburguesa */
		#menu-h {
			display: none;
			flex-direction: column;
			background-color: #244268;
			padding: 10px;
			margin: 0;
		}
		#menu-h.active { display: flex !important; }
		#menu-h li a {
			float: none !important;
			text-align: left !important;
			border: none !important;
			background: transparent !important;
			display: block;
			color: white !important;
			text-decoration: none;
			font-size: 40px !important;
			padding: 12px 15px !important;
		}
		#menu-h li a:hover { background-color: #53689f !important; }

		/* Submenús verticales, fondo oscuro */
		#menu-h li ul {
			position: static !important;
			display: none;
			padding-left: 15px;
			margin: 0;
			flex-direction: column;
			background-color: #1b2d4a !important;
			border: none !important;
		}
		#menu-h li.active > ul { display: flex !important; }
		#menu-h li ul li a {
			font-size: 14px;
			padding: 8px 10px;
			color: #ddd !important;
		}
		#menu-h li ul li a:hover {
			background-color: #2a3b66 !important;
			color: white !important;
		}
		`;
		var style = document.createElement("style");
		style.type = "text/css";
		style.appendChild(document.createTextNode(css));
		document.head.appendChild(style);

		// --- Inicializar menú ---
		function initMenu() {
			var menu = document.querySelector("#menu-h");
			if (!menu) return;

			// Aplicar hamburguesa solo en pantallas < 992px
			if(window.innerWidth >= 992) {
				menu.style.display = "block"; // menú original en PC
				return;
			}

			if (!document.querySelector("#menu-toggle")) {
				var toggle = document.createElement("button");
				toggle.id = "menu-toggle";
				toggle.innerHTML = "&#9776; MENU";
				menu.parentNode.insertBefore(toggle, menu);

				toggle.addEventListener("click", function() {
					menu.classList.toggle("active");
				});
			}

			// Submenús: solo un submenu abierto a la vez
			document.querySelectorAll("#menu-h > li > a").forEach(function(link){
				link.addEventListener("click", function(e){
					var parentLi = link.parentNode;
					var submenu = parentLi.querySelector("ul");
					if(submenu){
						e.preventDefault();
						document.querySelectorAll("#menu-h li.active").forEach(function(openLi){
							if(openLi !== parentLi) openLi.classList.remove("active");
						});
						parentLi.classList.toggle("active");
					}
				});
			});
		}

		// --- Inicializar botones flotantes ---
		function initBotones() {
			var map = {
				"ei-boton-volver":   "volver",
				"ei-boton-guardar":  "guardar",
				"ei-boton-imprimir": "imprimir",
				"ei-boton-eliminar": "eliminar"
			};

			Object.keys(map).forEach(function(cls) {
				var original = document.querySelector("." + cls);
				if (!original) return;

				original.style.display = "none";

				var clone = document.createElement("button");
				clone.className = "btn-flotante " + map[cls];
				clone.innerHTML = original.innerHTML;

				clone.addEventListener("click", function() {
					original.click();
				});

				document.body.appendChild(clone);
			});
		}

		// --- Ejecutar al cargar ---
		window.addEventListener("load", function() {
			var tries = 0;
			var maxTries = 20;
			var interval = setInterval(function() {
				var menu = document.querySelector("#menu-h");
				if (menu || tries > maxTries) {
					initMenu();
					initBotones();
					clearInterval(interval);
				}
				tries++;
			}, 200);
		});

	})();
	</script>';
}













	function ini_avecessiyno()
{
	static $ejecutado = false;
	if ($ejecutado) return;
	$ejecutado = true;

	echo '<script type="text/javascript">
	(function() {

		// --- CSS ---
		var css = `
		/* Botones flotantes */
		.btn-flotante {
			position: fixed !important;
			right: 20px;
			min-width: 120px;
			text-align: center;
			padding: 8px 14px !important;
			border-radius: 5px;
			font-weight: bold;
			color: black !important;
			cursor: pointer;
			z-index: 9999;
		}
		.btn-flotante.volver   { bottom: 140px; background-color: #666 !important; }
		.btn-flotante.guardar  { bottom: 100px; background-color: #28a745 !important; }
		.btn-flotante.imprimir { bottom:  60px; background-color: #17a2b8 !important; }
		.btn-flotante.eliminar { bottom:  20px; background-color: #dc3545 !important; }

		/* Botón hamburguesa */
		#menu-toggle {
			font-size: 50px !important;
			padding: 10px 15px !important;
			display: inline-block;
			color: white;
			background: none;
			border: none;
			cursor: pointer;
			margin-bottom: 5px;
		}

		/* Menú hamburguesa */
		#menu-h {
			display: none;
			flex-direction: column;
			background-color: #244268;
			padding: 10px;
			margin: 0;
		}
		#menu-h.active {
			display: flex !important;
		}
		#menu-h li a {
			float: none !important;
			text-align: left !important;
			border: none !important;
			background: transparent !important;
			display: block;
			color: white !important;
			text-decoration: none;
			font-size: 40px !important;
			padding: 12px 15px !important;
		}
		#menu-h li a:hover {
			background-color: #53689f !important;
		}

		/* Submenús verticales, fondo oscuro */
		#menu-h li ul {
			position: static !important;
			left: auto !important;
			top: auto !important;
			display: none;
			padding-left: 15px;
			margin: 0;
			flex-direction: column;
			background-color: #1b2d4a !important;
			border: none !important;
		}
		#menu-h li.active > ul {
			display: flex !important;
		}
		#menu-h li ul li a {
			font-size: 14px;
			padding: 8px 10px;
			color: #ddd !important;
		}
		#menu-h li ul li a:hover {
			background-color: #2a3b66 !important;
			color: white !important;
		}
		`;
		var style = document.createElement("style");
		style.type = "text/css";
		style.appendChild(document.createTextNode(css));
		document.head.appendChild(style);

		// --- Inicializar menú hamburguesa SOLO en pantallas chicas ---
		function initMenu() {
			if(window.innerWidth >= 992) return; // PC: no aplicar hamburguesa

			var menu = document.querySelector("#menu-h");
			if (!menu) return;

			// Crear botón hamburguesa si no existe
			if (!document.querySelector("#menu-toggle")) {
				var toggle = document.createElement("button");
				toggle.id = "menu-toggle";
				toggle.innerHTML = "&#9776; MENU";
				menu.parentNode.insertBefore(toggle, menu);

				toggle.addEventListener("click", function() {
					menu.classList.toggle("active");
				});
			}

			// Submenús: solo un submenu abierto a la vez
			document.querySelectorAll("#menu-h > li > a").forEach(function(link){
				link.addEventListener("click", function(e){
					var parentLi = link.parentNode;
					var submenu = parentLi.querySelector("ul");
					if(submenu){
						e.preventDefault();
						document.querySelectorAll("#menu-h li.active").forEach(function(openLi){
							if(openLi !== parentLi) openLi.classList.remove("active");
						});
						parentLi.classList.toggle("active");
					}
				});
			});
		}

		// --- Inicializar botones flotantes SIEMPRE ---
		function initBotones() {
			var map = {
				"ei-boton-volver":   "volver",
				"ei-boton-guardar":  "guardar",
				"ei-boton-imprimir": "imprimir",
				"ei-boton-eliminar": "eliminar"
			};
			Object.keys(map).forEach(function(cls) {
				var original = document.querySelector("." + cls);
				if (!original) return;
				original.style.display = "none";

				var clone = document.createElement("button");
				clone.className = "btn-flotante " + map[cls];
				clone.innerHTML = original.innerHTML;

				clone.addEventListener("click", function() {
					original.click();
				});
				document.body.appendChild(clone);
			});
		}

		// --- Ejecutar al cargar ---
		window.addEventListener("load", function() {
			var tries = 0;
			var maxTries = 20;
			var interval = setInterval(function() {
				if (document.querySelector("#menu-h") || tries > maxTries) {
					initMenu();
					initBotones();
					clearInterval(interval);
				}
				tries++;
			}, 200);
		});

	})();
	</script>';
}





	//******************************************************************************************************************
	
	function ini_siempre_hamburguesa()
{
	static $ejecutado = false;
	if ($ejecutado) return;
	$ejecutado = true;

	echo '<script type="text/javascript">
	(function() {

		// --- CSS para botones flotantes (siempre aplicado) ---
		var cssBotones = `
		.btn-flotante {
			position: fixed !important;
			right: 20px;
			min-width: 120px;
			text-align: center;
			padding: 8px 14px !important;
			border-radius: 5px;
			font-weight: bold;
			color: black !important;
			cursor: pointer;
			z-index: 9999;
		}
		.btn-flotante.volver   { bottom: 140px; background-color: #666 !important; }
		.btn-flotante.guardar  { bottom: 100px; background-color: #28a745 !important; }
		.btn-flotante.imprimir { bottom:  60px; background-color: #17a2b8 !important; }
		.btn-flotante.eliminar { bottom:  20px; background-color: #dc3545 !important; }
		`;
		var styleBotones = document.createElement("style");
		styleBotones.type = "text/css";
		styleBotones.appendChild(document.createTextNode(cssBotones));
		document.head.appendChild(styleBotones);

		// --- Inicializar menú ---
		function initMenu() {
			if (window.innerWidth > 1024) return; // PC -> no cambiar menú

			// --- CSS menú hamburguesa (solo móviles/tablets) ---
			var cssMenu = `
			#menu-toggle {
				font-size: 50px !important;
				padding: 10px 15px !important;
				display: inline-block;
				color: white;
				background: none;
				border: none;
				cursor: pointer;
				margin-bottom: 5px;
			}
			#menu-h {
				display: none;
				flex-direction: column;
				background-color: #244268;
				padding: 10px;
				margin: 0;
			}
			#menu-h.active { display: flex !important; }
			#menu-h li a {
				float: none !important;
				text-align: left !important;
				border: none !important;
				background: transparent !important;
				display: block;
				color: white !important;
				text-decoration: none;
				font-size: 40px !important;
				padding: 12px 15px !important;
			}
			#menu-h li a:hover { background-color: #53689f !important; }
			#menu-h li ul {
				position: static !important;
				left: auto !important;
				top: auto !important;
				display: none;
				padding-left: 15px;
				margin: 0;
				flex-direction: column;
				background-color: #1b2d4a !important;
				border: none !important;
			}
			#menu-h li.active > ul { display: flex !important; }
			#menu-h li ul li a {
				font-size: 14px;
				padding: 8px 10px;
				color: #ddd !important;
			}
			#menu-h li ul li a:hover {
				background-color: #2a3b66 !important;
				color: white !important;
			}
			`;
			var styleMenu = document.createElement("style");
			styleMenu.type = "text/css";
			styleMenu.appendChild(document.createTextNode(cssMenu));
			document.head.appendChild(styleMenu);

			var menu = document.querySelector("#menu-h");
			if (!menu) return;

			if (!document.querySelector("#menu-toggle")) {
				var toggle = document.createElement("button");
				toggle.id = "menu-toggle";
				toggle.innerHTML = "&#9776; MENU";
				menu.parentNode.insertBefore(toggle, menu);

				toggle.addEventListener("click", function() {
					menu.classList.toggle("active");
				});
			}

			document.querySelectorAll("#menu-h > li > a").forEach(function(link){
				link.addEventListener("click", function(e){
					var parentLi = link.parentNode;
					var submenu = parentLi.querySelector("ul");
					if(submenu){
						e.preventDefault();
						document.querySelectorAll("#menu-h li.active").forEach(function(openLi){
							if(openLi !== parentLi) openLi.classList.remove("active");
						});
						parentLi.classList.toggle("active");
					}
				});
			});
		}

		// --- Inicializar botones flotantes ---
		function initBotones() {
			var map = {
				"ei-boton-volver":   "volver",
				"ei-boton-guardar":  "guardar",
				"ei-boton-imprimir": "imprimir",
				"ei-boton-eliminar": "eliminar"
			};
			Object.keys(map).forEach(function(cls) {
				var original = document.querySelector("." + cls);
				if (!original) return;
				original.style.display = "none";
				var clone = document.createElement("button");
				clone.className = "btn-flotante " + map[cls];
				clone.innerHTML = original.innerHTML;
				clone.addEventListener("click", function() { original.click(); });
				document.body.appendChild(clone);
			});
		}

		// --- Ejecutar al cargar ---
		window.addEventListener("load", function() {
			var tries = 0;
			var maxTries = 20;
			var interval = setInterval(function() {
				if (document.querySelector("#menu-h") || tries > maxTries) {
					initMenu();     // solo cambia menú si pantalla pequeña
					initBotones();  // siempre flotantes
					clearInterval(interval);
				}
				tries++;
			}, 200);
		});

	})();
	</script>';
}

	
	
	
	
	
	
	
	
	
	
	
	//***************************************************************************************
	//***************************************************************************************
	function ini_hamburgues_para_todes()
	//function ini()
{
	static $ejecutado = false;
	if ($ejecutado) return;
	$ejecutado = true;

	echo '<script type="text/javascript">
	(function() {

		// --- CSS ---
		var css = `
		/* Botones flotantes */
		.btn-flotante {
			position: fixed !important;
			right: 20px;
			min-width: 120px;
			text-align: center;
			padding: 8px 14px !important;
			border-radius: 5px;
			font-weight: bold;
			color: black !important;
			cursor: pointer;
			z-index: 9999;
		}
		.btn-flotante.volver   { bottom: 140px; background-color: #666 !important; }
		.btn-flotante.guardar  { bottom: 100px; background-color: #28a745 !important; }
		.btn-flotante.imprimir { bottom:  60px; background-color: #17a2b8 !important; }
		.btn-flotante.eliminar { bottom:  20px; background-color: #dc3545 !important; }

		/* Botón hamburguesa */
		#menu-toggle {
			font-size: 50px !important;       /* botón hamburguesa más grande */
			padding: 10px 15px !important;
			display: inline-block;
			color: white;
			background: none;
			border: none;
			cursor: pointer;
			margin-bottom: 5px;
		}

		/* Menú hamburguesa */
		#menu-h {
			display: none;
			flex-direction: column;
			background-color: #244268;
			padding: 10px;
			margin: 0;
		}
		#menu-h.active {
			display: flex !important;
		}
		#menu-h li a {
			float: none !important;
			text-align: left !important;
			border: none !important;
			background: transparent !important;
			display: block;
			color: white !important;
			text-decoration: none;
			font-size: 40px !important;       /* mayor tamaño de fuente */
			padding: 12px 15px !important;    /* más espacio para tocar/leer */
		}
		#menu-h li a:hover {
			background-color: #53689f !important;
		}

		/* Submenús verticales, fondo oscuro */
		#menu-h li ul {
			position: static !important;
			left: auto !important;
			top: auto !important;
			display: none;
			padding-left: 15px;
			margin: 0;
			flex-direction: column;
			background-color: #1b2d4a !important;
			border: none !important;
		}
		#menu-h li.active > ul {
			display: flex !important;
		}
		#menu-h li ul li a {
			font-size: 14px;
			padding: 8px 10px;
			color: #ddd !important;
		}
		#menu-h li ul li a:hover {
			background-color: #2a3b66 !important;
			color: white !important;
		}
		`;
		var style = document.createElement("style");
		style.type = "text/css";
		style.appendChild(document.createTextNode(css));
		document.head.appendChild(style);

		// --- Inicializar menú ---
		function initMenu() {
			var menu = document.querySelector("#menu-h");
			if (!menu) return;

			// Crear botón hamburguesa si no existe
			if (!document.querySelector("#menu-toggle")) {
				var toggle = document.createElement("button");
				toggle.id = "menu-toggle";
				toggle.innerHTML = "&#9776; MENU";
				menu.parentNode.insertBefore(toggle, menu);

				toggle.addEventListener("click", function() {
					menu.classList.toggle("active");
				});
			}

			// Submenús: solo un submenu abierto a la vez
			document.querySelectorAll("#menu-h > li > a").forEach(function(link){
				link.addEventListener("click", function(e){
					var parentLi = link.parentNode;
					var submenu = parentLi.querySelector("ul");
					if(submenu){
						e.preventDefault(); // evita ir a href #
						
						// cerrar cualquier submenú abierto
						document.querySelectorAll("#menu-h li.active").forEach(function(openLi){
							if(openLi !== parentLi) {
								openLi.classList.remove("active");
							}
						});

						// abrir/cerrar el submenú clickeado
						parentLi.classList.toggle("active");
					}
				});
			});
		}

		// --- Inicializar botones flotantes ---
		function initBotones() {
			var map = {
				"ei-boton-volver":   "volver",
				"ei-boton-guardar":  "guardar",
				"ei-boton-imprimir": "imprimir",
				"ei-boton-eliminar": "eliminar"
			};

			Object.keys(map).forEach(function(cls) {
				var original = document.querySelector("." + cls);
				if (!original) return;

				original.style.display = "none";

				var clone = document.createElement("button");
				clone.className = "btn-flotante " + map[cls];
				clone.innerHTML = original.innerHTML;

				clone.addEventListener("click", function() {
					original.click();
				});

				document.body.appendChild(clone);
			});
		}

		// --- Ejecutar al cargar ---
		window.addEventListener("load", function() {
			var tries = 0;
			var maxTries = 20;
			var interval = setInterval(function() {
				if (document.querySelector("#menu-h") || tries > maxTries) {
					initMenu();
					initBotones();
					clearInterval(interval);
				}
				tries++;
			}, 200);
		});

	})();
	</script>';
}

	
	
	
	
	

	


	

	
}
?>