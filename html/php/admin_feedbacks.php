<?php
// Define o nome do arquivo, que deve ser 'admin_feedbacks.php'

// Inclui a conexão para acessar a sessão
include 'conexao.php';

// VERIFICA SE O USUÁRIO ESTÁ LOGADO
if (!isset($_SESSION['logado']) || $_SESSION['logado'] !== true) {
    header("Location: ../login.html?status=error&mensagem=" . urlencode("Acesso restrito. Por favor, faça login."));
    exit();
}

// E-mail do usuário para exibir na barra
$email_usuario = $_SESSION['user_email'] ?? 'Usuário Logado';

// 1. CONFIGURAÇÕES DO BANCO DE DADOS
$servername = "localhost";
$username   = "root";
$password   = "";
$dbname     = "site_defeso";

// 2. CONEXÃO COM O BANCO DE DADOS
$conn = new mysqli($servername, $username, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die("Falha na conexão com o banco de dados: " . $conn->connect_error);
}

$mensagem_status = '';

// 3. LÓGICA DE DELEÇÃO (DELETE)
if (isset($_GET['delete_id'])) {
    $id_para_deletar = $conn->real_escape_string($_GET['delete_id']);
    
    $sql_delete = "DELETE FROM feedbacks WHERE id = ?";
    $stmt_delete = $conn->prepare($sql_delete);
    $stmt_delete->bind_param("i", $id_para_deletar);

    if ($stmt_delete->execute()) {
        $mensagem_status = "Feedback ID " . $id_para_deletar . " deletado com sucesso!";
        header("Location: admin_feedbacks.php?status=sucesso&msg=" . urlencode($mensagem_status));
        exit();
    } else {
        $mensagem_status = "Erro ao deletar o feedback: " . $stmt_delete->error;
        header("Location: admin_feedbacks.php?status=erro&msg=" . urlencode($mensagem_status));
        exit();
    }
    $stmt_delete->close();
}

// 4. LÓGICA DE ATUALIZAÇÃO (UPDATE)
if (isset($_POST['update_id'])) {
    $id_para_editar     = $conn->real_escape_string($_POST['update_id']);
    $nome               = $conn->real_escape_string(trim($_POST['nome']));
    $email              = $conn->real_escape_string(trim($_POST['email']));
    $mensagem_feedback  = $conn->real_escape_string(trim($_POST['mensagem']));

    $sql_update = "UPDATE feedbacks SET nome=?, email=?, mensagem=? WHERE id=?";
    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param("sssi", $nome, $email, $mensagem_feedback, $id_para_editar);

    if ($stmt_update->execute()) {
        $mensagem_status = "Feedback ID " . $id_para_editar . " atualizado com sucesso!";
        header("Location: admin_feedbacks.php?status=sucesso&msg=" . urlencode($mensagem_status));
        exit();
    } else {
        $mensagem_status = "Erro ao atualizar o feedback: " . $stmt_update->error;
        header("Location: admin_feedbacks.php?status=erro&msg=" . urlencode($mensagem_status));
        exit();
    }
    $stmt_update->close();
}

// Variável para armazenar o registro a ser editado
$registro_edicao = null;
if (isset($_GET['edit_id'])) {
    $id_para_editar = $conn->real_escape_string($_GET['edit_id']);
    $sql_edit = "SELECT * FROM feedbacks WHERE id = ?";
    $stmt_edit = $conn->prepare($sql_edit);
    $stmt_edit->bind_param("i", $id_para_editar);
    $stmt_edit->execute();
    $result_edit = $stmt_edit->get_result();
    if ($result_edit->num_rows > 0) {
        $registro_edicao = $result_edit->fetch_assoc();
    }
    $stmt_edit->close();
}

