
<?php

date_default_timezone_set('America/Sao_Paulo');
$errors = null;
if (isset($_POST['errors']))
	$errors = $_POST['errors'];

?>

<!DOCTYPE html>
<html>
	<link rel="stylesheet" type="text/css" href="gerencianet_errors.css"/>
	<head>
		<title>Efí Error</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	</head>
	<body>
		<div class="header">
			<center>
			<div id='whmcs-icone'>
				<img src="images/logo-whmcs.png" alt="WHMCS">
			</div>
			</center>
		</div>
		<div id="division">
			<center>
			<div class="content">
				
				<div id="information">
					<img id='icone-erro' src="images/icone-erro.png" alt="erro"><h1>Atenção! Foram detectados um ou mais erros.</h1>
					<h4><b>Obs:</b> Estes erros foram retornado pela Efí.</h4>
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
					<div class="clear">
						<div class='information-box'>
							<p>Se você preencheu um campo errado, volte no formulário e altere o campo corretamente.</p>
							<a href="../../../clientarea.php?action=details" class="css_btn_class"><< Voltar no formulário</a>
						</div>
						<div class="center-divisor">
							<center><h3>ou</h3></center>
						</div>
						<div id="opcao-2" class='information-box'><p>Se suas informações estão corretas, entre em contato com o vendedor e informe os erros encontrados acima.</p></div>
					</div>
				</div>
			</div>
			</center>
		</div>
		<footer>Copyright &#169 <?php echo date('Y'); ?> Efí. All Rights Reserved.</footer>
	</body>
</html>
