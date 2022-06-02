window.onload = function () {
    var linkCss = '<link rel="stylesheet" href="modules/gateways/gerencianet/gerencianet_lib/css/viewInvoiceModal.css">';
    document.body.insertAdjacentHTML('beforeend', linkCss);
    /**
     * Função Gerencianet para gerar o payment token
     */
    $gn.ready(function (checkout) {
        setTimeout(() => {
            let numCartaoLarge = $('#numCartao');
            let numCartaoMobile = $('#numCartaoMobile');
            $('#numCartao').blur(() => {
                var numCartao = $('#numCartao').val().replaceAll(' ', '');
                var numInputCartao = $('#numCartao');
                var invoiceValue = $('.invoice_value').val() * 100;
                if (brandOption(numInputCartao) != '' && brandOption(numInputCartao) != undefined && brandOption(numInputCartao) != null && numCartao.length >= 13) {
                    checkout.getInstallments(invoiceValue, brandOption(numInputCartao), function (error, response) {
                        if (error) {
                            console.log(error)
                        } else {
                            let installmentsOptions;
                            for (let i = 0; i < response.data.installments.length; i++) {
                                let juros = (((response.data.installments[i].value / 100) - 0.01) * (i + 1)) > $('.invoice_value').val() ? 'com juros' : 'sem juros';
                                installmentsOptions += `<option valor=${response.data.installments[i].value} value="${i + 1}">${i + 1}x de R$${response.data.installments[i].currency} ${juros} </option> `;
                            }
                            if ($("#numParcelas option:selected").val() == 1 || $("#numParcelas option:selected").val() == '' ) {
                                $('#numParcelas').html(installmentsOptions);
                                $("#dynamicValue").html(`${formatValue($(".invoice_value").val())}`)
                            }
                            verifyPaymentToken(checkout, numInputCartao, 1);
                        }
                    })

                } else {

                    $('#numParcelas').html('<option>Insira os dados do seu cartão...</option>');
                }
            });
            $('#numParcelas').change(function () {
                $("#dynamicValue").html(`${formatValue(($('#numParcelas option:selected').attr('valor') * $('#numParcelas option:selected').val()) / 100)}`);


            });
            $('#codSeguranca').keyup(() => {
                if ($('#codSeguranca').val().length == 3) {

                    verifyPaymentToken(checkout, numCartaoLarge, 1);
                }

            });
            $('#mesVencimento').change(() => {
                verifyPaymentToken(checkout, numCartaoLarge, 1);
            });
            $('#anoVencimento').change(() => {
                verifyPaymentToken(checkout, numCartaoLarge, 1);
            });
            $('#numCartaoMobile').blur(() => {
                var numCartao = $('#numCartaoMobile').val().replaceAll(' ', '');
                var numInputCartao = $('#numCartaoMobile');
                var invoiceValue = $('.invoice_value').val() * 100;
                if (brandOption(numInputCartao) != '' && brandOption(numInputCartao) != undefined && brandOption(numInputCartao) != null && numCartao.length >= 13) {
                    checkout.getInstallments(invoiceValue, brandOption(numInputCartao), function (error, response) {
                        if (error) {
                            console.log(error)
                        } else {
                            let installmentsOptions;
                            for (let i = 0; i < response.data.installments.length; i++) {
                                installmentsOptions += `<option value="${i + 1}">${i + 1}x de R$${response.data.installments[i].currency}</option> `;
                            }
                            if ($("#numParcelasMobile option:selected").val() == 1 || $("#numParcelasMobile option:selected").val() == '' ) {
                                $('#numParcelasMobile').html(installmentsOptions);
                                $("#dynamicValue").html(`${formatValue($(".invoice_value").val())}`)
                            }
                            verifyPaymentToken(checkout, numInputCartao);
                        }
                    })

                } else {
                    $('#numParcelasMobile').html('<option>Insira os dados do seu cartão...</option>');
                }
            });
            $('#codSegurancaMobile').keyup(() => {
                if ($('#codSegurancaMobile').val().length == 3) {
                    verifyPaymentToken(checkout, numCartaoMobile);
                }

            });
            $('#mesVencimentoMobile').change(() => {
                verifyPaymentToken(checkout, numCartaoMobile);
            });
            $('#anoVencimentoMobile').change(() => {
                verifyPaymentToken(checkout, numCartaoMobile);
            });

        }, 2500);

    })


    let modal = setInterval(() => {
        if (document.getElementsByClassName('invoice-container').length > 0) {
            clearButton()
            $.ajax({
                url: "modules/gateways/gerencianet/gerencianet_lib/html/viewInvoiceModal.html",
                dataType: "html",
                cache: false,
                success: function (dados) {
                    document.body.insertAdjacentHTML('beforeend', dados);
                    let buttonFinalizar = $('.botao');
                    $(buttonFinalizar).css('display', 'none');

                    $('.payment-btn-container').append(buttonFinalizar);
                    $(buttonFinalizar).css('display', 'inline-block');


                    $(buttonFinalizar).click(function () {
                        $('.optionPaymentGerencianet').show(700);
                        $('.optionPaymentGerencianet').css('display', 'flex');
                    });
                    $('.optionPaymentGerencianet').click(function (e) {

                        if (e.target.className.includes('optionPaymentGerencianet')) {
                            $('.optionPaymentGerencianet').hide(700);
                        }

                    });

                    $('.fechar').click(function (e) {
                        $('.optionPaymentGerencianet').hide(700);

                    });

                    document.getElementsByClassName('billet')[0].onclick = () => {
                        $(".formularios :submit").removeAttr('disabled');
                        $('.formularios :submit').css('opacity', '1');
                        veriFyMinValue();

                        document.getElementById('boleto').checked = true;
                        document.getElementsByClassName('billet')[0].classList.add('selectedOption');
                        (document.getElementsByClassName('pix')[0] != undefined) ? document.getElementsByClassName('pix')[0].classList.remove('selectedOption') : "";
                        (document.getElementsByClassName('credit_card')[0] != undefined) ? document.getElementsByClassName('credit_card')[0].classList.remove('selectedOption') : "";

                        $('.button').html('Gerar Boleto');
                        $('.creditSelected').hide(0);
                        $('.pixSelected').hide(0);
                        $('.billetSelected').show();

                        $('#nameBillet').attr('required', true);
                        $('#documentClientBillet').attr('required', true);
                        $('#clientEmailBillet').attr('required', true);

                        $('#nameCredit').removeAttr('required');
                        $('#clientEmailCredit').removeAttr('required');
                        $('#documentClientCredit').removeAttr('required');
                        $('#telephoneCredit').removeAttr('required');
                        $('#dataNasce').removeAttr('required');
                        $('#rua').removeAttr('required');
                        $('#numero').removeAttr('required');
                        $('#bairro').removeAttr('required');
                        $('#cidade').removeAttr('required');
                        $('#estado').removeAttr('required');
                        $('#cep').removeAttr('required');

                        if ($(document).width() > 767) {
                            $('#numCartao').removeAttr('required');
                            $('#codSeguranca').removeAttr('required');
                            $('#mesVencimento').removeAttr('required');
                            $('#anoVencimento').removeAttr('required');
                            $('#numParcelas').removeAttr('required');
                        } else {
                            $('#numCartaoMobile').removeAttr('required');
                            $('#codSegurancaMobile').removeAttr('required');
                            $('#mesVencimentoMobile').removeAttr('required');
                            $('#anoVencimentoMobile').removeAttr('required');
                            $('#numParcelasMobile').removeAttr('required');
                        }


                        $('#documentClientPix').removeAttr('required');
                        $('#clientNamePix').removeAttr('required');



                    }
                    document.getElementsByClassName('credit_card')[0].onclick = () => {
                        if ($(".invalid-feedback").length > 0) {
                            $(".formularios :submit").prop('disabled', true);
                            $('.formularios :submit').css('opacity', '0.3');
                        }
                        veriFyMinValue();
                        document.getElementById('credit').checked = true;
                        document.getElementsByClassName('credit_card')[0].classList.add('selectedOption');
                        (document.getElementsByClassName('pix')[0] != undefined) ? document.getElementsByClassName('pix')[0].classList.remove('selectedOption') : "";
                        (document.getElementsByClassName('billet')[0] != undefined) ? document.getElementsByClassName('billet')[0].classList.remove('selectedOption') : "";
                        $('.button').html('Pagar');
                        $('.pixSelected').hide(0);
                        $('.billetSelected').hide(0);
                        $('.creditSelected').show();

                        $('#nameBillet').removeAttr('required');
                        $('#documentClientBillet').removeAttr('required');
                        $('#clientEmailBillet').removeAttr('required');

                        $('#nameCredit').attr('required', true);
                        $('#clientEmailCredit').attr('required', true);
                        $('#documentClientCredit').attr('required', true);
                        $('#telephoneCredit').attr('required', true);
                        $('#dataNasce').attr('required', true);
                        $('#rua').attr('required', true);
                        $('#numero').attr('required', true);
                        $('#bairro').attr('required', true);
                        $('#cidade').attr('required', true);
                        $('#estado').attr('required', true);
                        $('#cep').attr('required', true);

                        if ($(document).width() > 767) {
                            $('#numCartao').attr('required', true);
                            $('#codSeguranca').attr('required', true);
                            $('#mesVencimento').attr('required', true);
                            $('#anoVencimento').attr('required', true);
                            $('#numParcelas').attr('required', true);
                        } else {
                            $('#numCartaoMobile').attr('required', true);
                            $('#codSegurancaMobile').attr('required', true);
                            $('#mesVencimentoMobile').attr('required', true);
                            $('#anoVencimentoMobile').attr('required', true);
                            $('#numParcelasMobile').attr('required', true);
                        }


                        $('#documentClientPix').removeAttr('required');
                        $('#clientNamePix').removeAttr('required');

                    }
                    document.getElementsByClassName('pix')[0].onclick = () => {
                        $(".formularios :submit").removeAttr('disabled');
                        $('.formularios :submit').css('opacity', '1');
                        document.getElementById('pix').checked = true;
                        document.getElementsByClassName('pix')[0].classList.add('selectedOption');
                        (document.getElementsByClassName('credit_card')[0] != undefined) ? document.getElementsByClassName('credit_card')[0].classList.remove('selectedOption') : "";
                        (document.getElementsByClassName('billet')[0] != undefined) ? document.getElementsByClassName('billet')[0].classList.remove('selectedOption') : "";
                        $('.button').html('Gerar QRCode');
                        $('.billetSelected').hide(0);
                        $('.creditSelected').hide(0);
                        $('.pixSelected').show();


                        $('#nameBillet').removeAttr('required');
                        $('#documentClientBillet').removeAttr('required');
                        $('#clientEmailBillet').removeAttr('required');


                        $('#nameCredit').removeAttr('required');
                        $('#clientEmailCredit').removeAttr('required');
                        $('#documentClientCredit').removeAttr('required');
                        $('#telephoneCredit').removeAttr('required');
                        $('#dataNasce').removeAttr('required');
                        $('#rua').removeAttr('required');
                        $('#numero').removeAttr('required');
                        $('#bairro').removeAttr('required');
                        $('#cidade').removeAttr('required');
                        $('#estado').removeAttr('required');
                        $('#cep').removeAttr('required');

                        if ($(document).width() > 767) {
                            $('#numCartao').removeAttr('required');
                            $('#codSeguranca').removeAttr('required');
                            $('#mesVencimento').removeAttr('required');
                            $('#anoVencimento').removeAttr('required');
                            $('#numParcelas').removeAttr('required');
                        } else {
                            $('#numCartaoMobile').removeAttr('required');
                            $('#codSegurancaMobile').removeAttr('required');
                            $('#mesVencimentoMobile').removeAttr('required');
                            $('#anoVencimentoMobile').removeAttr('required');
                            $('#numParcelasMobile').removeAttr('required');
                        }

                        $('#documentClientPix').attr('required', true);
                        $('#clientNamePix').attr('required', true);


                    }
                    document.querySelectorAll('.button')[0].onclick = (e) => {



                        if ((verifyActivation('boleto')) ? document.getElementById('boleto').checked : false) {
                            validationForm('billet', e)
                        } else if ((verifyActivation('pix')) ? document.getElementById('pix').checked : false) {
                            validationForm('pix', e)
                        } else {
                            validationForm('credit', e)
                        }

                    }
                    $("#documentClientBillet").on('paste', (e) => {
                        try {
                            $("#documentClientBillet").unmask();
                        } catch (e) { }

                        var tamanho = e.originalEvent.clipboardData.getData('text').replaceAll('.', '').replaceAll('-', '').length;
                        if (tamanho <= 11) {
                            $("#documentClientBillet").mask("999.999.999-99");
                        } else {
                            $("#documentClientBillet").mask("99.999.999/9999-99");
                        }

                        // ajustando foco
                        var elem = this;
                        setTimeout(function () {
                            // mudo a posição do seletor
                            elem.selectionStart = elem.selectionEnd = 10000;
                        }, 0);
                        // reaplico o valor para mudar o foco
                        var currentValue = $(this).val();
                        $(this).val('');
                        $(this).val(currentValue);
                    });
                    $("#documentClientCredit").on('paste', (e) => {
                        try {
                            $("#documentClientCredit").unmask();

                        } catch (e) { }

                        var tamanho = e.originalEvent.clipboardData.getData('text').replaceAll('.', '').replaceAll('-', '').length;

                        if (tamanho <= 11) {
                            $("#documentClientCredit").mask("999.999.999-99");
                        } else {
                            $("#documentClientCredit").mask("99.999.999/9999-99");
                        }

                        // ajustando foco
                        var elem = this;
                        setTimeout(function () {
                            // mudo a posição do seletor
                            elem.selectionStart = elem.selectionEnd = 10000;
                        }, 0);
                        // reaplico o valor para mudar o foco
                        var currentValue = $(this).val();
                        $(this).val('');
                        $(this).val(currentValue);
                    });
                    $("#documentClientPix").on('paste', (e) => {
                        try {
                            $("#documentClientPix").unmask();
                        } catch (e) { }

                        var tamanho = e.originalEvent.clipboardData.getData('text').replaceAll('.', '').replaceAll('-', '').length;

                        if (tamanho <= 11) {
                            $("#documentClientPix").mask("999.999.999-99");
                        } else {
                            $("#documentClientPix").mask("99.999.999/9999-99");
                        }

                        // ajustando foco
                        var elem = this;
                        setTimeout(function () {
                            // mudo a posição do seletor
                            elem.selectionStart = elem.selectionEnd = 10000;
                        }, 0);
                        // reaplico o valor para mudar o foco
                        var currentValue = $(this).val();
                        $(this).val('');
                        $(this).val(currentValue);
                    });

                    $('.creditSelected').hide(0);
                    $('.pixSelected').hide(0);
                    intervalTotal();
                    format();
                    shadowButton();
                    formatYear();
                    autoComplete();
                    veriFyMinValue();
                }
            });
        }
    }, 100);

    function clearButton() {
        clearInterval(modal);
    }

    /**
     * Mascara aplicada nos inputs 
     */
    function format() {
        $("#documentClientBillet").keydown(function () {
            try {
                $("#documentClientBillet").unmask();
            } catch (e) { }

            var tamanho = $("#documentClientBillet").val().length;
            if (tamanho < 13) {
                $("#documentClientBillet").mask("999.999.999-999999");
            } else {
                $("#documentClientBillet").mask("99.999.999/9999-99");
            }

            // ajustando foco
            var elem = this;
            setTimeout(function () {
                // mudo a posição do seletor
                elem.selectionStart = elem.selectionEnd = 10000;
            }, 0);
            // reaplico o valor para mudar o foco
            var currentValue = $(this).val();
            $(this).val('');
            $(this).val(currentValue);
        });
        $("#documentClientCredit").keydown(function () {
            try {
                $("#documentClientCredit").unmask();

            } catch (e) { }

            var tamanho = $("#documentClientCredit").val().length;

            if (tamanho < 13) {
                $("#documentClientCredit").mask("999.999.999-999999");
            } else {
                $("#documentClientCredit").mask("99.999.999/9999-99");
            }

            // ajustando foco
            var elem = this;
            setTimeout(function () {
                // mudo a posição do seletor
                elem.selectionStart = elem.selectionEnd = 10000;
            }, 0);
            // reaplico o valor para mudar o foco
            var currentValue = $(this).val();
            $(this).val('');
            $(this).val(currentValue);
        });
        $("#documentClientPix").keydown(function () {
            try {
                $("#documentClientPix").unmask();
            } catch (e) { }

            var tamanho = $("#documentClientPix").val().length;

            if (tamanho < 13) {
                $("#documentClientPix").mask("999.999.999-999999");
            } else {
                $("#documentClientPix").mask("99.999.999/9999-99");
            }

            // ajustando foco
            var elem = this;
            setTimeout(function () {
                // mudo a posição do seletor
                elem.selectionStart = elem.selectionEnd = 10000;
            }, 0);
            // reaplico o valor para mudar o foco
            var currentValue = $(this).val();
            $(this).val('');
            $(this).val(currentValue);
        });

        $("#telephoneBillet").mask("(00) 0000-00009");
        $("#telephoneCredit").mask("(00) 0000-00009");
        $("#cep").mask("99.999-999");
        $("#numCartao").mask("0000 0000 0000 0000");
        $("#numCartaoMobile").mask("0000 0000 0000 0000");




    }

    function shadowButton() {
        $('.button').hover(() => {
            $('.button').addClass('shadow-lg')
        }, () => {
            $('.button').removeClass('shadow-lg')
        })

    }

    function formatYear() {
        let anoVencimento = new Date().getFullYear();
        let optionAno = `<option value="${anoVencimento}">${anoVencimento}</option>`
        $('#anoVencimento').append(optionAno);
        for (let i = 1; i < 16; i++) {
            let optionAno = `<option value="${anoVencimento + i}">${anoVencimento + i}</option>`
            $('#anoVencimento').append(optionAno);

        }
        $('#anoVencimentoMobile').append(optionAno);
        for (let i = 1; i < 15; i++) {
            let optionAno = `<option value="${anoVencimento + i}">${anoVencimento + i}</option>`
            $('#anoVencimentoMobile').append(optionAno);

        }
    }
    /**
     * Validação da bandeira do cartão
     */

    let brandOption = (element) => {
        let bandeiraCartao = ''
        var numCartao = element.val().replaceAll(' ', '');
        var brand = "null";
        if (numCartao.length >= 13) {
            // MASTERCARD
            var regexMastercard = /^((5(([1-2]|[4-5])[0-9]{8}|0((1|6)([0-9]{7}))|3(0(4((0|[2-9])[0-9]{5})|([0-3]|[5-9])[0-9]{6})|[1-9][0-9]{7})))|((508116)\\d{4,10})|((502121)\\d{4,10})|((589916)\\d{4,10})|(2[0-9]{15})|(67[0-9]{14})|(506387)\\d{4,10})/;
            var resMastercard = regexMastercard.exec(numCartao);
            if (resMastercard) {
                brand = "<img src='modules/gateways/gerencianet/gerencianet_lib/images/mastercard.png' style='width:50px;' >";
                bandeiraCartao = 'mastercard'
            }
            // ELO  
            var regexELO = /^4011(78|79)|^43(1274|8935)|^45(1416|7393|763(1|2))|^50(4175|6699|67[0-6][0-9]|677[0-8]|9[0-8][0-9]{2}|99[0-8][0-9]|999[0-9])|^627780|^63(6297|6368|6369)|^65(0(0(3([1-3]|[5-9])|4([0-9])|5[0-1])|4(0[5-9]|[1-3][0-9]|8[5-9]|9[0-9])|5([0-2][0-9]|3[0-8]|4[1-9]|[5-8][0-9]|9[0-8])|7(0[0-9]|1[0-8]|2[0-7])|9(0[1-9]|[1-6][0-9]|7[0-8]))|16(5[2-9]|[6-7][0-9])|50(0[0-9]|1[0-9]|2[1-9]|[3-4][0-9]|5[0-8]))/;
            var resELO = regexELO.exec(numCartao);
            if (resELO) {
                brand = "<img src='modules/gateways/gerencianet/gerencianet_lib/images/elo.png' style='width:50px;' >";
                bandeiraCartao = 'elo'
            }
            // AMEX 
            var regexAmex = /^3[47][0-9]{13}$/;
            var resAmex = regexAmex.exec(numCartao);
            if (resAmex) {
                brand = "<img src='modules/gateways/gerencianet/gerencianet_lib/images/amex.png' style='width:50px;' >";
                bandeiraCartao = 'amex'
            }
            //Diners 
            var regexDiners = /(36[0-8][0-9]{3}|369[0-8][0-9]{2}|3699[0-8][0-9]|36999[0-9])/;
            var resDiners = regexDiners.exec(numCartao);
            if (resDiners) {
                brand = "<img src='modules/gateways/gerencianet/gerencianet_lib/images/diners.png' style='width:50px;' >";
                bandeiraCartao = 'diners'
            }

            // Hipercard
            var regexHipercard = /^606282|^3841(?:[0|4|6]{1})0/;
            var resHipercard = regexHipercard.exec(numCartao);
            if (resHipercard) {
                brand = "<img src='modules/gateways/gerencianet/gerencianet_lib/images/hipercard.png' style='width:50px;' >";
                bandeiraCartao = 'hipercard'
            }
            // Visa 
            var regexVisa = /^4[0-9]{15}$/;
            var resVisa = regexVisa.exec(numCartao);
            if (resVisa) {
                brand = "<img src='modules/gateways/gerencianet/gerencianet_lib/images/visa.png' style='width:50px;' >";
                bandeiraCartao = 'visa'
            }

            // MOSTRA RESULTADO

            if (brand != 'null') {
                $('#card').empty();
                $('#card').append(brand);
            }

            if (brand == 'null') {
                $('#card').empty();
                $('#card').append("<img src='modules/gateways/gerencianet/gerencianet_lib/images/cartao.png' style='width:50px;' >");
            }
        } else {
            $('#card').empty();
            $('#card').append("<img src='modules/gateways/gerencianet/gerencianet_lib/images/cartao.png' style='width:50px;' >");
        }

        return bandeiraCartao;

    }

    function verifyPaymentToken(checkout, inputCartao, i = 0) {
        $('.payment_token').each((i, element) => {
            $(element).remove();
        })
        if (i == 1) {
            var numCartao = inputCartao.val().replaceAll(' ', '');
            let brandOptionVerify = brandOption(inputCartao) != '' && brandOption(inputCartao) != undefined && brandOption(inputCartao) != null && numCartao.length >= 13;
            let codSeguranca = $('#codSeguranca').val().length >= 3;
            let mesVencimento = $('#mesVencimento option:selected').val().length == 2;
            let anoVencimento = $('#anoVencimento option:selected').val().length == 4;
            let numParcelas = $('#numParcelas option:selected').val().length > 0;

            if (brandOptionVerify && codSeguranca && mesVencimento && anoVencimento && numParcelas) {

                $(".invalid-feedback").remove();
                checkout.getPaymentToken({
                    brand: brandOption(inputCartao),
                    number: $('#numCartao').val().replaceAll(' ', ''),
                    cvv: $('#codSeguranca').val(),
                    expiration_month: $('#mesVencimento option:selected').val(),
                    expiration_year: $('#anoVencimento option:selected').val()
                }, (err, res) => {
                    if (err == null) {
                        var input = '';
                        input = $("<input />", {
                            value: res.data.payment_token,
                            type: 'hidden',
                            name: 'payment_token',
                            class: 'payment_token'
                        });
                        $(".formularios").append(input);
                        $(".formularios :submit").removeAttr('disabled');
                        $("#numCartao").removeClass("is-invalid");
                        $('.formularios :submit').css('opacity', '1');
                    } else {
                        var input = '';
                        input = $("<div />", {
                            class: 'invalid-feedback'
                        });
                        $(input).html('Número do cartão inválido');
                        $('#numCartao').after(input);
                        $(".formularios :submit").prop('disabled', true);
                        $('.formularios :submit').css('opacity', '0.3');
                        $("#numCartao").addClass("is-invalid");

                    }


                })
            }
        } else {
            var numCartao = inputCartao.val().replaceAll(' ', '');
            let brandOptionVerify = brandOption(inputCartao) != '' && brandOption(inputCartao) != undefined && brandOption(inputCartao) != null && numCartao.length >= 13;
            let codSeguranca = $('#codSegurancaMobile').val().length >= 3;
            let mesVencimento = $('#mesVencimentoMobile option:selected').val().length == 2;
            let anoVencimento = $('#anoVencimentoMobile option:selected').val().length == 4;
            let numParcelas = $('#numParcelasMobile option:selected').val().length > 0;

            if (brandOptionVerify && codSeguranca && mesVencimento && anoVencimento && numParcelas) {
                checkout.getPaymentToken({
                    brand: brandOption(inputCartao),
                    number: $('#numCartaoMobile').val().replaceAll(' ', ''),
                    cvv: $('#codSegurancaMobile').val(),
                    expiration_month: $('#mesVencimentoMobile option:selected').val(),
                    expiration_year: $('#anoVencimentoMobile option:selected').val()
                }, (err, res) => {
                    if (err == null) {
                        var input = '';
                        input = $("<input />", {
                            value: res.data.payment_token,
                            type: 'hidden',
                            name: 'payment_token',
                            class: 'payment_token'
                        });
                        $(".formularios").append(input);
                        $(".formularios :submit").removeAttr('disabled');
                        $("#numCartaoMobile").removeClass("is-invalid");
                        $('.formularios :submit').css('opacity', '1');
                    } else {
                        var input = '';
                        input = $("<div />", {
                            class: 'invalid-feedback'
                        });
                        $(input).html('Número do cartão inválido');
                        $('#numCartaoMobile').after(input);
                        $(".formularios :submit").prop('disabled', true);
                        $('.formularios :submit').css('opacity', '0.3');
                        $("#numCartaoMobile").addClass("is-invalid");
                    }

                })
            }
        }

    }

    let intervalTotal = () => {
        if ($('.invoice_value').val() > 0) {
            addTotalBoleto();
            addTotalPix();
            addTotalCartao();
           
            if ($('.ativarCredito').val() != 1  ) {
               
                $('.credit_card').remove();
                $('.creditSelected').remove();
                if ($('.boletoOption').val() != 1  ) {
                    $('.pix').addClass('selectedOption');
                    $('#pix').attr('checked', true);
                    $('.pixSelected').show();
                    $(".button").html('Gerar QRCode');
                }
            }
            if ($('.boletoOption').val() != 1   ) {
                $('.billet').remove();
                $('.billetSelected').remove();
                $('.credit_card').addClass('selectedOption');
                $('#credit').attr('checked', true);
                $('.creditSelected').show();
                $(".button").html('Pagar');

            }
            if ($('.pixOption').val() != 1) {
                $('.pix').remove();
                $('.pixSelected').remove();

            }
            
            
        }
    };

    function verifyActivation(name) {
        return document.getElementById(name) != null;
    }

    function formatValue(value) {
        return Intl.NumberFormat('pt-br', { style: 'currency', currency: 'BRL' }).format(value);
    }

    function addTotalBoleto() {
        if ($('.totalDescontoBoleto').val() > 0) {
            var div = '';
            var span = '';

            div = $("<div />", {
                id: 'total',
                class: 'container-fluid d-flex justify-content-between border mt-5 p-1'
            });
            $('.billetSelected').append(div);
            span = $("<span />", {
                id: 'spanTotal',
                class: 'font-weight-bold'
            });
            $('#total').append(span);
            $('#spanTotal').html(`Total:`)
            span = $("<span />", {
                id: 'spanValorTotal',
                class: 'font-weight-bold'
            });
            $('#total').append(span);
            $('#spanValorTotal').html(`${formatValue($('.invoice_value').val())}`)
            div = $("<div />", {
                id: 'descontoBoleto',
                class: 'container-fluid d-flex justify-content-between border p-1'
            });
            $('.billetSelected').append(div);
            span = $("<span />", {
                id: 'spanDesconto',
                class: 'font-weight-bold'
            });
            $('#descontoBoleto').append(span);
            $('#spanDesconto').html(`Desconto boleto:`)
            span = $("<span />", {
                id: 'spanDescontoTotal',
                class: 'font-weight-bold'
            });
            $('#descontoBoleto').append(span);
            $('#spanDescontoTotal').html(`-${formatValue($('.totalDescontoBoleto').val())}`)
            div = $("<div />", {
                id: 'descontoBoletoSubtraido',
                class: 'container-fluid d-flex justify-content-between border p-1'
            });
            $('.billetSelected').append(div);
            span = $("<span />", {
                id: 'spanDescontoSubtraido',
                class: 'font-weight-bold'
            });
            $('#descontoBoletoSubtraido').append(span);
            $('#spanDescontoSubtraido').html(`Total a pagar:`)
            span = $("<span />", {
                id: 'spanDescontoTotalSubtraido',
                class: 'font-weight-bold'
            });
            $('#descontoBoletoSubtraido').append(span);
            $('#spanDescontoTotalSubtraido').html(`${formatValue($('.invoice_value').val() - $('.totalDescontoBoleto').val())}`)
        } else {
            div = '';
            span = '';
            div = $("<div />", {
                id: 'total',
                class: 'container-fluid d-flex justify-content-between border mt-5 p-1'
            });
            $('.billetSelected').append(div);
            span = $("<span />", {
                id: 'spanTotal',
                class: 'font-weight-bold'
            });
            $('#total').append(span);
            $('#spanTotal').html(`Total:`)
            span = $("<span />", {
                id: 'spanValorTotal',
                class: 'font-weight-bold'
            });
            $('#total').append(span);
            $('#spanValorTotal').html(`${formatValue($('.invoice_value').val())}`)
        }
    }

    function addTotalPix() {
        if ($('.totalDescontoPix').val() > 0) {
            var div = '';
            var span = '';
            div = $("<div />", {
                id: 'totalPix',
                class: 'container-fluid d-flex justify-content-between border mt-5 p-1'
            });
            $('.pixSelected').append(div);
            span = $("<span />", {
                id: 'spanTotalPix',
                class: 'font-weight-bold'
            });
            $('#totalPix').append(span);
            $('#spanTotalPix').html(`Total:`)
            span = $("<span />", {
                id: 'spanValorTotalPix',
                class: 'font-weight-bold'
            });
            $('#totalPix').append(span);
            $('#spanValorTotalPix').html(`${formatValue($('.invoice_value').val())}`)
            div = $("<div />", {
                id: 'descontoPix',
                class: 'container-fluid d-flex justify-content-between border p-1'
            });
            $('.pixSelected').append(div);
            span = $("<span />", {
                id: 'spanDescontoPix',
                class: 'font-weight-bold'
            });
            $('#descontoPix').append(span);
            $('#spanDescontoPix').html(`Desconto pix:`)
            span = $("<span />", {
                id: 'spanDescontoTotalPix',
                class: 'font-weight-bold'
            });
            $('#descontoPix').append(span);
            $('#spanDescontoTotalPix').html(`-${formatValue($('.totalDescontoPix').val())}`)
            div = $("<div />", {
                id: 'descontoPixSubtraido',
                class: 'container-fluid d-flex justify-content-between border p-1'
            });
            $('.pixSelected').append(div);
            span = $("<span />", {
                id: 'spanDescontoSubtraidoPix',
                class: 'font-weight-bold'
            });
            $('#descontoPixSubtraido').append(span);
            $('#spanDescontoSubtraidoPix').html(`Total a pagar:`)
            span = $("<span />", {
                id: 'spanDescontoTotalSubtraidoPix',
                class: 'font-weight-bold'
            });
            $('#descontoPixSubtraido').append(span);
            $('#spanDescontoTotalSubtraidoPix').html(`${formatValue($('.invoice_value').val() - $('.totalDescontoPix').val())}`)

        } else {
            var div = '';
            var span = '';
            div = $("<div />", {
                id: 'totalPix',
                class: 'container-fluid d-flex justify-content-between border mt-5 p-1'
            });
            $('.pixSelected').append(div);
            span = $("<span />", {
                id: 'spanTotalPix',
                class: 'font-weight-bold'
            });
            $('#totalPix').append(span);
            $('#spanTotalPix').html(`Total:`)
            span = $("<span />", {
                id: 'spanValorTotalPix',
                class: 'font-weight-bold'
            });
            $('#totalPix').append(span);
            $('#spanValorTotalPix').html(`${formatValue($('.invoice_value').val())}`)
        }

    }

    function addTotalCartao() {
        var div = '';
        var span = '';
        div = $("<div />", {
            id: 'totalCredito',
            class: 'container-fluid d-flex justify-content-between border mt-5 p-1'
        });
        $('.creditSelected').append(div);
        span = $("<span />", {
            id: 'spanTotaCredit',
            class: 'font-weight-bold'
        });
        $('#totalCredito').append(span);
        $('#spanTotaCredit').html(`Total à vista:`)
        span = $("<span />", {
            id: 'spanValorTotalCredit',
            class: 'font-weight-bold'
        });
        $('#totalCredito').append(span);
        $('#spanValorTotalCredit').html(`${formatValue($('.invoice_value').val())}`)
        $('#totalCredito').append(span);

        div = $("<div />", {
            class: 'container-fluid d-flex justify-content-between border p-1'
        });
        span = $("<span />", {
            class: 'font-weight-bold'
        });
        $(span).html('Total com juros:');
        $(div).append(span);
        span = $("<span />", {
            class: 'font-weight-bold',
            id: 'dynamicValue'
        });
        $(span).html(`${formatValue($('.invoice_value').val())}`);
        $(div).append(span);

        $(".creditSelected").append(div);
    }
    function veriFyMinValue() {
        if ($('.invoice_value').val() < 5) {
            $('.creditSelected').children().remove();
            $('.creditSelected').html('<span class="validationMessage text-danger">Compra miníma para cartão: R$5,00</span>');
            $(".formularios :submit").prop('disabled', true);
            $('.formularios :submit').css('opacity', '0.3');
            
        }
        if ($('.invoice_value').val() < 5) {
            $('.billetSelected').children().remove();
            $('.billetSelected').html('<span class="validationMessage text-danger">Compra miníma para boleto: R$5,00</span>');
            $(".formularios :submit").prop('disabled', true);
            $('.formularios :submit').css('opacity', '0.3');
            
        }
    }

}