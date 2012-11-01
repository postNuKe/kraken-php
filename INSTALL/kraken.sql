-- phpMyAdmin SQL Dump
-- version 3.5.2.2
-- http://www.phpmyadmin.net
--
-- Servidor: localhost
-- Tiempo de generación: 01-11-2012 a las 14:43:23
-- Versión del servidor: 5.5.27
-- Versión de PHP: 5.4.7

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de datos: `kraken`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE IF NOT EXISTS `categorias` (
  `idCategoria` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `estado` bit(1) NOT NULL DEFAULT b'1',
  `idCategoriaPadre` smallint(5) unsigned NOT NULL DEFAULT '0',
  `col_mat_order` varchar(20) NOT NULL COMMENT 'nombre de la columna a ordenar en los materiales',
  `fechaAlta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idCategoria`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=5 ;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`idCategoria`, `nombre`, `estado`, `idCategoriaPadre`, `col_mat_order`, `fechaAlta`) VALUES
(1, 'Armas', '1', 0, 'numeroSerie', '2010-04-14 15:00:35'),
(2, 'HK G33', '1', 1, 'numeroSerie', '2010-04-14 15:00:35'),
(3, 'Armas depositadas', '1', 1, 'numeroSerie', '2010-04-14 15:00:35'),
(4, 'HK USP Compact', '1', 1, 'numeroSerie', '2010-04-14 15:00:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `empleo`
--

CREATE TABLE IF NOT EXISTS `empleo` (
  `id_empleo` int(2) unsigned NOT NULL,
  `nombre` varchar(32) NOT NULL,
  PRIMARY KEY (`id_empleo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

--
-- Volcado de datos para la tabla `empleo`
--

INSERT INTO `empleo` (`id_empleo`, `nombre`) VALUES
(1, 'Guardia Civil'),
(2, 'Cabo'),
(3, 'Cabo 1º'),
(4, 'Cabo Mayor'),
(5, 'Sargento'),
(6, 'Sargento 1º'),
(7, 'Brigada'),
(8, 'Subteniente'),
(9, 'Suboficial Mayor'),
(10, 'Alferez'),
(11, 'Teniente'),
(12, 'Capitán'),
(13, 'Comandante'),
(14, 'Teniente Coronel'),
(15, 'Coronel');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuadramientos`
--

CREATE TABLE IF NOT EXISTS `encuadramientos` (
  `id_encuadramiento` int(5) unsigned NOT NULL AUTO_INCREMENT,
  `asunto` varchar(100) NOT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `comentarios` text,
  `observaciones` text,
  `ef` text,
  `actividades` text,
  `material` text,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_encuadramiento`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuadramientos_vehiculos`
--

CREATE TABLE IF NOT EXISTS `encuadramientos_vehiculos` (
  `id_ev` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_encuadramiento` int(5) unsigned NOT NULL,
  `id_vehiculo` int(4) unsigned NOT NULL,
  `id_conductor` smallint(5) unsigned NOT NULL,
  `id_transmisiones` smallint(5) DEFAULT NULL,
  `comentarios` text,
  `indicativo` varchar(20) DEFAULT NULL,
  PRIMARY KEY (`id_ev`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encuadramientos_vehiculos_usuarios`
--

CREATE TABLE IF NOT EXISTS `encuadramientos_vehiculos_usuarios` (
  `id_evu` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_encuadramiento` int(5) unsigned NOT NULL,
  `id_vehiculo` int(4) unsigned NOT NULL,
  `id_usuario` smallint(5) unsigned NOT NULL,
  `comentarios` varchar(100) DEFAULT NULL,
  `bocacha` varchar(20) DEFAULT NULL,
  `escudo` varchar(20) DEFAULT NULL,
  `chaleco_balistico` varchar(20) DEFAULT NULL,
  `arma_larga` tinyint(1) NOT NULL DEFAULT '0',
  `seguridad` tinyint(1) NOT NULL DEFAULT '0',
  `base` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id_evu`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estadomaterial`
--

CREATE TABLE IF NOT EXISTS `estadomaterial` (
  `id_estadomaterial` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `fecha_alta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_estadomaterial`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=2 ;

--
-- Volcado de datos para la tabla `estadomaterial`
--

INSERT INTO `estadomaterial` (`id_estadomaterial`, `nombre`, `fecha_alta`) VALUES
(1, 'Roto en almacén', '2010-04-14 15:00:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gasto`
--

CREATE TABLE IF NOT EXISTS `gasto` (
  `gasto_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asunto` varchar(100) NOT NULL,
  `comentarios` text NOT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`gasto_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gasto_material`
--

CREATE TABLE IF NOT EXISTS `gasto_material` (
  `gasto_material_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `gasto_id` int(10) unsigned NOT NULL,
  `categoria` varchar(250) NOT NULL,
  `material` varchar(250) NOT NULL,
  `qty_inserted` smallint(5) unsigned NOT NULL,
  `qty_before` smallint(5) unsigned NOT NULL,
  `qty_after` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`gasto_material_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `material`
--

CREATE TABLE IF NOT EXISTS `material` (
  `idMaterial` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `idCategoria` smallint(5) unsigned NOT NULL,
  `cantidad` smallint(5) unsigned NOT NULL DEFAULT '1',
  `numeroSerie` varchar(100) NOT NULL,
  `lote` varchar(10) NOT NULL,
  `talla` varchar(10) NOT NULL,
  `fecha_fabricacion` varchar(10) NOT NULL,
  `comentarios` text NOT NULL,
  `fechaAlta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idMaterial`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `material_estado`
--

CREATE TABLE IF NOT EXISTS `material_estado` (
  `id_materialestado` mediumint(8) NOT NULL AUTO_INCREMENT,
  `id_material` mediumint(8) NOT NULL,
  `id_estadomaterial` smallint(5) NOT NULL,
  `cantidad` smallint(5) NOT NULL,
  `fecha_alta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comentarios` text NOT NULL,
  PRIMARY KEY (`id_materialestado`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notes`
--

CREATE TABLE IF NOT EXISTS `notes` (
  `id` smallint(5) unsigned NOT NULL,
  `text` varchar(300) NOT NULL,
  `position` varchar(10) NOT NULL,
  `dimension` varchar(10) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `novedad`
--

CREATE TABLE IF NOT EXISTS `novedad` (
  `novedad_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asunto` varchar(100) NOT NULL,
  `comentarios` text NOT NULL,
  `user_id` smallint(5) NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`novedad_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sa9`
--

CREATE TABLE IF NOT EXISTS `sa9` (
  `id_sa9` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `asunto` varchar(100) NOT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_sa9`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sa9_usuarios`
--

CREATE TABLE IF NOT EXISTS `sa9_usuarios` (
  `id_sa9` smallint(5) unsigned NOT NULL,
  `id_usuario` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`id_sa9`,`id_usuario`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `salida`
--

CREATE TABLE IF NOT EXISTS `salida` (
  `salida_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `asunto` varchar(50) NOT NULL,
  `comentarios` text NOT NULL,
  `responsable` smallint(5) unsigned NOT NULL,
  `date_start` timestamp NULL DEFAULT NULL,
  `date_end` timestamp NULL DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`salida_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `salida_material`
--

CREATE TABLE IF NOT EXISTS `salida_material` (
  `salida_id` int(10) unsigned NOT NULL,
  `idMaterial` mediumint(8) unsigned NOT NULL,
  `qty` smallint(5) unsigned NOT NULL,
  PRIMARY KEY (`salida_id`,`idMaterial`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `user_log`
--

CREATE TABLE IF NOT EXISTS `user_log` (
  `user_id` smallint(5) unsigned NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `text` text NOT NULL,
  `id` int(20) unsigned NOT NULL AUTO_INCREMENT,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE IF NOT EXISTS `usuarios` (
  `idUsuario` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(30) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `dni` char(20) NOT NULL,
  `tip` varchar(20) NOT NULL,
  `telf1` varchar(20) DEFAULT NULL,
  `telf2` varchar(20) DEFAULT NULL,
  `comentarios` text NOT NULL,
  `id_empleo` int(2) unsigned NOT NULL,
  `fechaAlta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `activo` tinyint(1) NOT NULL COMMENT 'Si el usuario sigue estando en la Unidad o no',
  `fecha_inactivo` timestamp NULL DEFAULT NULL COMMENT 'fecha en la que se da de baja en la Unidad',
  `order` int(4) unsigned NOT NULL DEFAULT '0',
  `password` varchar(100) DEFAULT NULL,
  `role` int(3) unsigned NOT NULL COMMENT 'Si el usuario es admin o algun role en el kraken',
  PRIMARY KEY (`idUsuario`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 AUTO_INCREMENT=121 ;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`idUsuario`, `nombre`, `apellidos`, `dni`, `tip`, `telf1`, `telf2`, `comentarios`, `id_empleo`, `fechaAlta`, `activo`, `fecha_inactivo`, `order`, `password`, `role`) VALUES
(1, 'Nombre', 'Apellido1 Apellido2', '12345678', 'A-11111-A', '', '', '', 1, '2010-04-15 15:57:36', 1, NULL, 1, '80cd46c824f8e86438816e6e562e969f', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_categorias`
--

CREATE TABLE IF NOT EXISTS `usuarios_categorias` (
  `id_categoria` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(50) NOT NULL,
  `estado` bit(1) NOT NULL DEFAULT b'1',
  `id_categoria_padre` smallint(5) unsigned NOT NULL DEFAULT '0',
  `fecha_alta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_categoria`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_material`
--

CREATE TABLE IF NOT EXISTS `usuarios_material` (
  `idUsuarioMaterial` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `idUsuario` smallint(5) unsigned NOT NULL,
  `idMaterial` mediumint(8) unsigned NOT NULL,
  `idEstado` tinyint(3) unsigned NOT NULL,
  `cantidad` smallint(5) unsigned NOT NULL DEFAULT '1',
  `comentarios` text NOT NULL,
  `fechaAlta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`idUsuarioMaterial`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_material_entregado`
--

CREATE TABLE IF NOT EXISTS `usuarios_material_entregado` (
  `id_usuario_material_entregado` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_usuario` smallint(5) unsigned NOT NULL,
  `id_material` mediumint(8) unsigned NOT NULL,
  `id_estado` tinyint(3) unsigned NOT NULL,
  `cantidad` smallint(5) unsigned NOT NULL DEFAULT '1',
  `comentarios` text NOT NULL,
  `fecha_alta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario_material_entregado`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vars`
--

CREATE TABLE IF NOT EXISTS `vars` (
  `name` varchar(250) NOT NULL,
  `value` varchar(250) NOT NULL,
  `date_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`name`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Variables que se necesitan en la bd para acceder a ellos.';

--
-- Volcado de datos para la tabla `vars`
--

INSERT INTO `vars` (`name`, `value`, `date_modified`) VALUES
('RECUENTO_ID', '1', '2010-05-19 08:20:12'),
('REPORT_ID_CATETORY_ARMA_CORTA', '103', '2010-07-19 09:39:08'),
('REPORT_ID_CATETORY_ARMA_LARGA', '104', '2010-07-19 09:39:08'),
('REPORT_ID_CATETORY_ARMA_ENTREGADA', '105', '2010-07-19 09:39:08'),
('CUADRANTE_COL_DNI', 'AI', '2010-09-18 09:13:00'),
('CUADRANTE_COL_DIAS_INICIO', 'D', '2010-09-18 09:13:00'),
('CUADRANTE_COL_DIAS_FIN', 'AH', '2010-09-18 09:13:00'),
('ID_JEFE_UNIDAD', '1', '2010-09-28 07:55:27'),
('LIST_USERS_NUMBER_CHARACTERS_COMENTARIOS', '100', '2011-02-05 07:56:07'),
('REPORT_ID_CATETORY_ARMAS', '1', '2011-02-14 06:13:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vars_usuarios_material`
--

CREATE TABLE IF NOT EXISTS `vars_usuarios_material` (
  `id_material` mediumint(8) unsigned NOT NULL,
  `cantidad` smallint(5) unsigned NOT NULL DEFAULT '1',
  `fecha_alta` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_material`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Materiales añadidos por defecto a los usuarios. Ir a opciones->Editar configuración para los Usuarios.';

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE IF NOT EXISTS `vehiculos` (
  `id_vehiculo` int(4) unsigned NOT NULL AUTO_INCREMENT,
  `id_disponibilidad` int(3) unsigned NOT NULL DEFAULT '0',
  `nombre` varchar(100) NOT NULL,
  `matricula` varchar(20) NOT NULL,
  `comentarios` text NOT NULL,
  `plazas` int(2) unsigned NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_vehiculo`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COMMENT='Vehiculos' AUTO_INCREMENT=1 ;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos_disponibilidad`
--

CREATE TABLE IF NOT EXISTS `vehiculos_disponibilidad` (
  `id_disponibilidad` int(2) unsigned NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `servicios` varchar(20) NOT NULL COMMENT 'servicios separados por una coma para la creacion automatica de encuadramientos',
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_disponibilidad`)
) ENGINE=MyISAM  DEFAULT CHARSET=latin1 COMMENT='tipos de disponibilidad para los vehiculos' AUTO_INCREMENT=11 ;

--
-- Volcado de datos para la tabla `vehiculos_disponibilidad`
--

INSERT INTO `vehiculos_disponibilidad` (`id_disponibilidad`, `nombre`, `servicios`, `date_added`) VALUES
(1, 'Alerta', 'A,GL,A/TIR', '2010-09-28 08:47:02'),
(2, 'Actividades', 'AC,ACN,J,CU,PIT', '2010-09-28 08:47:09'),
(7, 'Comisión de Servicio', '', '2010-10-01 10:52:59'),
(4, 'Mando', '', '2010-09-28 08:54:49'),
(5, 'Averiado', '', '2010-09-28 08:54:59'),
(6, 'No disponible', '', '2010-09-28 08:55:05'),
(8, 'Disponible', '', '2010-10-01 10:53:21'),
(9, 'P.A.B', '1PAB,2PAB,3PAB,PAB', '2010-11-14 13:36:46'),
(10, 'Seguridad Ciudadana', '', '2010-11-29 10:54:38');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `verbal`
--

CREATE TABLE IF NOT EXISTS `verbal` (
  `id_verbal` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `id_user` smallint(5) NOT NULL,
  `id_emisor` smallint(5) NOT NULL,
  `id_material` mediumint(8) unsigned NOT NULL,
  `asunto` varchar(100) NOT NULL,
  `ejercicio` varchar(100) NOT NULL,
  `narracion` text NOT NULL,
  `date` timestamp NULL DEFAULT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_verbal`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 AUTO_INCREMENT=1 ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
