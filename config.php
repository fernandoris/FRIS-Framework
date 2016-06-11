<?php
// Configuración del servidor de la base de datos

define('SERVIDOR', 'localhost');
define('USUARIO', 'root');
define('PASS', 'vagrant');
define('DATABASE', 'FRISFW');

define("DBCHARSET","utf8");

// La ruta raiz de la aplicacion, así podemos acceder facilmente a todos los directorios de proyecto
define("APP_PATH", dirname(__FILE__));

// Usaremos esto para asegurarnos de que los scripts no son llamados desde fuera del framework
define("FRISFW", true);

// Nombre del template que vamos a cargar
define('TEMPLATENAME', 'default');

// Nombre del dominio donde estará instalada la página
define('DOMAIN','loacalhost:8080');

// Ruta al direcctorio donde está instalado el FRAMEWORK
define('STARTPATH','/');

// Número de horas que estará activa la sesión de los usuarios
define('HOURS_SESSION',8);

?>
