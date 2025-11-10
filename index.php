<?php

// =============================================
// CONFIGURACIÓN Y CONEXIÓN A LA BASE DE DATOS
// =============================================
include    "database/bd.php";

// =============================================
// CONFIGURAR ZONA HORARIA DE PERÚ
// =============================================
date_default_timezone_set('America/Lima');

// =============================================
// CREACION DE LAS TABLAS
// =============================================
include "config/tabla.php";
// =============================================
// CLASE PRINCIPAL DEL SISTEMA DE ASISTENCIA
// =============================================
include "config/principal.php";

// =============================================
// HTML DEL INDEX
// =============================================
include "public/index.php"
?>



