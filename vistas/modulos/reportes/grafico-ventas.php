<?php

error_reporting(0);

if (isset($_GET["fechaInicial"])) {
    $fechaInicial = $_GET["fechaInicial"];
    $fechaFinal = $_GET["fechaFinal"];
} else {
    $fechaInicial = null;
    $fechaFinal = null;
}

$respuesta = ControladorVentas::ctrRangoFechasVentas($fechaInicial, $fechaFinal);

$ventasPorDia = array();

foreach ($respuesta as $key => $value) {
    // Capturamos la fecha completa
    $fecha = substr($value["fecha"], 0, 10);

    // Sumamos las ventas en el mismo día
    $ventasPorDia[$fecha] = isset($ventasPorDia[$fecha]) ? $ventasPorDia[$fecha] + $value["total"] : $value["total"];
}

?>

<!--=====================================
GRÁFICO DE VENTAS
======================================-->

<div class="box box-solid bg-teal-gradient">
    <div class="box-header">
        <i class="fa fa-th"></i>
        <h3 class="box-title">Gráfico de Ventas</h3>
    </div>
    <div class="box-body border-radius-none nuevoGraficoVentas">
        <div class="chart" id="line-chart-ventas" style="height: 250px;"></div>
    </div>
</div>

<script>
    // Obtener las fechas seleccionadas del DateRangePicker
    var fechaInicial = "<?php echo $fechaInicial; ?>";
    var fechaFinal = "<?php echo $fechaFinal; ?>";

    // Obtener el rango de fechas entre fechaInicial y fechaFinal
    var dateRange = getDateRange(fechaInicial, fechaFinal);

    // Crear un array con las ventas por día
    var ventasPorDia = [
        <?php
        foreach ($ventasPorDia as $fecha => $ventas) {
            echo "{ y: '" . $fecha . "', ventas: " . $ventas . " },";
        }
        ?>
    ];

    // Rellenar con ceros las fechas que no tengan ventas
    for (var i = 0; i < dateRange.length; i++) {
        if (!ventasPorDia.some(item => item.y === dateRange[i])) {
            ventasPorDia.push({ y: dateRange[i], ventas: 0 });
        }
    }

    // Ordenar el array por fecha ascendente
    ventasPorDia.sort((a, b) => new Date(a.y) - new Date(b.y));

    // Gráfico de Ventas
    var lineVentas = new Morris.Line({
        element: 'line-chart-ventas',
        resize: true,
        data: ventasPorDia,
        xkey: 'y',
        ykeys: ['ventas'],
        labels: ['Ventas'],
        lineColors: ['#efefef'],
        lineWidth: 2,
        hideHover: 'auto',
        gridTextColor: '#fff',
        gridStrokeWidth: 0.4,
        pointSize: 4,
        pointStrokeColors: ['#efefef'],
        gridLineColor: '#efefef',
        gridTextFamily: 'Open Sans',
        preUnits: '$',
        gridTextSize: 10,
        xLabelAngle: 45, // Rotar las etiquetas del eje x para mejorar la legibilidad
        xLabelFormat: function (x) {
            // Formato de las etiquetas del eje x (opcional)
            return moment(x).format('YYYY-MM-DD');
        }
    });

    // Función para obtener el rango de fechas entre dos fechas
    function getDateRange(startDate, endDate) {
        var dateArray = [];
        var currentDate = moment(startDate);
        var stopDate = moment(endDate);
        while (currentDate <= stopDate) {
            dateArray.push(moment(currentDate).format('YYYY-MM-DD'));
            currentDate = moment(currentDate).add(1, 'days');
        }
        return dateArray;
    }
</script>