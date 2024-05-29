<?php

require_once "../../../controladores/ventas.controlador.php";
require_once "../../../modelos/ventas.modelo.php";

require_once "../../../controladores/clientes.controlador.php";
require_once "../../../modelos/clientes.modelo.php";

require_once "../../../controladores/usuarios.controlador.php";
require_once "../../../modelos/usuarios.modelo.php";

require_once "../../../controladores/productos.controlador.php";
require_once "../../../modelos/productos.modelo.php";

class imprimirFactura {

    public $codigo;

    public function traerImpresionFactura() {

        // TRAEMOS LA INFORMACIÓN DE LA VENTA
        $itemVenta = "codigo";
        $valorVenta = $this->codigo;
        $respuestaVenta = ControladorVentas::ctrMostrarVentas($itemVenta, $valorVenta);

        $fecha = date("d/m/Y H:i:s", strtotime($respuestaVenta["fecha"]));
        $productos = json_decode($respuestaVenta["productos"], true);
        $neto = number_format($respuestaVenta["neto"], 2);
        $impuesto = number_format($respuestaVenta["impuesto"], 2);
        $total = number_format($respuestaVenta["total"], 2);

        // TRAEMOS LA INFORMACIÓN DEL CLIENTE
        $itemCliente = "id";
        $valorCliente = $respuestaVenta["id_cliente"];
        $respuestaCliente = ControladorClientes::ctrMostrarClientes($itemCliente, $valorCliente);

        // TRAEMOS LA INFORMACIÓN DEL VENDEDOR
        $itemVendedor = "id";
        $valorVendedor = $respuestaVenta["id_vendedor"];
        $respuestaVendedor = ControladorUsuarios::ctrMostrarUsuarios($itemVendedor, $valorVendedor);

        // REQUERIMOS LA CLASE TCPDF
        require_once('tcpdf_include.php');

        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);

        $pdf->AddPage('P', 'A7');

        // ---------------------------------------------------------
        $bloque1 = <<<EOF

<table style="font-size:7.5px; text-align:center">

    <tr>
        
        <td style="width:160px;">
		<div>
		<strong style="font-size:12px;">CLIMASHOP</strong>
		<br>
		Av. Libramiento 2 #16041
		Fraccionamiento Torremolinos Costa Azul
		<br><br>
		Ticket de venta
		<br>
		Fecha: $fecha  Folio:$valorVenta
		<br>Cajero: $respuestaVendedor[nombre]
		<br>
		Cliente: $respuestaCliente[nombre]
	</div>
        </td>
    </tr>
</table>
<br>--------------------------------------<br>
EOF;

        $pdf->writeHTML($bloque1, false, false, false, false, '');

        // ---------------------------------------------------------
		// Generar encabezado de la tabla para los productos
		$encabezadoProductos = <<<EOF

		<table style="font-size:7px;">

			<tr>
			
				<td style="width:40px; text-align:center">
					<strong>Producto</strong>
				</td>

				<td style="width:40px; text-align:center">
					<strong>Cantidad</strong>
				</td>

				<td style="width:40px; text-align:center">
					<strong>Valor unitario</strong>
				</td>

				<td style="width:40px; text-align:center">
					<strong>Total</strong>
				</td>
				
			</tr>

		</table>

		EOF;

		$pdf->writeHTML($encabezadoProductos, false, false, false, false, '');

		// Iterar sobre los productos y mostrarlos en el ticket
		foreach ($productos as $key => $item) {

			$valorUnitario = number_format($item["precio"], 2);
			$precioTotal = number_format($item["total"], 2);

			// Generar fila de la tabla para cada producto
			$filaProducto = <<<EOF

		<table style="font-size:7px;">

			<tr>
			
				<td style="width:40px; text-align:left">
					$item[descripcion] 
				</td>

				<td style="width:40px; text-align:center">
					$item[cantidad]
				</td>

				<td style="width:40px; text-align:right">
					$valorUnitario
				</td>

				<td style="width:40px; text-align:right">
					$precioTotal
				</td>

			</tr>

		</table>

		EOF;

			$pdf->writeHTML($filaProducto, false, false, false, false, '');

}

        // ---------------------------------------------------------

        $bloque3 = <<<EOF

<table style="font-size:9px; text-align:right">

    <tr>
    
        <td style="width:80px;">
             NETO: 
        </td>

        <td style="width:80px;">
            $ $neto
        </td>

    </tr>

    <tr>
    
        <td style="width:80px;">
             IMPUESTO: 
        </td>

        <td style="width:80px;">
            $ $impuesto
        </td>

    </tr>

    <tr>
    
        <td style="width:160px;">
             --------------------------
        </td>

    </tr>

    <tr>
    
        <td style="width:80px;">
             TOTAL: 
        </td>

        <td style="width:80px;">
            $ $total
        </td>

    </tr>

    <tr>
    
        <td style="width:160px;">
            <br>
            <br>
            Muchas gracias por su compra
        </td>

    </tr>

</table>

EOF;

        $pdf->writeHTML($bloque3, false, false, false, false, '');

        // ---------------------------------------------------------
        // SALIDA DEL ARCHIVO 
        $pdf->Output('factura.pdf');

    }

}

$factura = new imprimirFactura();
$factura->codigo = htmlspecialchars($_GET["codigo"]);
$factura->traerImpresionFactura();

?>
