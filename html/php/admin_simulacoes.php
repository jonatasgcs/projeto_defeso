<?php
// Define o nome do arquivo, que deve ser 'admin_simulacoes.php'

// 1. INCLUI A CONEXÃO (que inicia a sessão)
include 'conexao.php';

// 2. VERIFICA SE O USUÁRIO ESTÁ LOGADO (PORTÃO DE SEGURANÇA)
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: ../login.html?status=error&mensagem=" . urlencode("Acesso restrito. Por favor, faça login."));
    exit();
}

// Obtém o email para exibir no painel (opcional)
$email_usuario = $_SESSION['user_email'] ?? 'Usuário Logado';

$mensagem_status = '';

// 3. LÓGICA DE DELEÇÃO (DELETE)
if (isset($_GET['delete_id'])) {
    // $conn deve vir do conexao.php
    $id_para_deletar = $conn->real_escape_string($_GET['delete_id']);

    $sql_delete = "DELETE FROM simulacoes WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $id_para_deletar);

    if ($stmt_delete->execute()) {
        $mensagem_status = "Registro ID " . $id_para_deletar . " deletado com sucesso!";
        header("Location: admin_simulacoes.php?status=sucesso&msg=" . urlencode($mensagem_status));
        exit();
    } else {
        $mensagem_status = "Erro ao deletar o registro: " . $stmt_delete->error;
        header("Location: admin_simulacoes.php?status=erro&msg=" . urlencode($mensagem_status));
        exit();
    }
    $stmt_delete->close();
}

// 4. LÓGICA DE BUSCA (SELECT)
$sql_select = "SELECT id, nome, email, resultado, data_simulacao FROM simulacoes ORDER BY data_simulacao DESC";
$result = $conn->query($sql_select);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Simulações - Defeso</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #F8FAFC; color: #334155; }
        .container-admin { max-width: 1200px; margin: 2rem auto; padding: 2.5rem; background-color: #FFFFFF; border-radius: 1.25rem; box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08); }
        .table-custom { width: 100%; border-collapse: separate; border-spacing: 0; }
        .table-custom th, .table-custom td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #E2E8F0; }
        .table-custom th { background-color: #EBF8FF; color: #1E293B; font-weight: 700; border-top: 2px solid #3B82F6; }
        .table-custom tr:last-child td { border-bottom: none; }
        .delete-btn { background-color: #EF4444; color: white; padding: 6px 12px; border-radius: 6px; font-weight: 600; transition: background-color 0.3s; text-decoration: none; }
        .delete-btn:hover { background-color: #DC2626; }
        .success-msg { background-color: #10B981; color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600; }
        .error-msg { background-color: #EF4444; color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600; }
        .userbar { display: flex; gap: .75rem; align-items: center; justify-content: center; margin-top: .5rem; }
        .userbar .pill { background: #F1F5F9; border: 1px solid #E2E8F0; color: #475569; padding: .35rem .75rem; border-radius: 9999px; font-size: .875rem; }
    </style>
</head>
<body>

    <div class="container-admin">
        <!-- Título centralizado (igual ao admin_pesquisa) -->
        <h2 class="text-3xl font-bold text-center mb-2 text-gray-800">Área Administrativa - Simulações</h2>
        <!-- Barra de usuário enxuta, centralizada -->
        <div class="userbar">
            <span class="pill"><i class="bi bi-person-circle mr-1"></i> <?php echo htmlspecialchars($email_usuario); ?></span>
            <a href="logout.php" class="delete-btn inline-flex items-center">
                <i class="bi bi-box-arrow-right mr-1"></i> Sair
            </a>
        </div>

        <?php if (isset($_GET['status']) && isset($_GET['msg'])): ?>
            <div class="<?php echo $_GET['status'] == 'sucesso' ? 'success-msg' : 'error-msg'; ?>">
                <?php echo htmlspecialchars($_GET['msg']); ?>
            </div>
        <?php endif; ?>

        <a href="../direitos.html" class="text-blue-600 hover:text-blue-800 font-medium mb-6 inline-flex items-center">
            <i class="bi bi-arrow-left mr-2"></i> Voltar para Simulação
        </a>

        <h3 class="text-2xl font-semibold mt-6 mb-4 border-b pb-2 text-gray-700">Registros de Simulações (Busca)</h3>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="overflow-x-auto">
                <table class="table-custom shadow-lg">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Resultado</th>
                            <th>Data Simulação</th>
                            <th>Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['nome']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <span class="px-2 py-1 rounded-full text-xs font-semibold 
                                    <?php echo $row['resultado'] == 'Provável Benefício' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                    <?php echo htmlspecialchars($row['resultado']); ?>
                                </span>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['data_simulacao'])); ?></td>
                            <td>
                                <a href="admin_simulacoes.php?delete_id=<?php echo $row['id']; ?>" 
                                   class="delete-btn"
                                   onclick="return confirm('Tem certeza que deseja deletar este registro ID <?php echo $row['id']; ?>?');">
                                    Deletar
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-lg text-center py-8 bg-gray-50 border border-gray-200 rounded-lg">Nenhuma simulação encontrada no banco de dados.</p>
        <?php endif; ?>
    </div>

</body>
</html>

<?php
$conn->close();
?>
