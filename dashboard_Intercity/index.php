<?php
$url = "http://cidadesinteligentes.lsdi.ufma.br/collector/resources/8cd968a1-9982-4fcc-882a-f59eafbccb52/data";

// Faz a requisição GET
$response = file_get_contents($url);

if ($response === FALSE) {
    die("Falha na requisição GET");
}

$data = json_decode($response, true);
$array_temp = array();
$array_gas_a = array();
$medida_aceitavel = 0;
$medida_alerta = 0;
$medida_critica= 0;
$original_min = 0;
$original_max = 4000;

foreach ($data['resources'][0]['capabilities']['environment_monitoring'] as $entry) {
    $uuid = $data['resources'][0]['uuid'];
    $dateTime = new DateTime($entry['date']);
    $data_Formatada = $dateTime->format('Y-m-d H:i:s');
    $entry['date'] = $data_Formatada;

    if (isset($entry['Sensor_Gas_A']) && is_numeric($entry['Sensor_Gas_A'])) {
        $entry['Sensor_Gas_A'] = normalize_value($entry['Sensor_Gas_A'], $original_min, $original_max);
        $valor_gas_a = $entry['Sensor_Gas_A'];
        array_push($array_temp, $entry);
        array_push($array_gas_a, $valor_gas_a);
        if ($valor_gas_a <= 3) {
            $medida_aceitavel++;
        } elseif ($valor_gas_a > 3 && $valor_gas_a <= 6) {
            $medida_alerta++;
        } elseif ($valor_gas_a > 6) {
            $medida_critica++;
        }
    }
}

$media_aritmetica_Sensor_A = round(array_sum($array_gas_a) / count($array_gas_a));
$ultimo_elemento = end($array_gas_a);

function normalize_value($value, $min_value, $max_value, $new_min = 0, $new_max = 10) {
    if ($value < $min_value || $value > $max_value) {
        throw new Exception("O valor original está fora do intervalo especificado.");
    }

    // Aplicar a fórmula de normalização
    $normalized_value = ($value - $min_value) / ($max_value - $min_value) * ($new_max - $new_min) + $new_min;
    $normalized_value = round($normalized_value, 2);

    return $normalized_value;
}

