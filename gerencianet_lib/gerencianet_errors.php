
<?php

$errors = null;
if (isset($_POST['errors']))
	$errors = $_POST['errors'];

?>

<!DOCTYPE html>
<html>
	<link rel="stylesheet" type="text/css" href="gerencianet_errors.css"/>
	<head>
		<title>ola</title>
	</head>
	<body>
		<div id='whmcs-icone'>
			<img src="images/logo-whmcs.png" alt="WHMCS">
		</div>

		<div id="division">
			<div class="content">
				<img id='icone-erro' src="images/icone-erro.png" alt="erro">
				<div id="information">
					<h1>Atenção! Foram detectados um ou mais erros.</h1>
					<h4><b>Obs:</b> Estes erros foram retornado pela Gerencianet.</h4>
					<div class='error-box' id="integration-errors">
						<ul>
						<?php
							foreach ($errors as $error) {
								echo '<li>' . $error . '</li>';
							}
						?>
						</ul>
					</div>
					<h2>Você tem duas opções:</h2>
					<div id="opcao-1" class='information-box'>
						<p>Se você preencheu um campo errado, volte no formulário e altere o campo corretamente.</p>
						<a href="../../../clientarea.php?action=invoices#" class="css_btn_class"><< Voltar no formulário</a>
					</div>
					<h3>ou</h3>
					<div id="opcao-2" class='information-box'><p>Se suas informações estão corretas, entre em contato com o vendedor e informe os erros encontrados acima.</p></div>
				</div>
			</div>
		</div>
		<footer>Copyright &#169 <?php echo date(Y); ?> Gerencianet. All Rights Reserved.</footer>
	</body>
</html>
