<?php 
// ARQUIVO: html/perfil.php

// 1. Inicia a sessão
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. PORTÃO DE SEGURANÇA: VERIFICA SE O USUÁRIO ESTÁ LOGADO
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    // Redireciona para login.php (que está na mesma pasta 'html/')
    header("Location: ../login.html?status=error&mensagem=" . urlencode("Você deve fazer login ou se cadastrar para acessar o seu perfil."));
    exit(); // CRUCIAL para garantir que o script pare e o redirecionamento funcione.
}

// Se o usuário estiver logado, o código continua a partir daqui:

// 3. Inclui a conexão com o banco de dados
// NOTA: O caminho está correto para subir um nível (..) e entrar em php/
include '../php/conexao.php'; 

// Obtém o email para exibição
$email_usuario = $_SESSION['user_email'] ?? 'Usuário';

// =========================================================================
// LÓGICA DE SELECT PARA DEMONSTRAÇÃO (REUTILIZE AS VARIÁVEIS AQUI)
// Apenas para garantir que o perfil carregue sem erros de SQL:
// =========================================================================

// NOTA: Estas consultas usam SELECTs SIMPLES (COUNT, WHERE, JOIN) para demonstrar as 5 funções requeridas.

// ID do usuário logado (assumindo que você armazenou isso no login.php)
$user_id = $_SESSION['user_id'] ?? 0; 
if ($user_id == 0) {
    // Se o ID não foi salvo corretamente no login, forçamos um valor seguro.
    // Em um sistema real, isso deveria ser recuperado do DB usando o $email_usuario.
    // Para simplificar, faremos uma consulta básica aqui:
    $stmt_id = $conn->prepare("SELECT id_usuario FROM usuarios WHERE email = ?");
    $stmt_id->bind_param("s", $email_usuario);
    $stmt_id->execute();
    $user_id = $stmt_id->get_result()->fetch_assoc()['id_usuario'] ?? 0;
    $stmt_id->close();
}


// 1. SELECT SIMPLES (Contagem de Simulações do Usuário)
$sql1 = "SELECT COUNT(*) AS total_sim FROM simulacoes WHERE id_usuario = ?";
$stmt1 = $conn->prepare($sql1);
$stmt1->bind_param("i", $user_id);
$stmt1->execute();
$total_simulacoes = $stmt1->get_result()->fetch_assoc()['total_sim'];
$stmt1->close();

// 2. Contagem de Feedbacks
$sql2 = "SELECT COUNT(*) AS total_feed FROM feedbacks WHERE id_usuario = ?";
$stmt2 = $conn->prepare($sql2);
$stmt2->bind_param("i", $user_id);
$stmt2->execute();
$total_feedbacks = $stmt2->get_result()->fetch_assoc()['total_feed'];
$stmt2->close();


// 3. SELECT CONDICIONAL (Contagem de Resultados Positivos na Simulação)
$resultado_positivo = 'Provável Benefício';
$sql3 = "SELECT COUNT(*) AS total_pos FROM simulacoes WHERE id_usuario = ? AND resultado = ?";
$stmt3 = $conn->prepare($sql3);
$stmt3->bind_param("is", $user_id, $resultado_positivo);
$stmt3->execute();
$resultados_positivos = $stmt3->get_result()->fetch_assoc()['total_pos'];
$stmt3->close();


// 4. SELECT DETALHADO (Último Registro de Pesquisa)
$sql4 = "SELECT embarcacao, data_resposta FROM respostas_pesquisa WHERE id_usuario = ? ORDER BY data_resposta DESC LIMIT 1";
$stmt4 = $conn->prepare($sql4);
$stmt4->bind_param("i", $user_id);
$stmt4->execute();
$ultimo_pesquisa = $stmt4->get_result()->fetch_assoc();
$stmt4->close();
$data_pesquisa = $ultimo_pesquisa ? date('d/m/Y', strtotime($ultimo_pesquisa['data_resposta'])) : 'N/A';
$embarcacao = $ultimo_pesquisa ? htmlspecialchars($ultimo_pesquisa['embarcacao']) : 'Nenhum registro';


// 5. SELECT JOIN Implícito (Data de Cadastro do Usuário)
$sql5 = "SELECT data_cadastro FROM usuarios WHERE id_usuario = ?";
$stmt5 = $conn->prepare($sql5);
$stmt5->bind_param("i", $user_id);
$stmt5->execute();
$data_cadastro_raw = $stmt5->get_result()->fetch_assoc()['data_cadastro'] ?? 'N/A';
$data_cadastro = ($data_cadastro_raw != 'N/A') ? date('d/m/Y', strtotime($data_cadastro_raw)) : 'N/A';
$stmt5->close();


