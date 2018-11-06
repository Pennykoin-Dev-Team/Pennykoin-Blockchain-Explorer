<!-- // pools stats by MainCoins @ http://krb.mypool.name -->

<style>
    #pools_rows > tr > td{
        vertical-align: middle;
        font-family: 'Inconsolata', monospace;
        text-align: center;
    }

    #pools_header > tr > th:first-child,
    #pools_rows > tr > td:first-child {
        text-align: left;
    }

    #pools_header > tr > th:last-child,
    #pools_rows > tr > td:last-child {
        text-align: left;
    }
</style>

<div class="container">
    <div class="row">
        <div class="col-lg-12">
        <div class="blocksStatHolder">
            <span><i class="fa fa-tachometer"></i> Network hashrate*: <span id="totalPoolsHashrate"></span></span>
        </div>
    </div>
</div>

<br />

<div class="table-responsive">
    <table id="network-hash" class="table table-hover">
        <thead>
            <tr>
                <th><span id="symbol"></span> Pools</th>
                <th><i class="fa fa-bars"></i> Height</th>
                <th><i class="fa fa-tachometer"></i> Hashrate</th>
                <th><i class="fa fa-group"></i> Miners</th>
                <th><i class="fa fa-money"></i> Total Fee</th>
                <th><i class="fa fa-clock-o"></i> Last Block Found</th>
            </tr>
        </thead>
        <tbody id="pools_rows">
        </tbody>
    </table>
</div>


<div class="container">
    <div class="row">
        <div class="col-lg-6 col-md-6">
            <canvas id="poolsChart" style="margin: 0 auto;"></canvas>
        </div>
    </div>
</div>

<br />

<div class="container">
    <div class="row">
        <div class="col-lg-12">
            <small>*Total known pools hashrate.</small>
        </div>
    </div>
</div>

<script src="/js/Chart.bundle.min.js"></script>
<script>
    window.NETWORK_STAT_MAP = new Map(networkStat[symbol.toLowerCase()]);
    window.poolNames = [];
    window.poolHashrates = [];
    window.colors = [];

    totalHashrate = 0;

    var poolsRefreshed = 0;
    NETWORK_STAT_MAP.forEach((url, host, map) => {
        $.getJSON(url + '/stats', (data, textStatus, jqXHR) => {
            var d = new Date(parseInt(data.pool.lastBlockFound));
            var datestring = ("0" + d.getDate()).slice(-2) + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + d.getFullYear() + " " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
            var agostring = $.timeago(d);

            $('#pools_rows').append('<tr><td id=host'+host+'><a target=blank href=http://'+host+'>'+host+'</a></td><td id=height'+host+'>'+data.network.height+'</td><td id=hashrate'+host+'>'+getReadableHashRateString(data.pool.hashrate)+'&nbsp;</td><td id=miners'+host+'>'+data.pool.miners+'</td><td id=totalFree'+host+'>'+calculateTotalFee(data)+'%</td><td><span id=lastFound'+host+'>'+datestring+'</span> (<span class="timeago" id="ago-'+host+'">'+agostring+'</span>)</td><</tr>');
            if(host!="pool.pennykoin.com"){
              totalHashrate += parseInt(data.pool.hashrate);
              updateText('totalPoolsHashrate', getReadableHashRateString(totalHashrate));
              poolNames.push(host);
              poolHashrates.push(parseInt(data.pool.hashrate));
              window.colors.push(getRandomColor());
            }
        });
        poolsRefreshed++;
        if (poolsRefreshed === NETWORK_STAT_MAP.size){
            setTimeout(function(){ displayChart(); }, 1000);
        }
    });

    currentPage = {
        destroy: function(){
        },
        init: function(){
        },
        update: function(){
        }
    };

    function calculateTotalFee(config) {
        let totalFee = config.config.fee;
        for (let property in config.config.donation) {
            if (config.config.donation.hasOwnProperty(property)) {
                totalFee += config.config.donation[property];
            }
        }
        return totalFee;
    }

    function displayChart() {
        var ctx = document.getElementById("poolsChart");
        var chartData = {
            labels: poolNames,
            datasets: [{
                data: poolHashrates,
                backgroundColor: colors,
                borderWidth: 1,
                segmentShowStroke: false
            }]
        };
        var options = {
            title: {
                display: true,
                text: 'Pools Hashrate'
            },
            tooltips: {
                enabled: true,
                mode: 'single',
                callbacks: {
                    title: function (tooltipItem, data) { return data.labels[tooltipItem[0].index]; },
                    label: function (tooltipItem, data) {
                        var amount = data.datasets[tooltipItem.datasetIndex].data[tooltipItem.index];
                        var total = eval(data.datasets[tooltipItem.datasetIndex].data.join("+"));
                        return getReadableHashRateString(amount) + ' / ' + getReadableHashRateString(total) + '  (' + parseFloat(amount * 100 / total).toFixed(2) + '%)';
                    }
                }
            }
        };

        window.poolsChart = new Chart(ctx,{
            type: 'pie',
            data: chartData,
            options: options
        });
    }

    setInterval(function(){
        var totalHashrate = 0;
        poolNames = [];
        poolHashrates = [];
        var poolsRefreshed = 0;

        NETWORK_STAT_MAP.forEach((url, host, map) => {
            $.getJSON(url + '/stats', (data, textStatus, jqXHR) => {
                updateText('height'+host, data.network.height);
                updateText('hashrate'+host, data.pool.hashrate);
                updateText('miners'+host, data.pool.miners);
                updateText('totalFree'+host, calculateTotalFee(data)+'%');
                var d = new Date(parseInt(data.pool.lastBlockFound));

                var datestring = ("0" + d.getDate()).slice(-2) + "-" + ("0"+(d.getMonth()+1)).slice(-2) + "-" + d.getFullYear() + " " + ("0" + d.getHours()).slice(-2) + ":" + ("0" + d.getMinutes()).slice(-2);
                updateText('lastFound'+host, datestring);

                var agostring = $.timeago(d);
                updateText('ago-'+host, agostring);

                if(host!="pool.pennykoin.com"){
                  totalHashrate += parseInt(data.pool.hashrate);
                  updateText('totalPoolsHashrate', getReadableHashRateString(totalHashrate));
                }
            });
            poolsRefreshed++;
            if (poolsRefreshed === NETWORK_STAT_MAP.size){
                setTimeout(function(){ refreshChart(); }, 1000);
            }
        });
    }, 240000);

    function refreshChart() {
        var pool_rows = $('#pools_rows').children();
        for (var i = 0; i < pool_rows.length; i++) {
            var row = $(pool_rows[i]);
            var label = row.find('td:first').text();
            var hashrate =     row.find('td:nth-child(3)').text();
            poolsChart.data.labels[i] = label;
            poolsChart.data.datasets[0].data[i] = parseInt(hashrate);
        }
        poolsChart.update();
    }

    function getReadableHashRateString(hashrate){
        var i = 0;
        var byteUnits = [' H/s', ' KH/s', ' MH/s', ' GH/s', ' TH/s', ' PH/s' ];
        while (hashrate > 1024){
            hashrate = hashrate / 1024;
            i++;
        }
        return hashrate.toFixed(3) + byteUnits[i];
    }

    function getRandomColor() {
        var letters = '0123456789ABCDEF';
        var color = '#';
        for (var i = 0; i < 6; i++ ) {
            color += letters[Math.floor(Math.random() * 16)];
        }
        return color;
    }
</script>
