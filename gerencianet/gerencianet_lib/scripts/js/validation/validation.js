/**
 * Validação do formulário
 */
function validationForm(optionSelected, event) {

    switch (optionSelected) {
        case 'billet':
            validationBillet(event);
            break;
        case 'credit':
            validationCredit(event);
            break;
        case 'pix':
            validationPix(event);
            break;

        default:
            break;
    }

}

function validationBillet(event) {
    let message = '';
    let documentClientBillet = $('#documentClientBillet').val().replaceAll('.', '').replace('-', '').replace('/', '');
    let clientEmailBillet = $('#clientEmailBillet').val();
    let telephoneBillet = $('#telephoneBillet').val().replace('(', '').replace(')', '').replace(' ', '').replace('-', '');
    let nameClient = { 'value': $('#nameBillet').val(), 'nome': 'Nome/Razão Social' };
    if (documentClientBillet.length == 11) {
        if (!verifyCPF(documentClientBillet)) {
            event.preventDefault();
            scrollTop()
            message += `
                <div class="alert alert-danger" role="alert">
                    O campo <span class="font-weight-bold"> CPF</span> não foi preenchido corretamente.
                </div>
             `
        }

    } else if (documentClientBillet.length == 14) {
        if (!verifyCNPJ(documentClientBillet)) {
            event.preventDefault();
            scrollTop()
            message += `
                <div class="alert alert-danger" role="alert">
                    O campo <span class="font-weight-bold"> CNPJ</span> não foi preenchido corretamente.
                </div>
             `
        }
    } else {
        event.preventDefault();
        message += `
            <div class="alert alert-danger" role="alert">
            O campo <span class="font-weight-bold">CPF/CNPJ </span> não foi  preenchido.
            </div>
         `
    }
    if (!verifyEmail(clientEmailBillet)) {
        event.preventDefault();
        message += `
            <div class="alert alert-danger" role="alert">
               O campo <span class="font-weight-bold"> email</span> não foi preenchido corretamente.
            </div>
         `
    }
    if (telephoneBillet.length > 0) {
        if (!verifyPhone(telephoneBillet) || telephoneBillet.length < 10) {
            event.preventDefault();
            message += `
            <div class="alert alert-danger" role="alert">
                O campo <span class="font-weight-bold"> telefone</span> não foi preenchido corretamente.
            </div>
         `
        }
    }
    if (verifyInputsEmpty(nameClient).length > 0) {
        event.preventDefault();
        message += `
            <div class="alert alert-danger" role="alert">
                O campo <span class="font-weight-bold"> Nome/Razão Social</span> não foi preenchido corretamente.
            </div>
         `
    }
    scrollTop()
    if (message == '') {
        spinnerLoad()
        $('.meuPopUp').css('opacity', '0.3');
        $('.meuPopUp').addClass('desabilitar');
    }
    $('.messageValidationBillet').html(message);


}