?> 
<html>
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@700&display=swap" rel="stylesheet">

        <!--==================== UNICONS ====================-->
        <link rel="stylesheet" href="https://unicons.iconscout.com/release/v4.0.0/css/line.css">


        <!--==================== SWIPER CSS ====================-->
        <link rel="stylesheet" href="assets/css/swiper-bundle.min.css">

        <!--==================== CSS ====================-->
        <link rel="stylesheet" href="assets/css/styles.css">
        <style>
            li{
                font-weight: bold;
            }
        </style>
            <title>WebTemp - Monitor de Volume(m³)</title>
            <!-- Google Charts Loader -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    
    <!-- JavaScript for Google Charts -->
    <script type="text/javascript">
        google.charts.load('current', {'packages':['corechart', 'bar', 'gauge']});
        google.charts.setOnLoadCallback(drawCharts);

        function drawCharts() {
            drawChart();
            drawChart2();
            drawChart3();
            drawChart4();
        }

        function drawChart() {
            var data = google.visualization.arrayToDataTable([
                ['horas', 'Sensor Gás'],
                <?php foreach ($array_temp as $entry) : ?>
                    ['<?php echo date('H:i d/m', strtotime($entry['date'])) ?>', <?php echo $entry['Sensor_Gas_A'] ?>],
                <?php endforeach; ?>
            ]);

            var options = {
                title: 'Histórico Amostras',
                curveType: 'function',
                legend: { position: 'bottom' }
            };

            var chart = new google.visualization.LineChart(document.getElementById('curve_chart'));
            chart.draw(data, options);
        }

        function drawChart2() {
            var data = google.visualization.arrayToDataTable([
                ['Recorrência', 'Volume (m³)'],
                ['Aceitável', <?php echo $medida_aceitavel ?>],
                ['Crítica', <?php echo $medida_critica ?>],
                ['Atenção', <?php echo $medida_alerta ?>]
            ]);

            var options = {
                pieHole: 0.5,
                pieSliceTextStyle: { color: 'black' },
                title: 'Tendência de Risco',
                legend: 'block'
            };

            var chart = new google.visualization.PieChart(document.getElementById('donut_single'));
            chart.draw(data, options);
        }

        function drawChart3() {
            var data = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['m³', <?php echo $ultimo_elemento;?>]
            ]);

            var options = {
                width: 600, height: 150, max: 10,
                greenFrom: 0, greenTo: 2,
                yellowFrom: 2, yellowTo: 5,
                redFrom: 5, redTo: 10,
                minorTicks: 5
            };

            var chart = new google.visualization.Gauge(document.getElementById('chart_div'));
            chart.draw(data, options);
        }

        function drawChart4() {
            var data = google.visualization.arrayToDataTable([
                ['Label', 'Value'],
                ['m³', <?php echo $media_aritmetica_Sensor_A ?>]
            ]);

            var options = {
                width: 600, height: 150, max: 10,
                greenFrom: 0, greenTo: 2,
                yellowFrom: 2, yellowTo: 5,
                redFrom: 5, redTo: 10,
                minorTicks: 5
            };

            var chart = new google.visualization.Gauge(document.getElementById('chart_div2'));
            chart.draw(data, options);
        }
    </script>
    </head>
    <body>
        <header class="header" id="header">
            <nav class="nav container">
                <!-- <div><a href="#" class="nav__logo" >Carlos  Cajado</a> -->
                </div>
                <div class="nav__menu" id="nav-menu">
                    <ul class="nav__list grid">
                        <li class="nav__item">
                            <a href="#dashboard" class="nav__link active-link">
                                <i class="uil uil-diamond nav__icon"></i>Dashboard
                            </a>
                        </li>
                        <li class="nav__item">
                            <a href="#grafico" class="nav__link">
                                <i class="uil uil-rocket nav__icon"></i>Gáficos
                            </a>
                        </li>
                        <li class="nav__item">
                            <a href="#relatorio" class="nav__link">
                                <i class="uil uil-octagon nav__icon"></i>Relatórios
                            </a>
                        </li>
                        <li class="nav__item">
                            <a href="#ajustes" class="nav__link">
                                <i class="uil uil-bright nav__icon"></i>Ajustes
                            </a>
                        </li>
                    </ul>
                    <i class="uil uil-times nav__close" id="nav-close"></i>
                </div>
                <div class="nav_btns">
                    <div class="nav__toggle" id="nav-toggle">
                        <i class="uil uil-apps"></i>

                    </div>
                </div>
                <i class="uil uil-moon change-theme" id="theme-button"></i>
            </nav>

        </header>
        <?php echo("<meta http-equiv='refresh' content='60'>"); ?>
            <main class="main">
        <section class="about section" id="dashboard">
            <h2 class="section__title">Controle de Segurança </h2>
            <span class="section__subtitle">Sistema contra vazamento de Gás/fumaça</span>
            <div class="about__container container grid">
                    <div  style="width: 160px;">
                        <div>
                            <h3 >Medição Atual</h3>
                        </div>
                        <div id="chart_div" style="width: 400px; height: 140px;"></div>
                        <br>
                        <div>
                            <h3 >Média Leituras</h3>
                        </div>
                        <div id="chart_div2" style="width: 400px; height: 140px;"></div>

                    </div>
                <div class="about__data">
                <p class="about__description">

                    A detecção rápida de vazamentos assegura que quaisquer emissões de gás ou fumaça sejam prontamente
                    identificadas e mitigadas, minimizando o risco de explosões, incêndios e intoxicações. Ademais,
                    é vital para evitar interrupções no processo produtivo, reduzir perdas de material, e garantir a conformidade com normas de segurança e ambientais.
                </p>
                <p class="about__description">
                Atualizações Automática por minuto.<br> Em caso de resultados na faixa de alerta, Informar a equipe Resposável!!.                       
                </p>
                    <!-- <div class="about__buttons">
                        <form>
                        <input type="button" value="Precisa de Ajuda ?" onClick="history.go(0)" class="button button--flex" style=" background-color:brown;">
                        </form>
                    </div> -->
                </div>
            </div>
            <div id='clock' class="section__title"></div>
            <div class="about__buttons">
                    <form>
                        <input type="button" id="setTime" value="Atualização Manual" onClick="history.go(0)" class=" button button--flex">
                    </form> 
            </div>
        </section>
        <section class="skills section" id="grafico">
            <h2 class="section__title">Gáficos</h2>
            <span class="section__subtitle">Diferenciais</span>
            <div class="skills__container ">
                <div><div class="skills__content skills__close">
                    <div class="skills__header">
                        <i class="uil uil-emoji skills__icon"></i>

                        <div>
                            <h1 class="skills__title grid">Histórico</h1>
                        </div>
                        <i class="uil uil-angle-down skills__arrow"></i>
                    </div>

                    <div class="grid" style="justify-content: center; align-items: center;">
                        <div id="curve_chart" style="width: 1200px; height: 500px"></div>
                    </div>
                </div>
            </div>
            <div>
                <div>
                    <h1 class="skills__title grid">Tendências</h1>
                </div>
                <div class="grid" style="justify-content: center; align-items: center;">
                    <div id="donut_single" style="width: 375px; height: 400px;"></div>
                </div>
            </div>
        </section>
        <section class="about section" id="relatorio">
            <h2 class="section__title">Relatório Estatísticos</h2>
            <span class="section__subtitle">Análise descritiva dos dados</span>
                <div class="about__container container grid">
                    <div  style="width:800px;">
                    <div class="about__buttons">

                    </div>
                        <form action="gerar_pdf.php">
                            <input type="submit" value="Gerar PDF"  class=" button button--flex"/>
                        </form>
                    </div>
                    <div class="about__data">
                            <dl>
                                <p class="about__description">
                                Listagem de todas as medições de volume, feitas pelos sensores ordenadas pela data e horário da aferição. 
                                Ademais,é fornecidos:
                                </p>
                                <li>
                                Valor Máximo
                                </li>
                                <li>
                                Valor Mínimo
                                </li>
                                <li>
                                Amplitude entre os valores
                                </li>
                                <li>
                                Média Aritimética
                                </li>
                                <li>
                                Desvio Padrão
                                </li>
                            </dl>                  
                    </div>
                    
                </div>
                
        </section>
            <section class="about section" id="ajustes">
            </section>
    </main>
    <!--==================== SCROLL TOP ====================-->
    <a href="#" class="scrollup" id="scroll-up">
        <i class="uil uil uil-arrow-circle-up scrollup__icon"></i>
    </a>

    <!--==================== SWIPER JS ====================-->
    <script src="assets/js/swiper-bundle.min.js"></script>

    <!--==================== MAIN JS ====================-->
    <script src="assets/js/main.js"></script>
    <script src="assets/js/relogio.js"></script>
    </body>
</html>