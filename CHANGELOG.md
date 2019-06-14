
#v0.3.2

* Fix: Realiza verificação da versão do TLS do servidor.

#v0.3.1

* Fix:  Atualização no link do boleto gerado, agora o link encaminha para um PDF.

#v0.3.0

* Fix: Mensagens de erros para configurações erradas de juros e multa.
* Fix: Descrição mais coerente dos campos de configuração.
* Fix: Tela de erro.

#v0.2.9

* Add: Mensagens de erro mais detalhadas.

#v0.2.8

* Add: Configuração de juros e multa.

#v0.2.7

* Fix: Callback automático do WHMCS para a Gerencianet nos casos de cancelamentos de mudanças na data de vencimento da fatura.

#v0.2.6

* Add: Callback automático do WHMCS para a Gerencianet nos casos de cancelamentos de mudanças na data de vencimento da fatura.

#v0.2.5

* Fix: Aceita o numero de telefone que vem do WHMCS, mesmo que tal numero venha com uma mascara.

#v0.2.4

* Fix: Funcao #delete não estava no BD_access, causando assim, erro 500 na notificação GN

#v0.2.3

* Fix: Atualização do vencimento da fatura mensal quando existe confirmação de pagamento. 


#v0.2.2

* Add: Envio automático de e-mail do WHMCS quando o pagamento é confirmado. 

#v0.2.1

* Add: Campo para configuração do valor mínimo da fatura.
* Fix: Mensagem de erros armazenada nos logs de gateway do WHMCS: Ao invés de mensagens genéricas, algumas mensagens com explicações mais detalhadas são armazenadas nos logs do gateway. 

# v0.2.0

* Fix: Tela de erros responsiva
* Delete: Obrigatoriedade de dois campos para receber CPF e CNPJ e do campo referente à Razão Social. 
* Add: Aplicação de taxas no boleto Gerencianet referentes a vencimentos do WHMCS.


# v0.1.1

* Fix: Notifiação de erro referente ao campo "Instruções do boleto"
* Add: Aplicação de créditos do WHMCS na forma de desconto no boleto Gerencianet
* Add: Opção que permite ou bloqueia o envio de emails de cobrança por parte da Gerencianet 

# v0.1.0

* Versão Beta