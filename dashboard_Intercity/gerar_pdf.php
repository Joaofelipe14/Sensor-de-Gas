<?php
require './vendor/autoload.php';

use Dompdf\Dompdf;

// Incluir os arquivos necessários
include_once './estatistica.php'; 

function normalize_value($value, $min_value, $max_value, $new_min = 0, $new_max = 10) {
   // Verificar se o valor original está dentro do intervalo
   if ($value < $min_value || $value > $max_value) {
       throw new Exception("O valor original está fora do intervalo especificado.");
   }

   // Aplicar a fórmula de normalização
   $normalized_value = ($value - $min_value) / ($max_value - $min_value) * ($new_max - $new_min) + $new_min;

   // Arredondar o valor normalizado para duas casas decimais
   $normalized_value = round($normalized_value, 2);

   return $normalized_value;
}

$dados = "<!DOCTYPE html>";
$dados .= "<html lang='pt-br'>";
$dados .= "<head>";
$dados .= "<meta charset='UTF-8'>";
$dados .= "<style>
    body {
        background: rgb(204, 204, 204);
        font-family: 'Lucida Console', Monaco, monospace;
    }
    page {
        background: white;
        box-shadow: 0 0 0.5cm rgba(0, 0, 0, 0.5);
    }
    page[size='A4'] {
        width: 21cm;
        height: 29.7cm;
    }
    page[size='A4'][layout='portrait'] {
        width: 21cm;
        height: 29.7cm;
    }
    @media print {
        body, page {
            margin: 0;
            box-shadow: 0;
        }
    }
    .header {
        padding-top: 10px;
        text-align: center;
        border: 2px solid #ddd;
    }
    table {
        border-collapse: collapse;
        width: 100%;
        font-size: 80%;
    }
    table th {
        background-color: #4caf50;
        text-align: center;
    }
    th, td {
        border: 1px solid #ddd;
        text-align: center;
    }
    tr:nth-child(even) {
        background-color: #f2f2f2;
    }
    .nrPage {
        text-align: right;
        margin-right: 10px;
    }
    .td-primaria {
        height: 50px;
    }
    .td-segundaria {
        height: 20px;
    }
</style>";
$dados .= "<title>Histórico De Variação</title>";
$dados .= "</head>";
$dados .= "<body>";
$dados .= "<page size='A4' layout='portrait'>";
$dataAtual = date('d/m/Y'); 
$dados .= "<div class='header'>
    <div class='nrPage'>$dataAtual</div>
    Sistema contra vazamento de Gás/fumaça <br>
    UFMA - SÃO LUIS - MA <br>
    <h3>Histórico De Variacão</h3>
</div>";
$dados .= "<table>";
$dados .= "<thead>
    <tr>
        <th class='td-segundaria'>Escala Sensor</th>
        <th class='td-segundaria'>Data e Hora</th>
    </tr>
</thead>";
$dados .= "<tbody>";

// URL do serviço web
$url = "http://cidadesinteligentes.lsdi.ufma.br/collector/resources/8cd968a1-9982-4fcc-882a-f59eafbccb52/data";

// Faz a requisição GET
$response = file_get_contents($url);

if ($response !== false) {
    $data = json_decode($response, true);

    foreach ($data['resources'][0]['capabilities']['environment_monitoring'] as $entry) {
        $dateTime = new DateTime($entry['date']);
        $data_Formatada = $dateTime->format('Y-m-d H:i:s');
        $entry['date'] = $data_Formatada;

        if (isset($entry['Sensor_Gas_A']) && is_numeric($entry['Sensor_Gas_A'])) {
            $entry['Sensor_Gas_A'] = normalize_value($entry['Sensor_Gas_A'], 0, 4000, 0, 10);
            $valor_gas_a = $entry['Sensor_Gas_A'];
            $array_medidas[] = $valor_gas_a;
            $dados .= "<tr>
                <td>$valor_gas_a</td>
                <td>{$entry['date']}</td>
            </tr>";
        }
    }
}

// Calcular estatísticas
if (!empty($array_medidas)) {
    $media_aritimetica_medida = number_format(array_sum($array_medidas) / count($array_medidas), 4);
    $desvio_padrao_medida = number_format(stats_standard_deviation($array_medidas), 4);
    $maior_medida = max($array_medidas);
    $menor_medida = min($array_medidas);
    $Amplitude_medida = ($maior_medida - $menor_medida);
} else {
    $media_aritimetica_medida = '-';
    $desvio_padrao_medida = '-';
    $maior_medida = '-';
    $menor_medida = '-';
    $Amplitude_medida = '-';
}

$dados .= "</tbody>";
$dados .= "</table>";

$dados .= "<div class='header'>
    <h3>Estimativas amostrais</h3>
</div>";

$dados .= "<table>";
$dados .= "<tr>
    <th class='td-segundaria'>&nbsp;</th>
    <th class='td-segundaria'>Valor Max</th>
    <th class='td-segundaria'>Valor Min</th>
    <th class='td-segundaria'>Amplitude</th>
    <th class='td-segundaria'>Média Aritmética</th>
    <th class='td-segundaria'>Desvio Padrão</th>
</tr>";

$dados .= "<tr>
    <td class='td-primaria'>medida</td>
    <td>{$maior_medida}</td>
    <td>{$menor_medida}</td>
    <td>{$Amplitude_medida}</td>
    <td>{$media_aritimetica_medida}</td>
    <td>{$desvio_padrao_medida}</td>
</tr>";

$dados .= "</table>";
$dados .= "</page>";
$dados .= "</body>";
$dados .= "</html>";

// Instanciar Dompdf
$dompdf = new Dompdf(['enable_remote' => true]);
$dompdf->loadHtml($dados);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();
$dompdf->stream();

?>
