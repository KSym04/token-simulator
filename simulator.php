<?php 
/* Token Simulator Variables v1.0 */
require './vendor/autoload.php';

use Codenixsv\CoinGeckoApi\CoinGeckoClient;
$client = new CoinGeckoClient();
$api_status = $client->ping();

// Supply.
$token_max_supply = 45000000000; // 45 billion tokens.
$circulating_supply = 45000000000; // 45 billion tokens.
$token_min_supply = 1000000000; // 1 billion tokens.
$total_supply = 0;

// Treasury.
$coin_data = $client->simple()->getPrice(
    'bitcoin,ethereum,cardano,axie-infinity,smooth-love-potion,stellar,ripple,binancecoin', 
    'usd'
);

$coin_treasury = array(
    'BTC' => $coin_data['bitcoin']['usd'],              // Bitcoin.
    'ETH' => $coin_data['ethereum']['usd'],             // Ethereum.
    'ADA' => $coin_data['cardano']['usd'],              // Cardano.
    'AXS' => $coin_data['axie-infinity']['usd'],        // Axie Infinity.
    'SLP' => $coin_data['smooth-love-potion']['usd'],   // Smooth Love Potion.
    'XLM' => $coin_data['stellar']['usd'],              // Stellar.
    'XRP' => $coin_data['ripple']['usd'],               // XRP.
    'BNB' => number_format( $coin_data['binancecoin']['usd'] , 8 )          // Binance Coin.
);

$coin_airdrop_holdings  = 0.20; // airdrop 20%.
$coin_airdrop_tvl       = 0.30; // airdrop 30%.

// Economy.
$transaction_fee        = 0.02; // 2%.
$buy_commission         = 0.10; // 10%.
$sell_commission        = 0.20; // 20%.

$allocations            = 0;
$allocations_buy_tax    = 0.60; // 60%

$burned_token           = 0;
$burned_buy_tax         = 0.20; // 20%

$tvl                    = 0;
$tvl_buy_tax            = 0.20; // 20%.

$token_holders          = 0;

// Build data.
$days_performance = $_GET['days'];
$current_date = date("Y-m-d");

$initial_price_value = 0.000001; // value in USD
$initial_buy         = $coin_data['binancecoin']['usd']; // price of 1 BNB
$initial_asset       = 'BNB';

// Genesis buy.
$first_buy                = $initial_buy / $initial_price_value; // someone bought with 1 BNB.
$first_buy_taxed          = $first_buy * $buy_commission; // blockchain commission on buy.
$first_coins_on_wallet    = $first_buy - $first_buy_taxed;
$circulating_supply = $circulating_supply - $first_buy; // first buy will be less from max supply.
$market_cap         = $initial_buy; // market cap since from 1 BNB

$current_price           = $market_cap / $circulating_supply;
$current_price_formatted = number_format( $current_price, 10 ); // cheap coin!!!!

// Rebalance computation.
$allocations = $first_buy_taxed * $allocations_buy_tax;

$burned_token = $first_buy_taxed * $burned_buy_tax;

$tvl = $first_buy_taxed * $tvl_buy_tax;
$tvl_in_usd = $tvl * $current_price;

$total_supply = $circulating_supply + $allocations + $burned_token + $tvl + $first_coins_on_wallet;
$token_holders++;

