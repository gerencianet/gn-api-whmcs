let url = new URL(window.location.href);


let id = url.searchParams.get("id");
let validatePayment = 0;





(function poll() {

    setTimeout(function() {

        $.get('modules/gateways/efi/gerencianet_lib/functions/frontend/ajax/PixPaymentStatusAjaxHandler.php', { "id": id }, function(data) {
            var pixStatus = JSON.parse(data);
            var buttonText = document.getElementById("txtBtn");
            var buttonSpinner = document.getElementById("spinnerBtn");
            var button = document.getElementById("confirmPayment");
            var img = document.getElementById("imgPix");
            var buttonCopy = document.getElementById("copyButton");
            if (pixStatus.paid) {
                img.style.opacity = '0.5';
                buttonCopy.disabled = true;
                buttonText.classList.add('fade-out');
                buttonSpinner.classList.remove('spinner');
                buttonSpinner.classList.add('fade-out');

                buttonText.innerHTML = "Pago";

                buttonText.classList.add('fade-in');

                return
            }
            if (validatePayment >= 30) {
                buttonText.classList.add('fade-out');
                buttonSpinner.classList.remove('spinner');
                buttonSpinner.classList.add('fade-out');
                buttonText.innerHTML = "Confirmar Pagamento";
                buttonText.classList.add('fade-in');
                button.setAttribute('data-toggle', 'tooltip');
                button.setAttribute('data-placement', 'top');
                button.setAttribute('title', 'Clique aqui após a finalização de seu pagamento');
                button.onclick = poll;
                return;
            }
            validatePayment++;
            poll()
        })

    }, 1000);
})();