function validationCredit(event) {
    let message = '';
    let documentClientCredit = $('#documentClientCredit').val().replaceAll('.', '').replace('-', '').replace('/', '');
    let clientEmailCredit = $('#clientEmailCredit').val();
    let telephoneCredit = $('#telephoneCredit').val().replace('(', '').replace(')', '').replace(' ', '').replace('-', '');
    let birthDate = $('#dataNasce').val();
    let cep = $('#cep').val();
    let nameClient = $('#nameCredit').val();
    let rua = $('#rua').val();
    let numero = $('#numero').val();
    let bairro = $('#bairro').val();
    let cidade = $('#cidade').val();
    let numCartao = '';
    let codSeguranca = '';
    let mes = '';
    let ano = '';
    let parcela = '';
    let estadoCliente = $("#estado option:selected").val();
    if ($(document).width() > 767) {
        numCartao = $('#numCartao').val();
        codSeguranca = $('#codSeguranca').val();
        mes = $("#mesVencimento option:selected").val();
        ano = $("#anoVencimento option:selected").val();
        parcela = $("#numParcelas option:selected").val();
    } else {
        numCartao = $('#numCartaoMobile').val();
        codSeguranca = $('#codSegurancaMobile').val();
        mes = $("#mesVencimentoMobile option:selected").val();
        ano = $("#anoVencimentoMobile option:selected").val();
        parcela = $("#numParcelasMobile option:selected").val();
    }
    let inputsEmpty = verifyInputsEmpty({
        'value': nameClient,
        'nome': 'Nome/Razão Social'
    }, {
        'value': rua,
        'nome': 'Rua'
    }, {
        'value': numero,
        'nome': 'Número'
    }, {
        'value': bairro,
        'nome': 'Bairro'
    }, {
        'value': cidade,
        'nome': 'Cidade'
    }, {
        'value': numCartao,
        'nome': 'Número do cartão'
    }, {
        'value': codSeguranca,
        'nome': 'Código de Segurança'
    }, {
        'value': estadoCliente,
        'nome': 'Estado'
    }, {
        'value': mes,
        'nome': 'Mês de Vencimento'
    }, {
        'value': ano,
        'nome': 'Ano de Vencimento'
    }, {
        'value': parcela,
        'nome': 'Parcelas'
    });

    if (inputsEmpty.length > 0) {

        message += inputsEmpty;
    }
    if (documentClientCredit.length == 11) {
        if (!verifyCPF(documentClientCredit)) {
            event.preventDefault();
            message += `
                <div class="alert alert-danger" role="alert">
                    O campo <span class="font-weight-bold"> CPF</span> não foi preenchido corretamente.
                </div>
             `
        }

    } else if (documentClientCredit.length == 14) {
        if (!verifyCNPJ(documentClientCredit)) {
            event.preventDefault();
            message += `
                <div class="alert alert-danger" role="alert">
                    O campo <span class="font-weight-bold"> CNPJ</span> não foi preenchido corretamente.
                </div>
             `
        }
    } else {
        event.preventDefault();
        message += `
            <div class="alert alert-danger" role="alert">
            O campo <span class="font-weight-bold"> CPF/CNPJ</span> não foi  preenchido.
            </div>
         `
    }
    if (!verifyEmail(clientEmailCredit)) {
        event.preventDefault();
        message += `
            <div class="alert alert-danger" role="alert">
               O campo <span class="font-weight-bold"> email</span> não foi preenchido corretamente.
            </div>
         `
    }
    if (!verifyPhone(telephoneCredit) || telephoneCredit.length < 10) {
        event.preventDefault();
        message += `
            <div class="alert alert-danger" role="alert">
                O campo <span class="font-weight-bold"> telefone</span> não foi preenchido corretamente.
            </div>
         `
    }
    if (!verifyBirthDate(birthDate)) {
        event.preventDefault();
        message += `
            <div class="alert alert-danger" role="alert">
                O campo <span class="font-weight-bold"> data de nascimento</span> não foi preenchido corretamente.
            </div>
         `
    }
    if (!verifyZipCode(cep)) {
        event.preventDefault();
        message += `
            <div class="alert alert-danger" role="alert">
                O campo <span class="font-weight-bold"> CEP</span> não foi preenchido corretamente.
            </div>
         `
    }
    scrollTop()
    if (message == '') {
        spinnerLoad()
        $('.meuPopUp').css('opacity', '0.3');
        $('.meuPopUp').addClass('desabilitar');
    }
    $('.messageValidationCredit').html(message);

}

function validationPix(event) {
    let message = '';
    let documentClientPix = $('#documentClientPix').val().replaceAll('.', '').replace('-', '').replace('/', '');
    let nameClient = { 'value': $('#clientNamePix').val(), 'nome': 'Nome/Razão Social' };
    if (documentClientPix.length == 11) {
        if (!verifyCPF(documentClientPix)) {
            event.preventDefault();
            message += `
                <div class="alert alert-danger" role="alert">
                    O campo <span class="font-weight-bold"> CPF</span> não foi preenchido corretamente.
                </div>
             `
        }

    } else if (documentClientPix.length == 14) {
        if (!verifyCNPJ(documentClientPix)) {
            event.preventDefault();
            message += `
                <div class="alert alert-danger" role="alert">
                    O campo <span class="font-weight-bold"> CNPJ</span> não foi preenchido corretamente.
                </div>
             `
        }
    } else {
        event.preventDefault();
        message += `
            <div class="alert alert-danger" role="alert">
           O campo <span class="font-weight-bold">CPF/CNPJ </span> não foi  preenchido.
            </div>
         `
    }
    if (verifyInputsEmpty(nameClient).length > 0) {
        event.preventDefault();
        message += `
            <div class="alert alert-danger" role="alert">
                O campo <span class="font-weight-bold"> Nome/Razão Social</span> não foi preenchido corretamente.
            </div>
         `
    }
    scrollTop()
    if (message == '') {
        spinnerLoad()
        $('.meuPopUp').css('opacity', '0.3')
        $('.meuPopUp').addClass('desabilitar');
    }
    $('.messageValidationPix').html(message);
}

function verifyName(name) {
    if (name == '') {
        return false;
    } else {
        return true;
    }

}

function verifyInputsEmpty(...inputs) {
    let verify = '';
    for (let index = 0; index < inputs.length; index++) {
        let element = inputs[index].value;
        if (element == '') {
            verify += `
            <div class="alert alert-danger" role="alert">
                O campo <span class="font-weight-bold"> ${inputs[index].nome}</span> não foi preenchido.
            </div>
         `;
        }
    }
    return verify;
}

