<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Manual de Usuario</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10.5pt;
            color: #222;
            line-height: 1.45;
        }
        @page { margin: 60px 50px; }

        h1 { color: #0d6efd; font-size: 22pt; margin: 0 0 4pt 0; }
        h2 {
            color: #fff;
            background: #0d6efd;
            padding: 6pt 10pt;
            font-size: 13pt;
            border-radius: 4px;
            margin: 22pt 0 8pt 0;
            page-break-after: avoid;
        }
        h3 { color: #0a58ca; font-size: 11.5pt; margin: 12pt 0 4pt 0; page-break-after: avoid; }
        p { margin: 4pt 0; }
        ul, ol { margin: 4pt 0 4pt 16pt; padding: 0; }
        li { margin-bottom: 2pt; }

        table { width: 100%; border-collapse: collapse; margin: 8pt 0; font-size: 9.5pt; }
        th {
            background: #0d6efd;
            color: #fff;
            padding: 5pt 6pt;
            text-align: left;
            border: 1px solid #0a58ca;
        }
        td { padding: 5pt 6pt; border: 1px solid #c9d4e3; vertical-align: top; }
        tr:nth-child(even) td { background: #f2f6fb; }

        .portada {
            text-align: center;
            padding-top: 140pt;
        }
        .portada h1 { font-size: 26pt; color: #0d6efd; }
        .portada .sub { font-size: 14pt; color: #555; margin-top: 6pt; }
        .portada .meta {
            margin-top: 30pt;
            font-size: 10.5pt;
            color: #666;
        }

        .badge { display: inline-block; padding: 1pt 6pt; border-radius: 3px; font-size: 8.5pt; font-weight: bold; }
        .b-admin { background: #dc3545; color: #fff; }
        .b-ventas { background: #0d6efd; color: #fff; }
        .b-almacen { background: #ffc107; color: #333; }
        .b-logistica { background: #6f42c1; color: #fff; }
        .b-transporte { background: #198754; color: #fff; }
        .b-tienda { background: #20c997; color: #fff; }

        .box {
            border-left: 4px solid #0d6efd;
            background: #f2f6fb;
            padding: 8pt 10pt;
            margin: 8pt 0;
            page-break-inside: avoid;
        }
        .box.warn { border-left-color: #dc3545; background: #fdf0f0; }
        .box.ok { border-left-color: #198754; background: #f0faf2; }

        .pagebreak { page-break-before: always; }
        .footer {
            text-align: center;
            color: #888;
            font-size: 8.5pt;
            border-top: 1px solid #ddd;
            padding-top: 4pt;
            margin-top: 20pt;
        }
    .status { font-family: 'DejaVu Sans'; }
    code {
            font-family: 'DejaVu Sans';
            background: #eee;
            padding: 0 4pt;
            border-radius: 3px;
            font-size: 9pt;
        }
        .pedido-box {
            border: 2px solid #0a58ca;
            border-radius: 6px;
            padding: 12pt 14pt;
            margin: 12pt 0;
            page-break-inside: avoid;
        }
        .pedido-box .ph { font-size: 8.5pt; color: #555; margin-bottom: 2pt; }
        .pedido-box .pv { font-size: 10.5pt; margin-bottom: 6pt; font-weight: bold; }
        .pedido-box table { margin-top: 6pt; font-size: 9.5pt; }
        .pedido-box .sig-box { margin-top: 20pt; display: flex; justify-content: space-between; }
        .pedido-box .sig { border-top: 1px solid #aaa; width: 45%; text-align: center; padding-top: 3pt; font-size: 9pt; }
    </style>
</head>
<body>

<!-- portada -->
<div class="portada">
    <h1>Logística Maruchan</h1>
    <div class="sub">Manual de Usuario</div>
    <div class="meta">
        Sistema de gestión logística para Sopas Maruchan<br>
        Ventas → Almacén → Embarque → Transporte → Tienda
    </div>
</div>

<div class="pagebreak"></div>

<h2>1. Introducción</h2>
<p><strong>Logística Maruchan</strong> es el sistema que controla el flujo completo de pedidos de Sopas Maruchan
desde su captura en Ventas hasta su entrega en tienda. Cada paso queda registrado para poder responder en todo
momento la pregunta: <strong>“¿Dónde está el pedido?”</strong>.</p>
<p>Este manual explica el uso de cada módulo según el rol del usuario. Al iniciar sesión, el sistema
redirige automáticamente a la pantalla principal de tu área de trabajo.</p>

<h3>Roles del sistema</h3>
<table>
    <tr><th>Rol</th><th>Responsabilidad</th></tr>
    <tr><td><span class="badge b-admin">Administrador</span></td><td>Administra catálogo, tiendas, almacenes, vehículos, clientes, consulta indicadores, gráficas, descarga reportes y recibe las alertas por correo del sistema.</td></tr>
    <tr><td><span class="badge b-ventas">Ventas</span></td><td>Captura y da seguimiento a los pedidos de las tiendas.</td></tr>
    <tr><td><span class="badge b-almacen">Almacén</span></td><td>Registra la llegada y verificación de la mercancía y confirma la recepción de devoluciones.</td></tr>
    <tr><td><span class="badge b-logistica">Logística</span></td><td>Planifica los traslados (embarques) hacia las tiendas.</td></tr>
    <tr><td><span class="badge b-transporte">Transporte</span></td><td>Consulta los viajes, su status en ruta y registra incidencias del trayecto.</td></tr>
    <tr><td><span class="badge b-tienda">Tienda</span></td><td>Consulta y confirma las recepciones de sus pedidos y registra devoluciones al almacén.</td></tr>
</table>

<h2>2. Acceso al sistema</h2>
<ol>
    <li>Abre tu navegador y entra a la dirección del sistema (por ejemplo <code>http://127.0.0.1:8000</code>).</li>
    <li>La página redirige automáticamente a la pantalla de <strong>inicio de sesión</strong>.</li>
    <li>Captura tu <strong>correo</strong> y <strong>contraseña</strong> y pulsa <strong>Login</strong>.</li>
    <li>Ingresarás a la pantalla de tu área según tu rol.</li>
</ol>

<div class="box warn"><strong>Importante:</strong> si olvidas tu contraseña, solicita el restablecimiento a tu administrador.
No compartas tus credenciales con otros usuarios.</div>

<h3>Cuentas de demostración</h3>
<p>En el entorno de prueba, todas las cuentas usan la contraseña <code>password</code>:</p>
<table>
    <tr><th>Rol</th><th>Correo</th></tr>
    <tr><td>Administrador</td><td><code>admin@maruchan.test</code></td></tr>
    <tr><td>Ventas</td><td><code>ventas@maruchan.test</code></td></tr>
    <tr><td>Almacén</td><td><code>almacen@maruchan.test</code></td></tr>
    <tr><td>Logística</td><td><code>logistica@maruchan.test</code></td></tr>
    <tr><td>Transporte</td><td><code>transporte@maruchan.test</code></td></tr>
    <tr><td>Tienda</td><td><code>tienda@maruchan.test</code></td></tr>
</table>

<h2>3. Barra de navegación</h2>
<p>En la parte superior aparece el menú del sistema. Cada usuario ve únicamente las opciones correspondientes
a su rol. Todos los roles cuentan además con una opción <strong>Trazabilidad</strong> (ver sección 16). El botón
<strong>Cerrar sesión</strong> se encuentra siempre en la parte superior derecha.</p>

<!-- ==================== DASHBOARD ==================== -->
<h2>4. Dashboard operativo (Administrador)</h2>
<p>Es la pantalla inicial del Administrador y muestra los indicadores en tiempo real del negocio:</p>
<table>
    <tr><th>Indicador</th><th>Qué significa</th></tr>
    <tr><td>Mercancía recibida hoy</td><td>Pedidos que entraron al almacén el día de hoy.</td></tr>
    <tr><td>Pedidos pendientes</td><td>Pedidos aún no entregados o cerrados.</td></tr>
    <tr><td>Pedidos preparados</td><td>Pedidos listos o asignados a un embarque.</td></tr>
    <tr><td>Traslados activos</td><td>Embarques en curso (sin entregar).</td></tr>
    <tr><td>Unidades en almacén</td><td>Unidades recibidas menos las preparadas (stock físico en almacén).</td></tr>
    <tr><td>Entregas realizadas</td><td>Pedidos recibidos en tienda o cerrados.</td></tr>
    <tr><td>Incidencias</td><td>Total de incidencias registradas (faltantes, daños, etc.).</td></tr>
    <tr><td>Días promedio en almacén</td><td>Tiempo promedio entre la recepción y el envío de los pedidos.</td></tr>
</table>
<p>Además se muestra la gráfica de <strong>Pedidos por tienda</strong>, que ayuda a detectar los principales destinos
de despacho.</p>
<h3>Gráficas del dashboard</h3>
<p>El dashboard incluye cuatro gráficas generadas con <strong>Chart.js</strong> para analizar la operación:</p>
<table>
    <tr><th>Gráfica</th><th>Qué muestra</th></tr>
    <tr><td>Pedidos por estado</td><td>Dona con la distribución actual de pedidos (creado, confirmado, en tránsito, etc.).</td></tr>
    <tr><td>Embarques por estado</td><td>Dona con los embarques en cada etapa (preparado, cargado, en tránsito, entregado, cerrado).</td></tr>
    <tr><td>Pedidos por día</td><td>Línea con los pedidos creados en los últimos 14 días.</td></tr>
    <tr><td>Incidencias por tipo</td><td>Barras horizontales con el total de incidencias (faltantes, daños, percances, retrasos, etc.).</td></tr>
</table>
<p>Cuando no hay datos para una gráfica, esta muestra el aviso <em>“Sin datos”</em> en lugar de un gráfico vacío.</p>
<h3>KPIs avanzados</h3>
<p>Debajo de los indicadores se muestran dos paneles adicionales:</p>
<ul>
    <li><strong>Tiempos por etapa (días promedio)</strong> — duración promedio de cada tramo del pedido:
        recepción (pedido → entrada en almacén), preparación (entrada → preparado), en almacén (recepción → envío),
        tránsito y entrega (envío → cierre en tienda) y ciclo total (pedido → cerrado). Se calcula sobre los pedidos
        que ya completaron cada etapa.</li>
    <li><strong>Rotación de inventario</strong> — stock actual en almacén, unidades preparadas (salidas) en los
        últimos 30 días, la rotación (salidas / stock) y los días de cobertura estimados (stock ÷ salidas diarias).</li>
</ul>

<!-- ==================== CATALOGO ==================== -->
<h2>5. Catálogo de presentaciones (Administrador)</h2>
<p>El menú <strong>Catálogo</strong> administra las presentaciones de producto disponibles (vaso, paquete, etc.).</p>
<h3>Listar y buscar</h3>
<ul>
    <li>La pantalla <strong>Listado</strong> muestra todas las presentaciones con su SKU, tipo, sabor y estado.</li>
    <li>Usa el campo de búsqueda para filtrar por nombre, sabor o SKU.</li>
</ul>
<h3>Registrar una presentación</h3>
<ol>
    <li>Pulsa <strong>Nueva presentación</strong>.</li>
    <li>Completa los campos requeridos (nombre, tipo, sabor, SKU, estado).</li>
    <li>Pulsa <strong>Guardar</strong>. El campo SKU no debe repetirse.</li>
</ol>
<h3>Editar / Eliminar</h3>
<ul>
    <li>Usa los botones <strong>Editar</strong> y <strong>Eliminar</strong> de cada fila.</li>
    <li>Solo se eliminan presentaciones que no estén siendo utilizadas por pedidos.</li>
</ul>

<!-- ==================== TIENDAS ==================== -->
<h2>6. Tiendas (Administrador)</h2>
<p>El menú <strong>Tiendas</strong> administra las sucursales destino de los pedidos.</p>
<ol>
    <li>Pulsa <strong>Nueva tienda</strong> para registrar una sucursal.</li>
    <li>Captura el <strong>código</strong> (único), el <strong>nombre</strong>, la dirección, la
        zona, la <strong>persona de contacto</strong> y el <strong>horario de atención</strong>.</li>
    <li>Pulsa <strong>Guardar</strong>.</li>
</ol>
<div class="box">El código de tienda identifica la sucursal en pedidos y embarques. No se puede repetir.
Los campos de contacto y horario aparecen en el detalle de la tienda y en los formatos de pedido y embarque.</div>

<!-- ==================== ALMACENES ==================== -->
<h2>7. Almacenes y ubicaciones (Administrador)</h2>
<p>El menú <strong>Almacenes</strong> administra los almacenes y sus zonas de almacenaje.</p>
<h3>Registrar un almacén</h3>
<ol>
    <li>Pulsa <strong>Nuevo almacén</strong>.</li>
    <li>Captura el <strong>código</strong> (único) y el <strong>nombre</strong>.</li>
    <li>En la misma pantalla de edición puedes <strong>agregar ubicaciones</strong> escribiendo la zona o pasillo
        y pulsando añadir. Cada ubicación se puede eliminar cuando ya no exista mercancía asignada.</li>
</ol>
<div class="box ok"><strong>Ejemplo:</strong> almacén “Central” con ubicaciones “Zona A”, “Zona B”, “Zona C” y “Zona D”.</div>

<!-- ==================== VEHICULOS ==================== -->
<h2>8. Vehículos (Administrador)</h2>
<p>El menú <strong>Vehículos</strong> administra la flota utilizada en los embarques. Cada embarque debe
indicar el vehículo que realizará el traslado.</p>
<h3>Listar y buscar</h3>
<ul>
    <li>El <strong>Listado</strong> muestra todos los vehículos con su código, número económico, placa,
        marca, modelo y conductor.</li>
    <li>Usa el campo de búsqueda para filtrar por código, placa o conductor.</li>
</ul>
<h3>Registrar un vehículo</h3>
<ol>
    <li>Pulsa <strong>Nuevo vehículo</strong>.</li>
    <li>Captura el <strong>código</strong> (único), el <strong>número económico</strong>, la <strong>placa</strong>,
        la <strong>marca</strong>, el <strong>modelo</strong> y, de forma opcional, el <strong>conductor</strong>.</li>
    <li>Pulsa <strong>Guardar</strong>.</li>
</ol>
<h3>Editar / Desactivar</h3>
<ul>
    <li>Usa los botones <strong>Editar</strong> y <strong>Desactivar</strong> de cada fila.</li>
    <li>Al desactivar un vehículo, este deja de aparecer en los embarques nuevos pero conserva su historial
        en embarques ya creados.</li>
</ul>

<!-- ==================== CLIENTES ==================== -->
<h2>9. Clientes (Administrador)</h2>
<p>El menú <strong>Clientes</strong> administra los clientes que solicitaron pedidos. Al registrar una tienda
se asocia al cliente que la atiende.</p>
<ol>
    <li>Pulsa <strong>Nuevo cliente</strong>.</li>
    <li>Captura el <strong>nombre</strong> (o razón social), el <strong>RFC</strong> (opcional), el
        <strong>contacto</strong>, el <strong>teléfono</strong> y el <strong>correo</strong>.</li>
    <li>Pulsa <strong>Guardar</strong>.</li>
</ol>
<div class="box">Puedes buscar clientes por nombre o RFC desde el <strong>Listado</strong>. Los datos de contacto
del cliente y de la tienda aparecen juntos en el detalle de la tienda.</div>

<!-- ==================== PEDIDOS ==================== -->
<h2>10. Pedidos de venta (Ventas)</h2>
<p>El área de <strong>Ventas</strong> captura los pedidos que las tiendas solicitan.</p>

<h3>Listado de pedidos</h3>
<ul>
    <li>Muestra todos los pedidos capturados con su folio, tienda, fecha, importe y estado.</li>
    <li>Puedes buscar por folio y filtrar por estado o tienda.</li>
</ul>

<h3>Crear un pedido</h3>
<ol>
    <li>Pulsa <button>Nuevo pedido</button>.</li>
    <li>Selecciona la <strong>tienda</strong> que solicita y el <strong>almacén</strong> que despachará.</li>
    <li>Agrega uno o más <strong>productos</strong>: elige la presentación y la <strong>cantidad</strong>.</li>
    <li>Puedes añadir o quitar renglones antes de guardar.</li>
    <li>Pulsa <strong>Guardar</strong>. El sistema asigna automáticamente el folio único
        <code>PED-YYYYMMDD-####</code> y registra el movimiento “Pedido creado”.</li>
</ol>

<h3>Ver un pedido</h3>
<p>Al abrir un pedido se muestran: datos generales, los productos con sus cantidades, el <strong>código QR</strong>
con el folio y el <strong>historial</strong>
de movimiento del pedido (quién lo registró, cuándo y en qué estado).</p>

<h3>Ejemplo de formato de pedido</h3>
<p>El sistema genera un formato con los siguientes campos, listo para enviar al almacén:</p>

<div class="pedido-box">
    <div style="text-align:center; margin-bottom:8pt">
        <strong style="font-size:13pt; color:#0d6efd">SOPAS MARUCHAN</strong><br>
        <span style="font-size:8.5pt; color:#666">Logística y distribución</span>
    </div>
    <div style="display:flex; justify-content:space-between; margin-bottom:6pt">
        <div>
            <div class="ph">FOLIO</div>
            <div class="pv" style="color:#0d6efd">PED-20260910-0001</div>
        </div>
        <div style="text-align:right">
            <div class="ph">FECHA</div>
            <div class="pv">10/09/2026  09:45 hrs</div>
        </div>
    </div>
    <div style="display:flex; justify-content:space-between; margin-bottom:8pt">
        <div>
            <div class="ph">TIENDA DESTINO</div>
            <div class="pv" style="font-size:10pt">Calle Reforma 215, Col. Centro · Zona Norte</div>
        </div>
        <div style="text-align:right">
            <div class="ph">ALMACÉN ORIGEN</div>
            <div class="pv" style="font-size:10pt">Almacén Central</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:10%">SKU</th>
                <th style="width:25%">Presentación</th>
                <th>Sabor</th>
                <th style="width:13%; text-align:center">Cant. pedida</th>
                <th style="width:13%; text-align:center">Peso aprox.</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>MAR-VAS-01</td>
                <td>Vaso Maruchan</td>
                <td>Pollo</td>
                <td style="text-align:center">24 pzas</td>
                <td style="text-align:center">720 g</td>
            </tr>
            <tr>
                <td>2</td>
                <td>MAR-VAS-05</td>
                <td>Vaso Maruchan</td>
                <td>Res</td>
                <td style="text-align:center">24 pzas</td>
                <td style="text-align:center">720 g</td>
            </tr>
            <tr>
                <td>3</td>
                <td>MAR-PAQ-03</td>
                <td>Paquete 12-pack</td>
                <td>Pollo</td>
                <td style="text-align:center">6 pzas</td>
                <td style="text-align:center">4.32 kg</td>
            </tr>
            <tr>
                <td>4</td>
                <td>MAR-VAS-08</td>
                <td>Vaso Maruchan</td>
                <td>Cerdo</td>
                <td style="text-align:center">12 pzas</td>
                <td style="text-align:center">360 g</td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top:10pt; display:flex; justify-content:flex-end">
        <div style="text-align:right">
            <div class="ph">TOTAL UNIDADES</div>
            <div class="pv" style="font-size:12pt">66 pzas</div>
        </div>
    </div>

    <div style="margin-top:12pt; border-top:1px solid #ddd; padding-top:8pt">
        <strong style="font-size:9pt; color:#0d6efd">ESTADO:</strong>
        <span class="badge" style="background:#0d6efd; color:#fff; padding:2pt 8pt; border-radius:3px; font-size:9pt">CREADO</span>
    </div>

    <div style="margin-top:18pt; display:flex; justify-content:space-between">
        <div style="width:45%; text-align:center">
            <div style="border-top:1px solid #aaa; padding-top:3pt; font-size:9pt; color:#555">
                Firma del vendedor<br>
                <span style="font-size:8pt">María López – Ventas</span>
            </div>
        </div>
        <div style="width:45%; text-align:center">
            <div style="border-top:1px solid #aaa; padding-top:3pt; font-size:9pt; color:#555">
                VoBo. gerente de tienda<br>
                <span style="font-size:8pt">Tienda Centro Norte</span>
            </div>
        </div>
    </div>
</div>

<div class="box ok"><strong>Cómo se genera:</strong> en la pantalla de <strong>Nuevo pedido</strong>, captura la tienda, el almacén y los productos. El sistema asigna automáticamente el folio único y genera el formato listo para enviar al almacén.</div>

<div class="box warn"><strong>Nota:</strong> un pedido pasa de estado <code>CREADO</code> a <code>CONFIRMADO</code>
cuando se confirma; a partir de ahí inicia su trayecto logístico.</div>

<!-- ==================== RECEPCION ==================== -->
<h2>11. Recepción en almacén (Almacén)</h2>
<p>Cuando la mercancía llega del proveedor, el personal de almacén registra la entrada.</p>

<h3>Pedidos pendientes de recibir</h3>
<ul>
    <li>La pantalla <strong>Pendientes</strong> lista los pedidos confirmados que aún no han entrado al almacén.</li>
    <li>Cada pedido muestra la tienda solicitante y los productos.</li>
</ul>

<h3>Registrar la llegada</h3>
<ol>
    <li>En un pedido pendiente, pulsa <strong>Recibir</strong>.</li>
    <li>El sistema sugiere como cantidad recibida la cantidad pedida; ajústala a lo que realmente llegó.</li>
    <li>Si existe diferencia, captura una <strong>incidencia</strong> (faltante, sobrante, daño) con su descripción.</li>
    <li>Pulsa <strong>Guardar entrada</strong>. El pedido pasa a <code>EN_ALMACEN</code> y se registra el movimiento.</li>
</ol>

<h3>Verificar y confirmar</h3>
<ol>
    <li>Revisa la mercancía físicamente contra lo capturado en la pantalla de detalle.</li>
    <li>Pulsa <strong>Confirmar verificación</strong> para marcarla como lista. El pedido pasa a <code>RECIBIDO</code>.</li>
</ol>
<div class="box warn"><strong>Regla:</strong> no se puede recibir dos veces el mismo pedido, ni confirmar
una entrada que aún no esté en <code>EN_ALMACEN</code>. Las cantidades recibidas no pueden ser negativas.</div>

<h3>Ejemplo de formato de recepción de mercancía</h3>
<p>Al capturar la llegada de la mercancía, el sistema genera un formato con la información capturada:</p>

<div class="pedido-box">
    <div style="text-align:center; margin-bottom:8pt">
        <strong style="font-size:13pt; color:#0d6efd">SOPAS MARUCHAN</strong><br>
        <span style="font-size:8.5pt; color:#666">Formato de recepción de mercancía – Almacén</span>
    </div>
    <div style="display:flex; justify-content:space-between; margin-bottom:6pt">
        <div>
            <div class="ph">FOLIO DEL PEDIDO</div>
            <div class="pv" style="color:#0d6efd">PED-20260910-0001</div>
        </div>
        <div style="text-align:right">
            <div class="ph">FECHA DE RECEPCIÓN</div>
            <div class="pv">10/09/2026  15:30 hrs</div>
        </div>
    </div>
    <div style="display:flex; justify-content:space-between; margin-bottom:8pt">
        <div>
            <div class="ph">TIENDA DESTINO</div>
            <div class="pv" style="font-size:10pt">Calle Reforma 215, Col. Centro · Zona Norte</div>
        </div>
        <div style="text-align:right">
            <div class="ph">ALMACÉN</div>
            <div class="pv" style="font-size:10pt">Almacén Central</div>
        </div>
    </div>

    <div style="margin-bottom:6pt">
        <div class="ph">PERSONAL QUE RECIBE</div>
        <div class="pv" style="font-size:10pt">Carlos Hernández – Almacén Central</div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width:5%">#</th>
                <th style="width:10%">SKU</th>
                <th style="width:20%">Presentación</th>
                <th>Sabor</th>
                <th style="width:12%; text-align:center">Cant. pedida</th>
                <th style="width:12%; text-align:center">Cant. recibida</th>
                <th style="width:12%; text-align:center">Diferencia</th>
                <th style="width:10%; text-align:center">Estado</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>1</td>
                <td>MAR-VAS-01</td>
                <td>Vaso Maruchan</td>
                <td>Pollo</td>
                <td style="text-align:center">24 pzas</td>
                <td style="text-align:center"><strong>24 pzas</strong></td>
                <td style="text-align:center; color:#198754">✓ 0</td>
                <td style="text-align:center"><span class="badge" style="background:#198754; color:#fff">OK</span></td>
            </tr>
            <tr>
                <td>2</td>
                <td>MAR-VAS-05</td>
                <td>Vaso Maruchan</td>
                <td>Res</td>
                <td style="text-align:center">24 pzas</td>
                <td style="text-align:center"><strong>22 pzas</strong></td>
                <td style="text-align:center; color:#dc3545"><strong>-2 pzas</strong></td>
                <td style="text-align:center"><span class="badge" style="background:#dc3545; color:#fff">FALTANTE</span></td>
            </tr>
            <tr>
                <td>3</td>
                <td>MAR-PAQ-03</td>
                <td>Paquete 12-pack</td>
                <td>Pollo</td>
                <td style="text-align:center">6 pzas</td>
                <td style="text-align:center"><strong>6 pzas</strong></td>
                <td style="text-align:center; color:#198754">✓ 0</td>
                <td style="text-align:center"><span class="badge" style="background:#198754; color:#fff">OK</span></td>
            </tr>
            <tr>
                <td>4</td>
                <td>MAR-VAS-08</td>
                <td>Vaso Maruchan</td>
                <td>Cerdo</td>
                <td style="text-align:center">12 pzas</td>
                <td style="text-align:center"><strong>12 pzas</strong></td>
                <td style="text-align:center; color:#198754">✓ 0</td>
                <td style="text-align:center"><span class="badge" style="background:#198754; color:#fff">OK</span></td>
            </tr>
        </tbody>
    </table>

    <div style="margin-top:6pt; display:flex; justify-content:flex-end">
        <div style="text-align:right">
            <div class="ph">TOTAL RECIBIDO</div>
            <div class="pv" style="font-size:11pt">64 pzas de 66</div>
        </div>
    </div>

    <div style="margin-top:10pt; border-top:1px solid #ddd; padding-top:8pt">
        <div class="ph" style="margin-bottom:4pt"><strong>INCIDENCIAS</strong></div>
        <div style="background:#fdf0f0; border-left:3px solid #dc3545; padding:6pt 8pt; font-size:9pt">
            <strong>FALTANTE</strong> – Vaso Maruchan Res: 2 pzas menos de lo pedido (24 pedidas / 22 recibidas).<br>
            <span style="color:#555; font-size:8.5pt">Observación: caja arrives dañada por apertura previa. Faltante probable por manipulación en tránsito.</span>
        </div>
    </div>

    <div style="margin-top:12pt; border-top:1px solid #ddd; padding-top:8pt">
        <strong style="font-size:9pt; color:#0d6efd">ESTADO:</strong>
        <span class="badge" style="background:#ffc107; color:#333; padding:2pt 8pt; border-radius:3px; font-size:9pt">EN_ALMACEN</span>
        <span style="font-size:8.5pt; color:#555; margin-left:8pt">(esperando verificación para pasar a RECIBIDO)</span>
    </div>

    <div style="margin-top:18pt; display:flex; justify-content:space-between">
        <div style="width:45%; text-align:center">
            <div style="border-top:1px solid #aaa; padding-top:3pt; font-size:9pt; color:#555">
                Firma del personal de almacén<br>
                <span style="font-size:8pt">Carlos Hernández – Recibe</span>
            </div>
        </div>
        <div style="width:45%; text-align:center">
            <div style="border-top:1px solid #aaa; padding-top:3pt; font-size:9pt; color:#555">
                Firma del conductor / transportista<br>
                <span style="font-size:8pt">Transporte externo</span>
            </div>
        </div>
    </div>
</div>

<div class="box warn"><strong>Pasos:</strong> (1) Captura las cantidades recibidas realmente por producto.
(2) Si existe diferencia, registra una incidencia. (3) Pulsas <em>Guardar entrada</em>.
(4) Al día siguiente o cuando verifiques la mercancía, pulsas <em>Confirmar verificación</em>.</div>

<!-- ==================== CLASIFICAR Y PREPARAR ==================== -->
<h2>12. Clasificar y preparar mercancía (Almacén)</h2>
<p>Una vez que la entrada fue verificada (<code>RECIBIDO</code>), la mercancía se ubica y se prepara para su salida.</p>

<h3>Clasificar</h3>
<ol>
    <li>En “Recientemente recibidos”, el pedido en estado <code>RECIBIDO</code> muestra el botón <strong>Clasificar</strong>.</li>
    <li>Captura la <strong>ubicación física</strong> donde se guardará la mercancía (ej. “Zona A, Pasillo 3, Repisa 2-A”).</li>
    <li>Pulsa <strong>Clasificar y ubicar</strong>. El pedido pasa a <code>CLASIFICADO</code> y se registra el movimiento con la ubicación.</li>
</ol>

<h3>Preparar</h3>
<ol>
    <li>En el pedido en estado <code>CLASIFICADO</code>, pulsa <strong>Preparar</strong>.</li>
    <li>Confirma la <strong>cantidad a preparar</strong> de cada producto (el sistema sugiere la cantidad recibida).</li>
    <li>Pulsa <strong>Confirmar preparación</strong>. El pedido pasa a <code>PREPARADO</code> y queda listo para embarcar.</li>
</ol>
<div class="box warn"><strong>Regla:</strong> solo se clasifican pedidos verificados (<code>RECIBIDO</code>) y solo se preparan pedidos clasificados (<code>CLASIFICADO</code>). Las cantidades no pueden ser negativas.</div>

<!-- ==================== EMBARQUES ==================== -->
<h2>13. Embarques (Logística)</h2>
<p>Aquí se agrupan los pedidos preparados en un traslado con vehículo hacia una tienda.</p>

<h3>Crear un embarque</h3>
<ol>
    <li>Entra al menú <strong>Embarques</strong> → <strong>Nuevo embarque</strong>.</li>
    <li>Selecciona el <strong>almacén de origen</strong> y la <strong>tienda destino</strong>.</li>
    <li>Elige el <strong>vehículo</strong> (código, placa y conductor) y, si aplica, el conductor y la fecha programada.</li>
    <li>Al elegir la tienda destino se muestran solo los <strong>pedidos preparados de esa tienda</strong>. Marca los que se embarcarán.</li>
    <li>Pulsa <strong>Crear embarque</strong>. Se asigna el folio único <code>EMB-YYYYMMDD-####</code> y los pedidos pasan a <code>ASIGNADO_EMBARQUE</code>.</li>
</ol>

<h3>Cargar el vehículo</h3>
<ol>
    <li>Abre el embarque creado y verifica los pedidos asignados.</li>
    <li>Pulsa <strong>Cargar vehículo</strong>. El embarque pasa a <code>CARGADO</code> y sus pedidos también.</li>
</ol>
<div class="box warn"><strong>Regla:</strong> cada embarque solo puede llevar pedidos de una misma tienda, todos en estado <code>PREPARADO</code>, y requiere al menos un pedido.</div>

<div class="box">La <strong>Trazabilidad</strong> permite consultar además el historial del embarque (creación, carga, salida y entrega).</div>

<!-- ==================== VIAJES ==================== -->
<h2>14. Viajes de transporte (Transporte)</h2>
<p>El personal de transporte da seguimiento a los embarques asignados hasta su entrega en tienda.</p>

<h3>Salir a ruta</h3>
<ol>
    <li>En <strong>Viajes</strong> se listan los viajes activos (<code>PREPARADO</code>, <code>CARGADO</code>, <code>EN_TRANSITO</code>).</li>
    <li>Abre el viaje en estado <code>CARGADO</code> y pulsa <strong>Salir a ruta</strong>.</li>
    <li>El embarque queda <code>EN_TRANSITO</code>, con fecha de salida (<code>departed_at</code>), y sus pedidos en el mismo estado.</li>
</ol>

<h3>Incidencias en ruta</h3>
<p>Mientras el viaje está <code>EN_TRANSITO</code>, puedes registrar cualquier evento del trayecto desde la
pantalla del viaje usando el formulario <strong>Incidencia en ruta</strong>:</p>
<ol>
    <li>Selecciona el <strong>tipo</strong> de incidencia:
        <ul>
            <li><strong>Percance</strong> – accidente o daño al vehículo o mercancía en ruta.</li>
            <li><strong>Parada</strong> – parada técnica, descanso o demora en caseta de peaje.</li>
            <li><strong>Retraso</strong> – retraso por tráfico, clima o cualquier motivo.</li>
            <li><strong>Nota</strong> – observación general del conductor o del equipo.</li>
        </ul>
    </li>
    <li>Escribe la <strong>descripción</strong> con los detalles del evento.</li>
    <li>Pulsa <strong>Registrar incidencia</strong>. La incidencia se registra como un <code>Incident</code> asociado
        al embarque y aparece en la trazabilidad.</li>
</ol>
<p>Las incidencias en ruta aparecen también en la gráfica <strong>Incidencias por tipo</strong> del Dashboard.</p>

<h3>Llegada a tienda</h3>
<ol>
    <li>Cuando el vehículo llega a la tienda, pulsa <strong>Llegada a tienda</strong>.</li>
    <li>El embarque queda <code>ENTREGADO</code> (con hora de llegada) y los pedidos pasan a <code>RECIBIDO_TIENDA</code>.</li>
</ol>
<div class="box warn"><strong>Regla:</strong> solo se inicia un viaje que esté <code>CARGADO</code> y solo se registra la llegada de uno <code>EN_TRANSITO</code>. Las incidencias en ruta solo se registran durante el viaje.</div>

<!-- ==================== RECEPCION TIENDA ==================== -->
<h2>15. Recepción en tienda (Tienda)</h2>
<p>El personal de la tienda confirma la mercancía recibida para cerrar el ciclo del pedido.</p>
<ol>
    <li>En <strong>Recepción</strong> se listan los pedidos entregados (<code>RECIBIDO_TIENDA</code>) de tu tienda.</li>
    <li>Verifica físicamente la mercancía contra el folio y las cantidades.</li>
    <li>Pulsa <strong>Recibido — cerrar</strong>. El pedido pasa a <code>CERRADO</code>.</li>
    <li>Cuando <strong>todos</strong> los pedidos de un embarque estén cerrados, el propio embarque se cierra automáticamente.</li>
</ol>
<div class="box">Solo ves los pedidos de tu propia tienda. El administrador puede confirmar cualquier pedido.</div>

<!-- ==================== TRAZABILIDAD ==================== -->
<h2>16. Trazabilidad (Ventas, Almacén, Logística, Administrador)</h2>
<p>Es la herramienta que responde <strong>“¿Dónde está el pedido?”</strong>.</p>
<ol>
    <li>Entra al menú <strong>Trazabilidad</strong>.</li>
    <li>Escribe el <strong>folio</strong> del pedido (ej. <code>PED-20260910-0001</code>). El buscador acepta parte del folio.</li>
    <li>Pulsa <strong>Consultar</strong>.</li>
</ol>
<p>La consulta muestra:</p>
<ul>
    <li>La <strong>situación actual</strong>: estado, tienda destino, almacén y fechas clave (creado, recibido, enviado, completado).</li>
    <li>Los <strong>productos</strong> del pedido con cantidades pedidas, recibidas y preparadas.</li>
    <li>Las <strong>incidencias</strong> registradas, si las hay.</li>
    <li>El <strong>historial completo</strong>, movimiento por movimiento, con fecha/hora, usuario responsable y estado.</li>
    <li>Un <strong>código QR</strong> (en el panel lateral) con el folio del pedido, útil para escanear desde un dispositivo móvil o formato físico.</li>
</ul>
<div class="box">Si seleccionas un pedido desde el detalle de Ventas, puedes consultar el mismo historial desde su pantalla.
El código QR se genera automáticamente al crear el pedido.</div>

<!-- ==================== ESTADOS ==================== -->
<h2>17. Estados del pedido</h2>
<table>
    <tr><th>Estado</th><th>Significado</th></tr>
    <tr><td>CREADO</td><td>El pedido fue capturado por Ventas.</td></tr>
    <tr><td>CONFIRMADO</td><td>El pedido fue confirmado y queda listo para su llegada al almacén.</td></tr>
    <tr><td>EN_ALMACEN</td><td>La mercancía llegó y su entrada fue registrada.</td></tr>
    <tr><td>RECIBIDO</td><td>La entrada fue verificada y confirmada en almacén.</td></tr>
    <tr><td>CLASIFICADO</td><td>La mercancía fue clasificada y asignada a su zona.</td></tr>
    <tr><td>PREPARADO</td><td>El pedido fue preparado para su traslado.</td></tr>
    <tr><td>ASIGNADO_EMBARQUE</td><td>El pedido fue asignado a un embarque.</td></tr>
    <tr><td>CARGADO</td><td>La mercancía fue cargada en el vehículo.</td></tr>
    <tr><td>EN_TRANSITO</td><td>El vehículo está en ruta hacia la tienda.</td></tr>
    <tr><td>RECIBIDO_TIENDA</td><td>La tienda confirmó la recepción.</td></tr>
    <tr><td>CERRADO</td><td>El pedido está terminado.</td></tr>
</table>

<h2>18. Flujo resumido de trabajo</h2>
<ol>
    <li><strong>Ventas</strong> captura el pedido de una tienda (estado CREADO).</li>
    <li>El pedido se confirma (CONFIRMADO).</li>
    <li><strong>Almacén</strong> recibe la mercancía y registra la entrada (EN_ALMACEN) y la verifica (RECIBIDO).</li>
    <li><strong>Almacén</strong> clasifica la mercancía (CLASIFICADO) y la prepara (PREPARADO) para su salida.</li>
    <li><strong>Logística</strong> crea el embarque, asigna los pedidos y carga el vehículo (ASIGNADO_EMBARQUE → CARGADO).</li>
    <li><strong>Transporte</strong> sale a ruta (EN_TRANSITO), registra incidencias si las hay, y entrega en tienda (RECIBIDO_TIENDA).</li>
    <li><strong>Tienda</strong> confirma la recepción y el pedido se cierra (CERRADO); el embarque se cierra al completar sus pedidos.</li>
</ol>
<p>Cada paso queda registrado en la <strong>Trazabilidad</strong> del pedido.</p>

<h2>19. Consejos y preguntas frecuentes</h2>
<table>
    <tr><th>Pregunta</th><th>Respuesta</th></tr>
    <tr><td>¿Por qué no encuentro un pedido en mi listado?</td><td>Verifica que el pedido tenga el estado adecuado. Los pendientes de recepción solo aparecen cuando están confirmados.</td></tr>
    <tr><td>¿Puedo recibir un pedido dos veces?</td><td>No. Una vez confirmada la entrada, el sistema lo bloquea.</td></tr>
    <tr><td>Llegó menos mercancía de la pedida.</td><td>Registra la cantidad real recibida y captura una incidencia de tipo faltante.</td></tr>
    <tr><td>¿Cómo sé cuánta mercancía hay en el almacén?</td><td>El indicador “Unidades en almacén” del Dashboard te da el total; por pedido la ves en la trazabilidad.</td></tr>
    <tr><td>¿Puedo consultar el historial de un pedido viejo?</td><td>Sí. Usa Trazabilidad con el folio completo o parte de él.</td></tr>
    <tr><td>¿Cómo registro una incidencia durante el traslado?</td><td>Abre el viaje (debe estar EN_TRANSITO) y usa el formulario de <em>Incidencia en ruta</em>. Selecciona el tipo (percance, parada, retraso o nota), escribe la descripción y regístrala. Aparecerá en la trazabilidad y en las gráficas del Dashboard.</td></tr>
    <tr><td>¿Qué es el código QR del pedido?</td><td>Cada pedido incluye un código QR en la pantalla de detalle y en Trazabilidad. Escánelo con la cámara de tu dispositivo para ver el folio del pedido sin tener que escribirlo manualmente.</td></tr>
    <tr><td>¿Cómo exporto un listado a Excel?</td><td>Entra a Reportes, abre el reporte que necesitas, aplica los filtros y pulsa <em>Descargar Excel (.xlsx)</em>. También puedes descargar la misma información en PDF.</td></tr>
    <tr><td>¿Recibo avisos cuando cambian los pedidos?</td><td>Sí. El sistema envía automáticamente un correo a los administradores cuando un pedido entra al almacén, un embarque sale a ruta, un pedido se entrega en tienda o se registra una incidencia en ruta.</td></tr>
    <tr><td>¿Cómo devuelvo mercancía al almacén?</td><td>En Recepción, abre un pedido entregado o cerrado y pulsa <em>Devolver</em>. Selecciona el motivo, los productos, las cantidades y su estado (buena o dañada) y regístralo. El almacén confirmará la recepción.</td></tr>
</table>

<div class="box ok"><strong>Soporte:</strong> si encuentras un error o necesitas ayuda, contacta al administrador del sistema
con el folio del pedido y una descripción de lo ocurrido.</div>

<!-- ==================== REPORTES ==================== -->
<h2>20. Reportes exportables (Administrador)</h2>
<p>El menú <strong>Reportes</strong> permite consultar y descargar la información operativa del sistema en
formato <strong>PDF</strong> o <strong>Excel (.xlsx)</strong>. Hay cuatro reportes disponibles:</p>
<table>
    <tr><th>Reporte</th><th>Qué contiene</th></tr>
    <tr><td>Pedidos</td><td>Folio, tienda destino, almacén origen, estado, fecha de creación y unidades.</td></tr>
    <tr><td>Embarques</td><td>Folio, almacén origen, tienda destino, placa del vehículo, conductor, estado y fechas de salida/llegada.</td></tr>
    <tr><td>Incidencias</td><td>Tipo, descripción, embarque o pedido asociado, usuario y fecha.</td></tr>
    <tr><td>Movimientos</td><td>Bitácora de trazabilidad: tipo de registro, folio, estado, acción, descripción, usuario y fecha.</td></tr>
</table>

<h3>Filtrar la información</h3>
<ol>
    <li>Entra al menú <strong>Reportes</strong> y abre el reporte que necesitas.</li>
    <li>Usa los filtros de <strong>Desde</strong> y <strong>Hasta</strong> (rango de fechas) y, cuando aplique,
        el filtro de <strong>Estado/Tipo</strong>.</li>
    <li>Pulsa <strong>Filtrar</strong> para actualizar la tabla. Pulsa <strong>Limpiar</strong> para quitar los filtros.</li>
</ol>

<h3>Descargar</h3>
<ul>
    <li><strong>Descargar PDF</strong> — genera un documento con el encabezado, los filtros aplicados y la tabla completa.</li>
    <li><strong>Descargar Excel (.xlsx)</strong> — genera un libro de Excel con los encabezados en negrita y una fila por registro, compatible con
        el resto de aplicaciones de hojas de cálculo.</li>
</ul>
<p>Ambas descargas respetan los filtros seleccionados y nombran el archivo con el tipo de reporte y la
fecha de generación (ej. <code>reporte-pedidos-20260910-123015.pdf</code>).</p>
<div class="box">Los reportes son de solo lectura: no modifican la información del sistema. Si necesitas un reporte
concreto con más detalle, contacta al administrador.</div>

<!-- ==================== ALERTAS ==================== -->
<h2>21. Alertas por correo</h2>
<p>El sistema envía <strong>avisos automáticos por correo</strong> a todos los usuarios con rol
<strong>Administrador</strong> cuando ocurre alguno de estos eventos:</p>
<table>
    <tr><th>Evento</th><th>Correo recibido</th></tr>
    <tr><td>Un pedido entra al almacén</td><td>“Pedido <code>PED-…</code> entró al almacén” con tienda, almacén, fecha y unidades recibidas.</td></tr>
    <tr><td>Un embarque sale a ruta</td><td>“Embarque <code>EMB-…</code> salió a ruta” con destino, vehículo, conductor, fecha de salida y pedidos.</td></tr>
    <tr><td>Un pedido se entrega en tienda</td><td>“Pedido <code>PED-…</code> entregado en tienda” con tienda, fecha y estado.</td></tr>
    <tr><td>Se registra una incidencia en ruta</td><td>“Incidencia en ruta: [tipo]” con fecha, descripción y embarque afectado.</td></tr>
</table>
<p>Cada correo incluye un enlace al detalle del pedido o del viaje para consultar la información completa.
Los avisos son de solo lectura y no requieren acción por parte del administrador.</p>
<div class="box">Los correos se envían únicamente a los usuarios con rol <strong>Administrador</strong>. Si no
quieres recibirlos, solicita el cambio a tu responsable de TI.</div>

<!-- ==================== DEVOLUCIONES ==================== -->
<h2>22. Devoluciones de tienda al almacén</h2>
<p>Las tiendas pueden devolver mercancía de un pedido <strong>entregado</strong> (<code>RECIBIDO_TIENDA</code>)
o <strong>cerrado</strong> (<code>CERRADO</code>). El módulo <strong>Devoluciones</strong> registra la solicitud
con su motivo y permite al almacén confirmar la recepción.</p>

<h3>Registrar una devolución (Tienda)</h3>
<ol>
    <li>Entra a <strong>Recepción</strong> y localiza el pedido entregado o cerrado.</li>
    <li>Pulsa el botón <strong>Devolver</strong> del pedido.</li>
    <li>Selecciona el <strong>motivo</strong> (mercancía dañada, producto equivocado, caducidad próxima,
        unidades sobrantes u otro) y llena una <strong>nota</strong> si es necesario.</li>
    <li>Para cada producto indica la <strong>cantidad a devolver</strong> (no puede exceder lo recibido)
        y el <strong>estado</strong> de la mercancía (en buen estado o dañada).</li>
    <li>Pulsa <strong>Registrar devolución</strong>. La devolución queda en estado <em>Solicitada</em>
        y se agrega a la trazabilidad del pedido.</li>
</ol>

<h3>Recibir la devolución (Almacén)</h3>
<ol>
    <li>En <strong>Devoluciones</strong>, el Almacén ve las solicitudes pendientes.</li>
    <li>Abre la devolución y pulsa <strong>Marcar como recibida en almacén</strong>.</li>
    <li>La devolución pasa a estado <em>Recibida en almacén</em>, con fecha y usuario responsable,
        y queda registrada en la trazabilidad del pedido.</li>
</ol>
<div class="box warn"><strong>Regla:</strong> solo pueden devolverse pedidos entregados o cerrados en tienda,
y cada tienda solo ve y maneja las devoluciones de sus propios pedidos.</div>

<div class="footer">
    Logística Maruchan · Manual de Usuario · Versión 1.5
</div>

</body>
</html>