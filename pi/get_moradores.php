<?php
require_once 'verifica_login.php';
header('Content-Type: application/json');

$usuario = getUsuarioLogado();

$host = 'localhost';
$dbname = 'banco_pi';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // ==== BUSCAR MORADORES COM ÚLTIMA LEITURA ====
    $sqlMoradores = "
        SELECT 
            r.id_residencia,
            r.numero_residencia,
            c.nome_condominio,
            c.id_condominio,
            COALESCE(leit.valor_kwh, 0) as consumo_kwh,
            leit.data_coleta
        FROM residencia r
        INNER JOIN condominio c ON r.id_condominio = c.id_condominio
        LEFT JOIN (
            SELECT 
                l1.id_residencia,
                l1.valor_kwh,
                l1.data_coleta
            FROM leitura l1
            INNER JOIN (
                SELECT id_residencia, MAX(data_coleta) as max_data
                FROM leitura
                GROUP BY id_residencia
            ) l2 ON l1.id_residencia = l2.id_residencia 
                AND l1.data_coleta = l2.max_data
        ) leit ON r.id_residencia = leit.id_residencia
        WHERE c.id_administrador = :id_usuario
        AND r.ativa = 1
       ORDER BY c.nome_condominio, CAST(r.numero_residencia AS UNSIGNED)
    ";
    
    $stmtMoradores = $pdo->prepare($sqlMoradores);
    $stmtMoradores->execute(['id_usuario' => $usuario['id']]);
    $moradores = $stmtMoradores->fetchAll(PDO::FETCH_ASSOC);
    
    // ==== BUSCAR DADOS DA USINA ====
    $sqlUsina = "
        SELECT 
            u.capacidade_geracao_kwh,
            c.id_condominio,
            COUNT(r.id_residencia) as total_residencias
        FROM usina u
        INNER JOIN condominio c ON u.id_condominio = c.id_condominio
        INNER JOIN residencia r ON c.id_condominio = r.id_condominio
        WHERE c.id_administrador = :id_usuario
        AND u.ativa = 1 
        AND r.ativa = 1
        GROUP BY u.capacidade_geracao_kwh, c.id_condominio
    ";
    
    $stmtUsina = $pdo->prepare($sqlUsina);
    $stmtUsina->execute(['id_usuario' => $usuario['id']]);
    $infoUsina = $stmtUsina->fetch(PDO::FETCH_ASSOC);
    
    // ==== CALCULAR DISTRIBUIÇÃO DE ENERGIA ====
    $energiaPorResidencia = 0;
    $energiaTotal = 0;
    $consumoTotal = 0;
    $creditosTotal = 0;
    
    if ($infoUsina && $infoUsina['total_residencias'] > 0) {
        $energiaTotal = floatval($infoUsina['capacidade_geracao_kwh']);
        $energiaPorResidencia = $energiaTotal / $infoUsina['total_residencias'];
        
        // Processar cada morador
        foreach ($moradores as &$morador) {
            $consumo = floatval($morador['consumo_kwh']);
            $energiaRecebida = $energiaPorResidencia;
            $saldo = $energiaRecebida - $consumo;
            
            // Adicionar campos calculados
            $morador['energia_recebida_kwh'] = $energiaRecebida;
            $morador['saldo_kwh'] = $saldo;
            $morador['status'] = $saldo >= 0 ? 'CREDITO' : 'DEBITO';
            
            // Somar totais
            $consumoTotal += $consumo;
            if ($saldo > 0) {
                $creditosTotal += $saldo;
            }
        }
        unset($morador); // Quebrar referência
        
    }
    
    // ==== MONTAR DADOS GERAIS ====
    $dadosGerais = [
        'energia_gerada' => $energiaTotal,
        'consumo_total' => $consumoTotal,
        'energia_recebida_total' => $energiaTotal,
        'saldo_total' => $energiaTotal - $consumoTotal,
        'creditos' => $creditosTotal
    ];
    
    // Resposta JSON
    echo json_encode([
        'success' => true,
        'moradores' => $moradores,
        'dadosGerais' => $dadosGerais,
        'info' => [
            'total_moradores' => count($moradores),
            'energia_por_residencia' => $energiaPorResidencia,
            'total_residencias' => $infoUsina ? $infoUsina['total_residencias'] : 0
        ]
    ], JSON_PRETTY_PRINT);
    
} catch (PDOException $e) {
    echo json_encode([
        'success' => false,
        'error' => 'Erro ao buscar dados: ' . $e->getMessage()
    ]);
}
?>