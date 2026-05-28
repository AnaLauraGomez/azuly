<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../html/login.html");
    exit();
}
$_SESSION['fecha_recogida'] = $_POST['fecha_recogida'];
$_SESSION['metodo_pago'] = 'Tarjeta';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Pagar con PayPal</title>
    <script src="https://www.paypal.com/sdk/js?client-id=Aawn9UgMGpqD0aIvpep-O-Doshhoaeavgiu5xlEHXg9bPepS3Xo3KOOtsty2DjIVksx4k_wl8ureI5ty&currency=MXN"></script>
</head>
<body>
    <h2>Confirma tu pago con PayPal</h2>
    <div id="paypal-button-container"></div>

    <script>
    paypal.Buttons({
        createOrder: function(data, actions) {
            return fetch('calcular_total.php')
                .then(res => res.json())
                .then(data => {
                    return actions.order.create({
                        purchase_units: [{
                            amount: {
                                value: data.total
                            }
                        }]
                    });
                });
        },
        onApprove: function(data, actions) {
            return actions.order.capture().then(function(details) {
                alert('Pago completado por ' + details.payer.name.given_name);
                window.location.href = "finalizar_compra.php?paypal=1&fecha_recogida=" + 
                encodeURIComponent('<?php echo $_SESSION['fecha_recogida']; ?>') + 
                "&metodo_pago=Tarjeta";
            });
        }
    }).render('#paypal-button-container');
    </script>
</body>
</html>
