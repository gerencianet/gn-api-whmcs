<?php
require_once __DIR__ . '/../../../../../init.php';
use Illuminate\Database\Capsule\Manager as Capsule;
use WHMCS\Config\Setting;




$whmcsUrl = rtrim(Setting::getValue('SystemURL'), '/');

// Verificar se o usuário está logado


    if (isset($_GET['identificadorPagamento'])) {
        $idPagamento = $_GET['identificadorPagamento'];
        $pagamento = Capsule::table('tblefiopenfinance')
            ->where('identificadorPagamento', '=', $idPagamento)
            ->select('invoiceid')
            ->first();
        $idFatura = $pagamento->invoiceid;

    }

    $cardClass = '';
    $message = '';

    if (isset($_GET['erro'])) { 
        $textoFalha = $_GET['erro'];
        $cardClass = 'failure';
        $message = "Erro: " . $textoFalha;
    } 
?>

<html>
<head>
    <title>Finalização</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style media="screen">
        /* Estilos CSS */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 20px;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            text-align: center;
        }
        h1 {
            color: #333;
        }
        .failure {
            color: red;
        }
        .button {
            display: inline-block;
            padding: 10px 20px;
            background-color: #333;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
        }
        .cardMsg {
            display:flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }

    .payment-success {
        text-align: center;
        font-family: Arial, sans-serif;
        color: #333333;
        margin-top: -200px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        align-items: center;
    }
    .payment-success img{
        width: 350px;
    }
    .payment-success h1 {
      font-size: 2.2rem;
      margin-bottom: 20px;
      color: #f37021;
    }
    .payment-success p {
      font-size: 1.4rem;
      margin-bottom: 30px;
    }

    .spinner {
      display: inline-block;
      width: 40px;
      height: 40px;
      border: 4px solid #f37021;
      border-radius: 50%;
      border-top-color: transparent;
      animation: spin 0.8s ease-in-out infinite;
    }
    .fade-out {
        animation-name: fadeOut;
        animation-duration: 0.5s;
        animation-fill-mode: forwards;
    }
    .fade-in {
        animation-name: fadeIn;
        animation-duration: 0.5s;
        animation-fill-mode: forwards;
    }

    @keyframes spin {
      0% {
        transform: rotate(0deg);
      }
      100% {
        transform: rotate(360deg);
      }
    }
    @keyframes fadeOut {
        0% {
            opacity: 1;
        }
        100% {
            opacity: 0;
        }
    }
    @keyframes fadeIn {
        0% {
            opacity: 0;
        }
        100% {
            opacity: 1;
        }
    }
    @media screen and (max-width: 768px) {
        .payment-success h1 {
            font-size: 1.3rem;
        }
        .payment-success p {
            font-size: 1.1rem;
        }
        .payment-success img{
            width: 250px;
        }
    }
    </style>
    
</head>
<body>
    <?php if (!empty($_GET['erro'])): ?>
        <div class="card">
            <h1 class="failure">Falha!</h1>
            <p class="failure"><?php echo $message; ?></p>
            <a href=<?php echo "$whmcsUrl/clientarea.php?action=invoices"?> class="button">Voltar para as faturas</a>
        </div>
    <?php endif; ?>
    <?php if (empty($_GET['erro'])): ?>
        <div class="payment-success">
        <?php echo "<img  src='$whmcsUrl/modules/gateways/efi/gerencianet_lib/images/efi.png' />"?>
            <h1>Pagamento Em Processamento!</h1>
            <p>Olá! O seu pagamento  está sendo processado. Por favor, aguarde alguns segundos enquanto realizamos o processamento.</p>
            <div class="spinner"></div>
        </div>
    <?php endif; ?>
    <!-- JavaScript -->
    <?php if ($pagamento !== null && empty($_GET['erro'])): ?>
        <script>
            let validatePayment = 0;
            
            let  container = document.getElementsByClassName("payment-success")[0];

            let intervalId = setInterval(function() {
                fetch("<?php echo $whmcsUrl?>/modules/gateways/efi/gerencianet_lib/functions/frontend/ajax/OFPaymentStatusAjaxHandler.php?id=<?php echo $idFatura?>", {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(function(response) {
                    if (response.ok) {
                        return response.json();
                    }
                    throw new Error('Erro na resposta da requisição.');
                })
                .then(function(ofStatus) {
                    if (ofStatus.paid) {
                        window.location.href = '<?php echo $whmcsUrl ?>/viewinvoice.php?id=<?php echo $idFatura ?>';
                    }
                    if (validatePayment == 30) {
                        let whmcsUrl = '<?php echo $whmcsUrl ?>';
                        let idFatura = '<?php echo $idFatura ?>';
                        let link = document.createElement('a');
                        link.href = whmcsUrl + '/viewinvoice.php?id=' + idFatura;
                        link.className = 'button';
                        link.innerText = 'Voltar para a fatura';
                        clearInterval(intervalId);
                        container.classList.add("fade-out");
                        document.getElementsByClassName("spinner")[0].style.display = "none";
                        
                        setTimeout(function() {
                            container.getElementsByTagName("h1")[0].innerText = "Pagamento Não Processado";
                            container.getElementsByTagName("p")[0].innerText = "O tempo para processamento do pagamento expirou. Por favor, verifique  sua fatura e o banco utilizado para pagamento";
                            container.appendChild(link);
                            container.classList.add("fade-in");
                        }, 500);
                    }
                    validatePayment++;
                })
                .catch(function(error) {
                    console.error(error);
                });
            }, 1000);
        </script>
    <?php endif; ?>

</body>
</html>

