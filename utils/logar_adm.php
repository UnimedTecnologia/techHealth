<?php
session_start();
			

if (!empty($_POST['usuario'])) {

	$usuario_adm = $_POST['usuario'];
	$senha_adm = $_POST['senha'];

	if ($usuario_adm != 123 && $usuario_adm != 12345) {

		include "mysql_conect.php";
		include "../adLDAP/src/adLDAP.php";

		try {
			$adldap = new adLDAP();
		}
		catch (adLDAPException $e) {
			$_SESSION['error'] = "Erro ao conectar ao servidor.";
			header("Location: ../index.php");
			exit();
			// echo $e; 
			// exit();   
		}
			

		if ($adldap->authenticate($usuario_adm,$senha_adm)){

			$sql = mysqli_query($conexao,"SELECT * FROM administrador WHERE USUARIO = '$usuario_adm'") or die ("Erro na consulta do usuário");
			while($dados=mysqli_fetch_assoc($sql)) { 

			$_SESSION['user_logado_adm'] = $dados['PERFIL'];
			$_SESSION['user_name'] = $usuario_adm;
			$_SESSION['user_id'] = $dados['ID'];

				// if ($_SESSION['user_logado_adm'] == 100) { header("Location: index.php"); }
				// if ($_SESSION['user_logado_adm'] == 200) { header("Location: index.php"); }
				// if ($_SESSION['user_logado_adm'] == 2) { header("Location: cardapio.php"); }
				// if ($_SESSION['user_logado_adm'] == 3) { header("Location: index.php"); }
					
			}
			header("Location: ../dashboard.php");
		}
	}else { //* Validação fictícia 

		if ($usuario_adm === '123' && $senha_adm === '123') {//! EXEMPLO 1
			$_SESSION['user_id'] = 3; // Exemplo de ID do usuário
			$_SESSION['user_name'] = "Pedro Rodrigues"; // nome usuario
			header("Location: ../dashboard.php");
			exit();
		} else if ($usuario_adm === '12345' && $senha_adm === '123'){ //! EXEMPLO 2
			$_SESSION['user_id'] = 6; // Exemplo de ID do usuário
			$_SESSION['user_name'] = "Gabriel Freitas"; // nome usuario
			header("Location: ../dashboard.php");
			exit();
			
		}else {
			// Login falhou
			$_SESSION['error'] = "Credenciais inválidas.";
			header("Location: ../index.php");
			exit();
			
		}


	}
}

		$_SESSION['error'] = "Credenciais inválidas.";
		header("Location: ../index.php");
		exit();
	// echo "<script>alert('Usuario ou senha incorreto !');</script>";
	// echo "<script>history.go(-1)</script>";
?>