// Data points.
$datapoints = '[';
for ( $x = 1; $x <= $days_performance; $x++ ) {
    $generated_price = mt_rand( 1, 100 );
    $generated_date = date( "Y, m, d", strtotime( "+$x day", strtotime( $current_date ) ) );
    $datapoints .= "{ x: new Date($generated_date), y: $current_price },";
}
rtrim( $datapoints, ',' );
$datapoints .= ']';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token Simulator</title>

    <!-- main script -->
    <script>
    window.onload = function () {
        var options = {
            animationEnabled: true,
            theme: "light2",
            title:{
                text: "Token Performance Simulation for <?php echo number_format( $days_performance ); ?> days"
            },
            axisX:{
                valueFormatString: "DD MMM"
            },
            axisY: {
                title: "Price",
                suffix: "USD",
                minimum: 1
            },
            toolTip:{
                shared:true
            },  
            legend:{
                cursor:"pointer",
                verticalAlign: "bottom",
                horizontalAlign: "left",
                dockInsidePlotArea: true,
                itemclick: toogleDataSeries
            },
            data: [{
                type: "line",
                showInLegend: true,
                name: "Coin Price",
                markerType: "circle",
                xValueFormatString: "DD MMM, YYYY",
                color: "#F08080",
                yValueFormatString: "#.# USD",
                dataPoints: <?php echo $datapoints; ?>
            }]
        };

        $("#chartContainer").CanvasJSChart(options);

        function toogleDataSeries(e){
            if (typeof(e.dataSeries.visible) === "undefined" || e.dataSeries.visible) {
                e.dataSeries.visible = false;
            } else{
                e.dataSeries.visible = true;
            }
            e.chart.render();
        }

    }
    </script>

    <style>
        * { box-sizing: border-box; }
        body { font-family: Tahoma, sans-serif; }

        #chartContainer {
            height: 500px; 
            max-width: 1280px; 
            margin: 20px;
        }

        .main-board {
            display: flex;
            flex-direction: row;
            flex-wrap: wrap;
        }

        .chart {
            flex: 1 0 70%;
        }

        .info {
            flex: 1 0 30%;
            padding: 40px;
            background: #f5f5f5;
        }

        .info table td {
            vertical-align: top;
            padding-bottom: 10px;
        }

        .info label {
            display: inline-block;
            width: 200px;
            font-weight: 600;
        }

        .data-pricing {
            padding: 0 40px;
        }

        .data-pricing td {
            vertical-align: top;
            padding-right: 60px;
        }
    </style>
</head>
<body>

<div class="main-board">
    <div class="chart">
        <div id="chartContainer"></div>
    </div>

    <div class="info">
        <table>
            <tr>
                <td><label>Token Price:</label></td>
                <td><?php echo $current_price_formatted; ?> USD</td>
            </tr>
            <tr>
                <td><label>Max Supply:</label></td>
                <td><?php echo number_format( $token_max_supply ); ?></td>
            </tr>

            <tr>
                <td><label>Min Supply:</label></td>
                <td><?php echo number_format( $token_min_supply ); ?></td>
            </tr>

            <tr>
                <td><label>Market Cap:</label></td>
                <td><?php echo number_format( $market_cap, 4 ); ?> USD</td>
            </tr>
            <tr>
                <td><label>Token Holders:</label></td>
                <td><?php echo number_format( $token_holders ); ?></td>
            </tr>
            <tr>
                <td><label>Circulating Supply:</label></td>
                <td><?php echo number_format( $circulating_supply ); ?></td>
            </tr>
            <tr>
                <td><label>Burned Token:</label></td>
                <td><?php echo number_format( $burned_token ); ?></td>
            </tr>
            <tr>
                <td><label>Total Supply:</label></td>
                <td><?php echo number_format( $total_supply ); ?></td>
            </tr>
            <tr>
                <td><label>Token Value Locked:</label></td>
                <td>
                    <?php echo number_format( $tvl, 4 ); ?><br />
                    <?php echo number_format( $tvl_in_usd, 4 ); ?> USD
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="data-pricing">
    <table>
        <td>
            <h3>First Block</h3>
            <?php echo 'Worth: ' . $initial_buy . ' USD' . '<br />'; ?>
            <?php echo 'Asset: ' . $initial_asset . '<br />'; ?>
            <?php echo 'Initial Acquisition: ' . number_format( $first_buy, 2 ) . '<br />'; ?>
            <hr />
            <?php echo 'Coins: ' . number_format( $first_coins_on_wallet, 2 ) . '<br />'; ?>
            <hr />
            <?php echo 'Allocations: ' . number_format( $allocations, 2 ) . '<br />'; ?>
            <?php echo 'Burned: ' . number_format( $burned_token, 2 ) . '<br />'; ?>
            <?php echo 'TVL: ' . number_format( $tvl, 2 ) . '<br />'; ?>
        </td>
        <td>
            <h3>Latest Coin Prices</h3>
            <?php foreach( $coin_treasury as $coinkey => $coinval ) : ?>
                <div><?php printf( '%s: $%s', $coinkey, number_format( $coinval, 4 ) ); ?></div>
            <?php endforeach; ?>
        </td>
    </table>
</div>

<script src="https://canvasjs.com/assets/script/jquery-1.11.1.min.js"></script>
<script src="https://canvasjs.com/assets/script/jquery.canvasjs.min.js"></script>
</body>
</html>