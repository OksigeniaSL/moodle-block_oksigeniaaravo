# Horas de soporte (Oksigenia Aravo)

Un bloque de Moodle que le enseña a la gestión de un cliente de alojamiento en qué se va su tiempo de soporte, en su
propio Moodle y solo de lectura:

- la **bolsa de horas**: compradas, gastadas y restantes;
- las **intervenciones incluidas** del mes y si ya se han usado;
- el **registro de trabajos**: fecha, qué se hizo, tiempo y cómo cuenta, con descarga en CSV y Excel.

Los datos no viven en Moodle. Un **registro central** los guarda y hace las cuentas; el bloque lee una instantánea por
HTTPS, conserva una copia local para seguir funcionando cuando el central no responde («Actualizado a las…») y nunca
escribe. Nadie apunta trabajos desde Moodle.

*Aravo* es «hora» en guaraní.

## Quién lo ve

Quien tenga la capacidad `block/oksigeniaaravo:view`, concedida por defecto al arquetipo `manager`, de modo que un rol
de gestión lo ve y los alumnos no. El bloque se añade al Área personal. El enlace al registro central se muestra solo a
los administradores del sitio.

## Requisitos

- Moodle 4.1 a 5.3.
- PHP 7.4 o posterior.
- Salida HTTPS hacia el registro central.

## Instalación

Copia el plugin en `blocks/oksigeniaaravo` y termina la instalación como administrador. Después, en
*Administración del sitio → Extensiones → Bloques → Horas de soporte*, indica la dirección del registro central y, si el
registro lo pide, una clave de acceso. Añade el bloque al Área personal.

## Ajustes

| Ajuste | Qué hace |
|---|---|
| Dirección del registro central | La dirección HTTPS completa de la que lee el bloque. |
| Clave de acceso | Opcional. Solo para un servidor que el registro no reconozca por su IP. |
| Nombre del proveedor | El título del bloque cuando el central no envía uno. |
| Refrescar al abrir | Al abrir el registro, si la copia local es más antigua que estos minutos, se vuelve a leer. |

Una tarea programada refresca además la copia local en segundo plano.

## Privacidad

El bloque no guarda datos personales de los usuarios del sitio. Conserva una copia local de los datos de soporte que
guarda el registro central externo y los muestra. Consulta el proveedor de privacidad para la declaración.

## Licencia

GNU GPL v3 o posterior. Copyright Oksigenia SL.