// 5. LÓGICA DE BUSCA (SELECT)
$sql_select = "SELECT id, nome, email, mensagem, data_envio FROM feedbacks ORDER BY data_envio DESC";
$result = $conn->query($sql_select);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Feedbacks - Defeso</title>
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
        .delete-btn { background-color: #EF4444; color: white; padding: 6px 12px; border-radius: 6px; font-weight: 600; transition: background-color 0.3s; text-decoration: none; margin-right: 5px; }
        .delete-btn:hover { background-color: #DC2626; }
        .edit-btn { background-color: #3B82F6; color: white; padding: 6px 12px; border-radius: 6px; font-weight: 600; transition: background-color 0.3s; text-decoration: none; }
        .edit-btn:hover { background-color: #2563EB; }
        .success-msg { background-color: #10B981; color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600; }
        .error-msg { background-color: #EF4444; color: white; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; font-weight: 600; }
        .form-edit-container { background-color: #F0F9FF; padding: 2rem; border-radius: 1rem; margin-bottom: 2rem; box-shadow: 0 4px 10px rgba(0,0,0,0.05); }
        .form-edit-container label { display: block; font-weight: 600; margin-top: 1rem; margin-bottom: 0.25rem; }
        .form-edit-container input[type="text"], 
        .form-edit-container input[type="email"], 
        .form-edit-container textarea { width: 100%; padding: 0.75rem; border: 1px solid #CBD5E1; border-radius: 0.5rem; }
        .form-edit-container button[type="submit"] { background: #10B981; color: white; padding: 0.8rem 1.5rem; border: none; border-radius: 0.75rem; cursor: pointer; font-weight: 600; margin-top: 1.5rem; transition: background-color 0.3s; }
        .form-edit-container button[type="submit"]:hover { background: #059669; }
        .cancel-btn { background-color: #94A3B8; margin-left: 10px; }
        .cancel-btn:hover { background-color: #64748B; }
        .mensagem-preview { max-height: 50px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }

        /* Itens adicionados para igualar ao admin_simulacoes */
        .userbar { display: flex; gap: .75rem; align-items: center; justify-content: center; margin-top: .5rem; }
        .userbar .pill { background: #F1F5F9; border: 1px solid #E2E8F0; color: #475569; padding: .35rem .75rem; border-radius: 9999px; font-size: .875rem; }
    </style>
</head>
<body>

    <div class="container-admin">
        <!-- Título centralizado (padrão do admin_simulacoes) -->
        <h2 class="text-3xl font-bold text-center mb-2 text-gray-800">Área Administrativa - Feedbacks</h2>

        <!-- Barra de usuário centralizada (igual ao admin_simulacoes) -->
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

        <a href="../contato.html" class="text-blue-600 hover:text-blue-800 font-medium mb-6 inline-flex items-center">
            <i class="bi bi-arrow-left mr-2"></i> Voltar para Contato
        </a>

        <?php if ($registro_edicao): ?>
        <div class="form-edit-container">
            <h3 class="text-2xl font-semibold mb-4 text-gray-700">Editar Feedback ID: <?php echo htmlspecialchars($registro_edicao['id']); ?></h3>
            <form action="admin_feedbacks.php" method="POST">
                <input type="hidden" name="update_id" value="<?php echo htmlspecialchars($registro_edicao['id']); ?>">
                
                <label for="nome">Nome:</label>
                <input type="text" name="nome" id="nome" value="<?php echo htmlspecialchars($registro_edicao['nome']); ?>" required>

                <label for="email">E-mail:</label>
                <input type="email" name="email" id="email" value="<?php echo htmlspecialchars($registro_edicao['email']); ?>" required>

                <label for="mensagem">Mensagem:</label>
                <textarea name="mensagem" id="mensagem" rows="5" required><?php echo htmlspecialchars($registro_edicao['mensagem']); ?></textarea>

                <button type="submit">Salvar Alterações (UPDATE)</button>
                <a href="admin_feedbacks.php" class="cancel-btn inline-block px-8 py-3 text-white font-semibold rounded-lg text-center mt-4">Cancelar</a>
            </form>
        </div>
        <?php endif; ?>

        <h3 class="text-2xl font-semibold mt-6 mb-4 border-b pb-2 text-gray-700">Registros de Feedback (Busca - SELECT)</h3>

        <?php if ($result && $result->num_rows > 0): ?>
            <div class="overflow-x-auto">
                <table class="table-custom shadow-lg">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nome</th>
                            <th>E-mail</th>
                            <th>Mensagem</th>
                            <th>Data Envio</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['id']); ?></td>
                            <td><?php echo htmlspecialchars($row['nome']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td title="<?php echo htmlspecialchars($row['mensagem']); ?>">
                                <div class="mensagem-preview"><?php echo htmlspecialchars($row['mensagem']); ?></div>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($row['data_envio'])); ?></td>
                            <td>
                                <a href="admin_feedbacks.php?edit_id=<?php echo $row['id']; ?>" class="edit-btn">Editar</a>
                                <a href="admin_feedbacks.php?delete_id=<?php echo $row['id']; ?>" 
                                   class="delete-btn" 
                                   onclick="return confirm('Tem certeza que deseja deletar o feedback de <?php echo htmlspecialchars($row['nome']); ?>?');">
                                    Deletar
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <p class="text-lg text-center py-8 bg-gray-50 border border-gray-200 rounded-lg">Nenhum feedback encontrado no banco de dados.</p>
        <?php endif; ?>
    </div>

</body>
</html>

<?php
$conn->close();
?>
