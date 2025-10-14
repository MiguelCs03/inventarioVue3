
<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Restablecer contraseña</title>
	<style>
		body { background: #f4f6fb; font-family: Arial, sans-serif; margin: 0; padding: 0; }
		.container { max-width: 480px; margin: 40px auto; background: #fff; border-radius: 10px; box-shadow: 0 2px 8px #0001; padding: 32px 24px; }
		.logo { text-align: center; margin-bottom: 24px; }
		.logo img { max-width: 120px; }
		h2 { color: #2d3748; text-align: center; }
		p { color: #4a5568; font-size: 16px; }
		.btn { display: block; width: 100%; background: #1976d2; color: #fff !important; text-align: center; padding: 14px 0; border-radius: 6px; text-decoration: none; font-weight: bold; margin: 24px 0; font-size: 16px; }
		.footer { text-align: center; color: #a0aec0; font-size: 13px; margin-top: 32px; }
	</style>
</head>
<body>
	<div class="container">
		<div class="logo">
			<img src="https://www.shutterstock.com/shutterstock/photos/2586687241/display_1500/stock-photo-dark-blue-abstract-background-with-ultraviolet-neon-glow-blurry-light-lines-waves-2586687241.jpg" alt="Logo Intecruz">
		</div>
		<h2>Restablecimiento de contraseña</h2>
		<p>Hola,</p>
		<p>Recibiste este correo porque se solicitó un restablecimiento de contraseña para tu cuenta en <b>Intecruz</b>.</p>
		<a href="{{ $url ?? '#' }}" class="btn">Restablecer contraseña</a>
		<p style="font-size:14px; color:#986322;">Si no solicitaste el restablecimiento, puedes ignorar este mensaje.</p>
		<div class="footer">
			&copy; {{ date('Y') }} Intecruz. Todos los derechos reservados.
		</div>
	</div>
</body>
</html>
