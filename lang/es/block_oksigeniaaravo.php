<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Cadenas de block_oksigeniaaravo.
 *
 * @package    block_oksigeniaaravo
 * @copyright  2026 Oksigenia SL <dev@oksigenia.cc>
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Horas de soporte (Oksigenia Aravo)';

// Capacidades.
$string['oksigeniaaravo:addinstance'] = 'Añadir un bloque de horas de soporte';
$string['oksigeniaaravo:myaddinstance'] = 'Añadir un bloque de horas de soporte al área personal';
$string['oksigeniaaravo:view'] = 'Ver las horas de soporte: el bloque, el registro y la descarga';

// Ajustes.
$string['centralendpoint'] = 'Dirección del registro central';
$string['centralendpoint_desc'] = 'La dirección HTTPS completa de la que el bloque lee los datos de soporte.';
$string['centraltoken'] = 'Clave de acceso (opcional)';
$string['centraltoken_desc'] = 'Solo hace falta para un servidor que el central no reconozca por su IP. En un servidor gestionado, déjela vacía.';
$string['provider'] = 'Nombre del proveedor';
$string['provider_desc'] = 'Se muestra como título del bloque cuando el central no envía uno, por ejemplo «Soporte de Oksigenia».';
$string['pagettl'] = 'Refrescar al abrir (minutos)';
$string['pagettl_desc'] = 'Al abrir el registro, si la copia local es más antigua que esto, se vuelve a leer del central.';

// Tarea programada.
$string['task_refresh'] = 'Leer las horas de soporte del registro central';

// Un mes y un año, como formato de fecha para userdate (%B es el nombre del mes).
$string['monthyearformat'] = '%B de %Y';

// Duraciones.
$string['durationhm'] = '{$a->h} h {$a->m} min';
$string['durationh'] = '{$a->h} h';
$string['durationm'] = '{$a->m} min';

// Bloque y registro.
$string['blocktitle'] = 'Horas de soporte';
$string['bagtitle'] = 'Bolsa de horas';
$string['bagline'] = 'Quedan {$a->remaining} de {$a->purchased} · usadas {$a->used}';
$string['bagstatuslow'] = 'Saldo bajo';
$string['bagstatusempty'] = 'Bolsa agotada';
$string['couldnotupdate'] = 'No se han podido actualizar los datos; se muestra la última copia.';
$string['includedtitle'] = 'Incluido sin coste';
$string['recenttitle'] = 'Últimos trabajos';
$string['fullregister'] = 'Registro completo';
$string['updatedat'] = 'Actualizado el {$a->date} a las {$a->time}';
$string['nodatayet'] = 'Todavía no hay nada que mostrar.';
$string['registertitle'] = 'Horas de soporte';
$string['summarytitle'] = 'Por mes';
$string['worktitle'] = 'Registro de trabajos';
$string['centraladmin'] = 'Abrir en el registro central (administradores)';

// Tarjeta plegable.
$string['summarybag'] = '{$a} en la bolsa';
$string['summarymonth'] = '{$a->used} de {$a->allowed} este mes';
$string['summarydefault'] = 'Ver el soporte dedicado';
$string['cardintro'] = 'Esto solo lo ve la gestión de la plataforma: el tiempo de soporte que dedicamos a tus servicios.';

// Uso del plan.
$string['usagetitle'] = 'Uso del plan';
$string['usageusers'] = 'Usuarios activos (30 días)';
$string['usageusersof'] = '{$a->used} de {$a->limit}';
$string['usagedisk'] = 'Disco';
$string['usagediskof'] = '{$a->used} GB de {$a->limit} GB';

// Las intervenciones incluidas del mes.
$string['interventionstitle'] = 'Intervenciones incluidas';
$string['interventionline'] = 'Intervenciones de {$a->month}: {$a->used} de {$a->allowed}';

// Tablas.
$string['nofn'] = '{$a->a} de {$a->b}';
$string['colmonth'] = 'Mes';
$string['colinterventions'] = 'Intervenciones incluidas';
$string['colbag'] = 'Bolsa';
$string['coloutofbag'] = 'Fuera de bolsa';
$string['colmaintenance'] = 'Mantenimiento';
$string['coldate'] = 'Fecha';
$string['coldescription'] = 'Trabajo';
$string['colminutes'] = 'Minutos';
$string['coltype'] = 'Cómo cuenta';
$string['colincluded'] = 'Incluida';
$string['colreference'] = 'Referencia';

// Tipos de trabajo.
$string['type_included'] = 'Intervención incluida';
$string['type_bag'] = 'Con cargo a la bolsa';
$string['type_out_of_bag'] = 'Fuera de bolsa';
$string['type_maintenance'] = 'Mantenimiento del plan';

// Privacidad.
$string['privacy:metadata:central_registry'] = 'El bloque muestra los datos de soporte que guarda el registro central de Oksigenia y conserva una copia local para funcionar cuando el central no responde. No guarda datos personales de los usuarios del sitio.';
$string['privacy:metadata:central_registry:site'] = 'Se envía la dirección del sitio para que el central sepa qué cliente pregunta.';