function verifyCPF(cpf) {
    cpf = cpf.replace(/[^\d]+/g, '');

    if (cpf == '' || cpf.length != 11) return false;

    var resto;
    var soma = 0;

    if (cpf == "00000000000" || cpf == "11111111111" || cpf == "22222222222" || cpf == "33333333333" || cpf == "44444444444" || cpf == "55555555555" || cpf == "66666666666" || cpf == "77777777777" || cpf == "88888888888" || cpf == "99999999999" || cpf == "12345678909") return false;

    for (i = 1; i <= 9; i++) soma = soma + parseInt(cpf.substring(i - 1, i)) * (11 - i);
    resto = (soma * 10) % 11;

    if ((resto == 10) || (resto == 11)) resto = 0;
    if (resto != parseInt(cpf.substring(9, 10))) return false;

    soma = 0;
    for (i = 1; i <= 10; i++) soma = soma + parseInt(cpf.substring(i - 1, i)) * (12 - i);
    resto = (soma * 10) % 11;

    if ((resto == 10) || (resto == 11)) resto = 0;
    if (resto != parseInt(cpf.substring(10, 11))) return false;
    return true;
}

function verifyCNPJ(cnpj) {
    cnpj = cnpj.replace(/[^\d]+/g, '');

    if (cnpj == '' || cnpj.length != 14) return false;

    if (cnpj == "00000000000000" || cnpj == "11111111111111" || cnpj == "22222222222222" || cnpj == "33333333333333" || cnpj == "44444444444444" || cnpj == "55555555555555" || cnpj == "66666666666666" || cnpj == "77777777777777" || cnpj == "88888888888888" || cnpj == "99999999999999") return false;

    var tamanho = cnpj.length - 2
    var numeros = cnpj.substring(0, tamanho);
    var digitos = cnpj.substring(tamanho);
    var soma = 0;
    var pos = tamanho - 7;

    for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2)
            pos = 9;
    }

    var resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;

    if (resultado != digitos.charAt(0)) return false;

    tamanho = tamanho + 1;
    numeros = cnpj.substring(0, tamanho);
    soma = 0;
    pos = tamanho - 7;

    for (i = tamanho; i >= 1; i--) {
        soma += numeros.charAt(tamanho - i) * pos--;
        if (pos < 2) pos = 9;
    }

    resultado = soma % 11 < 2 ? 0 : 11 - soma % 11;

    if (resultado != digitos.charAt(1)) return false;

    return true;
}

function verifyPhone(phone_number) {
    var pattern = new RegExp(/^[1-9]{2}9?[0-9]{8}$/);
    if (pattern.test(phone_number.replace(/[^\d]+/g, ''))) {
        return true;
    } else {
        return false;
    }

}

function verifyEmail(email) {
    var pattern = new RegExp(/^([a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+(\.[a-z\d!#$%&'*+\-\/=?^_`{|}~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]+)*|"((([ \t]*\r\n)?[ \t]+)?([\x01-\x08\x0b\x0c\x0e-\x1f\x7f\x21\x23-\x5b\x5d-\x7e\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|\\[\x01-\x09\x0b\x0c\x0d-\x7f\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))*(([ \t]*\r\n)?[ \t]+)?")@(([a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\d\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.)+([a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]|[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF][a-z\d\-._~\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]*[a-z\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])\.?$/);
    return pattern.test(email);
}

function verifyZipCode(zipCode) {
    var objER = /^[0-9]{2}.[0-9]{3}-[0-9]{3}$/;

    if (zipCode.length > 0) {
        if (objER.test(zipCode))
            return true;
        else
            return false;
    } else
        return false;
}

function verifyBirthDate(birthDate) {
    var dataAtual = new Date();
    var anoAtual = dataAtual.getFullYear();
    var anoNascParts = birthDate.split('-');
    var diaNasc = anoNascParts[2];
    var mesNasc = anoNascParts[1];
    var anoNasc = anoNascParts[0];
    var idade = (anoNasc == "") ? 0 : anoAtual - anoNasc;
    var mesAtual = dataAtual.getMonth() + 1;
    //Se mes atual for menor que o nascimento, nao fez aniversario ainda;  
    if (mesAtual < mesNasc) {
        idade--;
    } else {
        //Se estiver no mes do nascimento, verificar o dia
        if (mesAtual == mesNasc) {
            if (new Date().getDate() < diaNasc) {
                //Se a data atual for menor que o dia de nascimento ele ainda nao fez aniversario
                idade--;
            }
        }
    }
    if (idade >= 18) {
        return true;

    } else {
        return false;
    }
}

function scrollTop() {
    $(".optionPaymentGerencianet,.meuPopUp").animate({ scrollTop: 0 }, 800);

}

function spinnerLoad() {
    $(".spinner1").animate({ width: "100%" }, 800);
}