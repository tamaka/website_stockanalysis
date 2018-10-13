<?php

require_once(__DIR__ . '/data/data.php');
require_once(__DIR__ . '/StockData.php');

function h($s) {
  return htmlspecialchars($s, ENT_QUOTES, 'UTF-8');
}

$stockData = new \MyApp\StockData();

$stockCode = 2121;
$highChartsDescription = '';
$companyName = $stockData->getCompanyName($stockCode);

// カテゴリ(大)リストを取得
$categoryLargeList = $stockData->getCategoryLargeList();
// デフォルト設定
$categoryLargeDefault = $categoryLargeList['0'];

// カテゴリ(小)リストを取得
$categorySmallList = $stockData->getCategorySmallList($categoryLargeDefault);
// デフォルト設定
$categorySmallDefault = $categorySmallList['0'];

// カテゴリから銘柄リストを取得
$companyList = $stockData->getStockDataFromCategory($categorySmallDefault);

// 銘柄コードから四半期決算データを取得
$quarterResultList = $stockData->getQuearterResult($stockCode);

// 銘柄コードから通期決算データを取得
$yearResultList = $stockData->getYearResult($stockCode);

// 銘柄コードからファンダメンタルデータを取得
$fundamentalDataList = $stockData->getFundamentalData($stockCode);

//

?>
<!DOCTYPE html>
<html lang="ja">
<haed>
  <meta charset="utf-8">
  <title>stockchart</title>
  <script src="code.jquery.com/jquery-3.1.1.min.js"></script>
  <script src="js/Highstock-6.1.3/code/highstock.js"></script>
  <script src="js/Highstock-6.1.3/code/modules/exporting.js"></script>
  <script src="js/handle_highcharts.js"></script>
  <link rel="stylesheet" href="css/styles.css">
</head>
<body>
<div id="container_highcharts">
  <div id ="chart_configuration">
    <button class="auto_load" data-id="3">3</button>
    <button class="data_load" data-id="data_load">LOAD</button>
    <button class="next_load" data-id="next_load">NEXT</button>
    <form method="post">
    <input type="text" name="input_code"></input>
    </form>
  </div>
  <div id="financial_data">
    <table>
    <th>期間</th>
    <th>売上</th>
    <th>営利</th>
    <th>経常</th>
    <th>純利</th>
    <th>1株利益</th>
    <th>利益率</th>
    <th>発表日</th>
  　<?php foreach ($quarterResultList as $quarterResult) : ?>
    <?php echo '<tr>'; ?>
    <?php echo '<td>' . $quarterResult['period'] . '</td>'; ?>
    <?php echo '<td>' . $quarterResult['sales'] . '</td>'; ?>
    <?php echo '<td>' . $quarterResult['operating_profit'] . '</td>'; ?>
    <?php echo '<td>' . $quarterResult['ordinary_profit'] . '</td>'; ?>
    <?php echo '<td>' . $quarterResult['net_income'] . '</td>'; ?>
    <?php echo '<td>' . $quarterResult['benefit_per_share'] . '</td>'; ?>
    <?php echo '<td>' . $quarterResult['profit_ratio'] . '</td>'; ?>
    <?php echo '<td>' . $quarterResult['date'] . '</td>'; ?>
    <?php echo '</tr>'; ?>
    <?php endforeach; ?>
    </table>
  </div>
  <div id="stockchart_short"></div>
  <div id="stockchart_long"></div>
  <div id="stocklist_company">
   <?php foreach ($companyList as $company) : ?>
   <?php echo "<button class=\"companybutton\" data-id=\"" . $company['stock_code'] . "\">" .  $company['stock_code'] . " " . $company['stock_name'] . "</button></br>"; ?>
   <?php endforeach; ?>
  </div>
  <div id="stocklist_category_small">
    <?php foreach ($categorySmallList as $categorySmall) : ?>
    <?php echo "<button class=\"categorybutton_small\" data-id=\"" . $categorySmall . "\">" .  $categorySmall . "</button></br>"; ?>
    <?php endforeach; ?>
  </div>
  <div id="stocklist_category_large">
    <?php foreach ($categoryLargeList as $categoryLarge) : ?>
    <?php echo "<button class=\"categorybutton_large\" data-id=\"" . $categoryLarge . "\">" .  $categoryLarge . "</button></br>"; ?>
    <?php endforeach; ?>
  </div>
</div>
  <script type="text/javascript">

  $.getJSON('stockdata/2121.json', function (data) {
    var ohlc = [],
      volume = [],
      dataLength = data.length,
      groupingUnits = [[
          'week',                         // unit name
          [1]                             // allowed multiples
      ], [
          'month',
          [1, 2, 3, 4, 6]
      ]],
      i = 0;

      for(i; i < dataLength; i+=1){
        ohlc.push([
          data[i][0], // 日
          data[i][1], // 始値
          data[i][2], // 高値
          data[i][3], // 安値
          data[i][4]  // 終値
        ]);
        volume.push([
          data[i][0], // 日
          data[i][5]  // 出来高
        ]);
      }
    // create the chart
    Highcharts.stockChart('stockchart_short', {
        rangeSelector: {
            selected: 1
        },
        title: {
            text: [<?php echo "'$companyName'"?>]
        },
        xAxis: {
            type: 'datetime'
        },
        yAxis: [{
            height: '82%', // キャンドルチャートの高さ
            resize: {
                enabled: true
            }
        }, {
            top: '83%',
            height: '17%',
            offset: 0
        }],
        series: [{
            type: 'candlestick',
            name: 'AAPL Stock Price',
            data: data,
            dataGrouping: {
              units: groupingUnits
            }
          }, {
            type: 'column',
            name: 'Volume',
            data: volume,
            yAxis: 1,
            dataGrouping: {
                units: groupingUnits
          }
        }],
        plotOptions: {
            series: {
                animation: false
            }
        }
    });

    Highcharts.stockChart('stockchart_long', {

        rangeSelector: {
            selected: 6
        },
        xAxis: {
            type: 'datetime'
        },
        yAxis: [{
            height: '82%',
            resize: {
                enabled: true
            }
        }, {
            top: '83%',
            height: '17%',
            offset: 0
        }],
        series: [{
            type: 'candlestick',
            name: 'AAPL Stock Price',
            data: data,
            dataGrouping: {
              units: groupingUnits
            }
          }, {
            type: 'column',
            name: 'Volume',
            data: volume,
            yAxis: 1,
            dataGrouping: {
                units: groupingUnits
          }
        }],
        plotOptions: {
            series: {
                animation: false
            }
        }
    });

  });

		</script>
	</body>
</html>
