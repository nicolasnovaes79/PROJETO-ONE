<?php
require_once('../conexao.php');
require_once('../cors.php');

if ($mysqli->connect_error) {
    die(json_encode(["error" => "Connection failed: " . $mysqli->connect_error]));
}

header('Content-Type: application/json');

$sql = "
    SELECT 
        empresa.idEmpresa,
        empresa.razaoSocial, 
        empresa.responsavelDp, 
        empresa.formaDeFechamento, 
        imposto.idImposto, 
        imposto.nomeImposto,
        mes.mes, 
        mes.ano, 
        mes.idMes,
        entregaimposto.entregue,
        estado.nomeEstado,
        GROUP_CONCAT(empresa_formaenvio.formaEnvio SEPARATOR ', ') AS formaEnvio
    FROM 
        entregaimposto
    JOIN 
        empresa ON entregaimposto.idEmpresa = empresa.idEmpresa
    JOIN 
        imposto ON entregaimposto.idImposto = imposto.idImposto
    JOIN 
        mes ON entregaimposto.idMes = mes.idMes
    JOIN 
        estado ON empresa.idEstado = estado.idEstado
    LEFT JOIN 
        empresa_forma_envio_rel ON empresa.idEmpresa = empresa_forma_envio_rel.idEmpresa
    LEFT JOIN 
        empresa_formaenvio ON empresa_forma_envio_rel.idEnvio = empresa_formaenvio.idEnvio
    WHERE 
        imposto.departamento = 'Pessoal'
    GROUP BY 
        empresa.idEmpresa, imposto.idImposto, mes.idMes
";

$result = $mysqli->query($sql);

if ($result === false) {
    echo json_encode(["error" => "Erro na consulta SQL: " . $mysqli->error]);
    exit();
}

$data = [];

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $data[] = [
            "idEmpresa" => $row["idEmpresa"],
            "razaoSocial" => $row["razaoSocial"],
            "responsavelDp" => $row["responsavelDp"],
            "formaDeFechamento" => $row["formaDeFechamento"],
            "idImposto" => $row["idImposto"],
            "nomeImposto" => $row["nomeImposto"],
            "mes" => $row["mes"],
            "ano" => $row["ano"],
            "idMes" => $row["idMes"],
            "entregue" => $row["entregue"] ? true : false,
            "nomeEstado" => $row["nomeEstado"],
            "formaEnvio" => $row["formaEnvio"]
        ];
    }
} else {
    $data = ["message" => "Nenhum dado encontrado"];
}

$mysqli->close();

echo json_encode($data);
?>