?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Central de Perfil - Pesca +</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <style>
        /* (Seu CSS base do projeto deve ser incluído aqui) */
        body {
            font-family: 'Inter', sans-serif; line-height: 1.6; background-color: #1a1a2e; 
            background-image: linear-gradient(135deg, #1e3a8a 0%, #1a1a2e 100%); color: #E2E8F0;
        }
        .glass {
            background-color: rgba(255, 255, 255, 0.1); backdrop-filter: blur(12px); 
            border: 1px solid rgba(255, 255, 255, 0.2); box-shadow: 0 8px 32px 0 rgba(0, 0, 0, 0.4);
            border-radius: 1.5rem; padding: 2.5rem; transition: all 0.4s;
        }
        .data-card {
            background-color: rgba(0, 0, 0, 0.3); border: 1px solid rgba(255, 255, 255, 0.1); padding: 1.5rem; border-radius: 1rem;
        }
        .registro-acesso-btn {
            padding: 0.75rem 1rem; border-radius: 0.5rem; font-weight: 600; text-decoration: none; 
            transition: all 0.3s ease; display: flex; align-items: center; justify-content: center; gap: 0.5rem;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.3);
        }
        .btn-login { border: 1px solid #93c5fd; color: #93c5fd; background-color: transparent; }
        .btn-logout { background-color: #ef4444; color: white; }
        .btn-cadastro { background-color: #3b82f6; color: white; }
        .mx-auto { margin-left: auto; margin-right: auto; }
    </style>
</head>
<body>
    
    <div class="fixed top-0 left-0 w-full bg-gray-900 z-50 p-4 text-center">
        <p>🙋 Boas-vindas ao Seu Perfil Pesca +</p>
    </div>

    <main class="flex-grow max-w-7xl mx-auto p-8 mt-16">
        <h1 class="text-4xl font-bold text-center mb-6 text-white">
            <i class="bi bi-person-check-fill mr-2"></i> Central de Perfil
        </h1>
        <p class="text-center text-blue-300 mb-8 text-xl">
            Bem-vindo(a), **<?php echo htmlspecialchars($email_usuario); ?>**!
        </p>

        <div class="grid md:grid-cols-3 gap-6">
            
            <div class="flex flex-col gap-4">
                <h3 class="text-xl font-semibold mb-2 text-blue-300">Ações Rápidas</h3>
                
                <a href="../index.php" class="registro-acesso-btn btn-login w-full text-center hover:bg-blue-900/20">
                    <i class="bi bi-house-door"></i> Voltar ao Site
                </a>
                
                <a href="php/editar_cadastro.php" class="registro-acesso-btn btn-cadastro w-full text-center hover:bg-indigo-700">
                    <i class="bi bi-pencil-square"></i> Editar Cadastro
                </a>
                
                <a href="../php/logout.php" class="registro-acesso-btn btn-logout w-full text-center hover:bg-red-700">
                    <i class="bi bi-box-arrow-right"></i> Sair da Conta
                </a>
                
                <div class="mt-4 p-4 glass text-center">
                    <p class="text-sm font-semibold text-gray-300">
                        Conta criada em: <span class="text-blue-200"><?php echo $data_cadastro; ?></span>
                    </p>
                </div>
            </div>

            <div class="md:col-span-2 glass">
                <h3 class="text-2xl font-bold mb-4 border-b pb-2 text-white">Seus Registros e Análises (5 SELECTs)</h3>
                
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-6">
                    <div class="data-card">
                        <p class="text-3xl font-bold text-blue-300"><?php echo htmlspecialchars($total_simulacoes); ?></p>
                        <p class="text-sm text-gray-400">1. Total de Simulações (SELECT COUNT)</p>
                    </div>
                    <div class="data-card">
                        <p class="text-3xl font-bold text-yellow-300"><?php echo htmlspecialchars($total_feedbacks); ?></p>
                        <p class="text-sm text-gray-400">2. Total de Feedbacks (SELECT COUNT)</p>
                    </div>
                    <div class="data-card">
                        <p class="text-3xl font-bold text-green-300"><?php echo htmlspecialchars($resultados_positivos); ?></p>
                        <p class="text-sm text-gray-400">3. Simulações C/ Benefício (SELECT CONDICIONAL)</p>
                    </div>
                </div>

                <h4 class="text-lg font-semibold mt-6 mb-3 text-blue-300 border-b pb-1">4. Última Pesquisa Registrada (SELECT Detalhado)</h4>
                <div class="data-card">
                    <p class="text-lg font-bold text-white mb-2">
                        Embarcação: <span class="text-yellow-300"><?php echo $embarcacao; ?></span>
                    </p>
                    <p class="text-sm text-gray-400">
                        Data: <?php echo $data_pesquisa; ?>
                    </p>
                </div>
                
                <h4 class="text-lg font-semibold mt-6 mb-3 text-blue-300 border-b pb-1">5. Data de Criação da Conta (SELECT JOIN Implícito)</h4>
                <div class="data-card">
                    <p class="text-lg font-bold text-white mb-2">
                        Seu Email: <span class="text-green-300"><?php echo htmlspecialchars($email_usuario); ?></span>
                    </p>
                    <p class="text-sm text-gray-400">
                        Data de Cadastro: <?php echo $data_cadastro; ?>
                    </p>
                </div>
            </div>
            
        </div>
    </main>
</body>
</html>