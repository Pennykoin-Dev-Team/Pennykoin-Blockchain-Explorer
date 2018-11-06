<!DOCTYPE html>
<html>
<head lang="en">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, minimum-scale=1">
    <link rel="shortcut icon" href="favicon.ico">
    <link rel="icon" type="image/icon" href="favicon.ico" >
    <title>PennyKoin (PK) Block Explorer</title>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-timeago/1.4.0/jquery.timeago.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/jquery-sparklines/2.1.2/jquery.sparkline.min.js"></script>
    <link href="/css/themes/dark/style.css" rel="stylesheet" id="theme_link">
    <script src="//netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    <link href="//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <link href="//fonts.googleapis.com/css?family=Inconsolata" rel="stylesheet" type="text/css">
    <script src="/config.js?<?php echo time();?>"></script>
</head>
<body>
<?php
$module = $_GET["module"];
$phash = $_GET["phash"];
?>
<script>
    //var blockchainExplorer 	= "/?hash={id}#blockchain_block";
    //var transactionExplorer = "/?hash={id}#blockchain_transaction";
    //var paymentIdExplorer 	= "/?hash={id}#blockchain_payment_id";
    var blockchainExplorer 	= "/block/{id}";
    var transactionExplorer = "/tx/{id}";
    var paymentIdExplorer 	= "/paymentid/{id}";

    var style_cookie_name = "style";
    var style_cookie_duration = 365;
    var style_domain = window.location.hostname;

    $(function(){
        $('.theme-switch[rel="/css/themes/dark/style.css"]').hide();
        set_style_from_cookie();

        $('.theme-switch').click(function() {
            swapStyleSheet($(this).attr('rel'));
            $('.theme-switch').show();
            $(this).hide();
            return false;
        });

        function swapStyleSheet(sheet){
            $('#theme_link').attr('href',sheet);
            $('.theme-switch').show();
            $('.theme-switch[rel="'+sheet+'"]').hide();
            set_cookie(style_cookie_name, sheet, style_cookie_duration, style_domain);
        }

        function set_style_from_cookie(){
            var style = get_cookie(style_cookie_name);
            if (style.length){
                swapStyleSheet(style);
            }
        }

        function set_cookie (cookie_name, cookie_value, lifespan_in_days, valid_domain){
            var domain_string = valid_domain ?
                ("; domain=" + valid_domain) : '';
            document.cookie = cookie_name +
                "=" + encodeURIComponent(cookie_value) +
                "; max-age=" + 60 * 60 *
                24 * lifespan_in_days +
                "; path=/" + domain_string;
        }

        function get_cookie (cookie_name){
            var cookie_string = document.cookie;
            if (cookie_string.length != 0){
                var cookie_value = cookie_string.match(
                    '(^|;)[\s]*' +
                    cookie_name +
                    '=([^;]*)' );
                if(cookie_value != null && cookie_value.length>0) {
                    return decodeURIComponent (cookie_value[2]);
                }
            }
            return '';
        }
    });

    function getTransactionUrl(id) {
        return transactionExplorer.replace('{symbol}', symbol.toLowerCase()).replace('{id}', id);
    }

    $.fn.update = function(txt){
        var el = this[0];
        if (el.textContent !== txt)
            el.textContent = txt;
        return this;
    };

    function updateTextClasses(className, text){
        var els = document.getElementsByClassName(className);
        for (var i = 0; i < els.length; i++){
            var el = els[i];
            if (el.textContent !== text)
                el.textContent = text;
        }
    }

    function updateText(elementId, text){
        var el = document.getElementById(elementId);
        if (el.textContent !== text){
            el.textContent = text;
        }
        return el;
    }

    function updateTextLinkable(elementId, text){
        var el = document.getElementById(elementId);
        if (el.innerHTML !== text){
            el.innerHTML = text;
        }
        return el;
    }

    var currentPage;
    var lastStats;

    function getReadableHashRateString(hashrate){
        var i = 0;
        var byteUnits = [' H', ' KH', ' MH', ' GH', ' TH', ' PH' ];
        while (hashrate > 1024){
            hashrate = hashrate / 1024;
            i++;
        }
        return hashrate.toFixed(2) + byteUnits[i];
    }

    function getReadableDifficultyString(difficulty, precision){
        if (isNaN(parseFloat(difficulty)) || !isFinite(difficulty)) return 0;
        if (typeof precision === 'undefined') precision = 0;
        var units = ['', 'k', 'M', 'G', 'T', 'P'],
            number = Math.floor(Math.log(difficulty) / Math.log(1000));
        if (units[number] === undefined || units[number] === null) {
            return 0
        }
        return (difficulty / Math.pow(1000, Math.floor(number))).toFixed(precision) + ' ' +  units[number];
    }

    function formatBlockLink(hash){
        return '<a href="' + getBlockchainUrl(hash) + '">' + hash + '</a>';
    }

    function getReadableCoins(coins, digits, withoutSymbol){
        var amount = (parseInt(coins || 0) / coinUnits).toFixed(digits || coinUnits.toString().length - 1);
        return amount + (withoutSymbol ? '' : (' ' + symbol));
    }

    function formatDate(time){
        if (!time) return '';
        return new Date(parseInt(time) * 1000).toLocaleString();
    }

    function formatPaymentLink(hash){
        return '<a href="' + getTransactionUrl(hash) + '">' + hash + '</a>';
    }

    function pulseLiveUpdate(){
        var stats_update = document.getElementById('stats_updated');
        stats_update.style.transition = 'opacity 100ms ease-out';
        stats_update.style.opacity = 1;
        setTimeout(function(){
            stats_update.style.transition = 'opacity 7000ms linear';
            stats_update.style.opacity = 0;
        }, 500);
    }

    window.onhashchange = function(){
        routePage();
    };

    function fetchLiveStats() {
        $.ajax({
            url: api + '/getinfo',
            dataType: 'json',
            type: 'GET',
            cache: 'false'
        }).done(function(data){
            pulseLiveUpdate();
            lastStats = data;
            currentPage.update();
        }).always(function () {
            setTimeout(function() {
                fetchLiveStats();
            }, refreshDelay);
        });
    }

    function floatToString(float) {
        return float.toFixed(6).replace(/[0\.]+$/, '');
    }

    var xhrPageLoading;
    function routePage(loadedCallback) {

        if (currentPage) currentPage.destroy();
        $('#page').html('');
        $('#loading').show();

        if (xhrPageLoading)
            xhrPageLoading.abort();

        $('.hot_link').parent().removeClass('active');
        var $link = $('a.hot_link[href="' + (window.location.hash || '#') + '"]');

        $link.parent().addClass('active');
        var page = $link.data('page');

        xhrPageLoading = $.ajax({
            url: 'pages/' + page,
            cache: false,
            success: function (data) {
                $('#loading').hide();
                $('#page').show().html(data);
                currentPage.init();
                currentPage.update();
                if (loadedCallback) loadedCallback();
            }
        });
    }

    function getBlockchainUrl(id) {
        return blockchainExplorer.replace('{id}', id);
    }

    $(function(){
        $.get(api + '/getinfo', function(data){
            lastStats = JSON.parse(data);
            routePage(fetchLiveStats);
        });
    });

    // Blockexplorer functions
    urlParam = function(name){
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results==null){
           return null;
        }
        else{
           return results[1] || 0;
        }
    }

    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>

<div class="navbar navbar-default navbar-fixed-top" role="navigation">
    <div class="container">
        <div class="navbar-header">
            <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Menu</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
            </button>
            <a href="https://blocks.pennykoin.com" class="foundation-menu-link"><img src="/pennykoin.png" border="0" height="36px"></a>
            <div id="stats_updated"><i class="fa fa-bolt"></i></div>
        </div>

        <div class="collapse navbar-collapse">

            <ul class="nav navbar-nav navbar-left explorer_menu">

                <li style="display:none;"><a class="hot_link" data-page="home.html" href="#">
                    <i class="fa fa-cubes" aria-hidden="true"></i> Block Explorer
                </a></li>

                <li><a class="hot_link" href="/">
                    <i class="fa fa-cubes" aria-hidden="true"></i> Block Explorer
                </a></li>

                <li><a class="hot_link" href="/pools">
                    <i class="fa fa-gavel" aria-hidden="true"></i> Pools
                </a></li>

                <li style="display:none;"><a class="hot_link" href="/api">
                    <i class="fa fa-code" aria-hidden="true"></i> API
                </a></li>

                <li style="display:none;"><a class="hot_link" data-page="blockchain_block.html" href="#blockchain_block">
                    <i class="fa fa-cubes"></i> Block
                </a></li>

                <li style="display:none;"><a class="hot_link" data-page="blockchain_transaction.html" href="#blockchain_transaction">
                    <i class="fa fa-cubes"></i> Transaction
                </a></li>

                <li style="display:none;"><a class="hot_link" data-page="blockchain_payment_id.html" href="#blockchain_payment_id">
                    <i class="fa fa-cubes"></i> Transactions by Payment ID
                </a></li>

                <li style="display:none;"><a class="hot_link" data-page="support.html" href="#support">
                    <i class="fa fa-comments"></i> Help
                </a></li>

            </ul>


            <div class="nav col-md-6 navbar-right explorer-search">
                <div class="input-group">
                    <input class="form-control" placeholder="Search by block height / hash, transaction hash, payment id" id="txt_search">
                    <span class="input-group-btn"><button class="btn btn-default" type="button" id="btn_search">
                        <span><i class="fa fa-search"></i> Search</span>
                    </button></span>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    $('#btn_search').click(function(e) {

        var text = document.getElementById('txt_search').value;

        function GetSearchBlockbyHeight(){
            var block, xhrGetSearchBlockbyHeight;
            if (xhrGetSearchBlockbyHeight) xhrGetSearchBlockbyHeight.abort();

            xhrGetSearchBlockbyHeight = $.ajax({
                url: api + '/json_rpc',
                method: "POST",
                data: JSON.stringify({
                    jsonrpc:"2.0",
                    id: "blockbyheight",
                    method:"getblockheaderbyheight",
                    params: {
                        height: parseInt(text)
                    }
                }),
                dataType: 'json',
                cache: 'false',
                success: function(data){
                    if(data.result){
                        block = data.result.block_header;
                        window.location.href = getBlockchainUrl(block.hash);
                    } else if(data.error) {
                        wrongSearchAlert();
                    }
                }
            });
        }

        function GetSearchBlock(){
            var block, xhrGetSearchBlock;
            if (xhrGetSearchBlock) xhrGetSearchBlock.abort();

            xhrGetSearchBlock = $.ajax({
                url: api + '/json_rpc',
                method: "POST",
                data: JSON.stringify({
                    jsonrpc:"2.0",
                    id: "GetSearchBlock",
                    method:"f_block_json",
                    params: {
                        hash: text
                    }
                }),
                dataType: 'json',
                cache: 'false',
                success: function(data){
                    if(data.result){
                        block = data.result.block;
                        sessionStorage.setItem('searchBlock', JSON.stringify(block));
                        window.location.href = getBlockchainUrl(block.hash);
                    } else if(data.error) {
                        $.ajax({
                            url: api + '/json_rpc',
                            method: "POST",
                            data: JSON.stringify({
                                jsonrpc:"2.0",
                                id: "test",
                                method:"f_transaction_json",
                                params: {
                                    hash: text
                                }
                            }),
                            dataType: 'json',
                            cache: 'false',
                            success: function(data){
                                if(data.result){
                                    sessionStorage.setItem('searchTransaction', JSON.stringify(data.result));
                                    window.location.href = transactionExplorer.replace('{id}', text);
                                } else if(data.error) {
                                    xhrGetTsx =  $.ajax({
                                        url: api + '/json_rpc',
                                        method: "POST",
                                        data: JSON.stringify({
                                            jsonrpc:"2.0",
                                            id: "test",
                                            method:"k_transactions_by_payment_id",
                                            params: {
                                                payment_id: text
                                            }
                                        }),
                                        dataType: 'json',
                                        cache: 'false',
                                        success: function(data){
                                            if(data.result){
                                                txsByPaymentId = data.result.transactions;
                                                sessionStorage.setItem('txsByPaymentId', JSON.stringify(txsByPaymentId));
                                                window.location.href = paymentIdExplorer.replace('{id}', text);
                                            } else if(data.error) {
                                                $('#page').after(
                                                    '<div class="alert alert-warning alert-dismissable fade in" style="position: fixed; right: 50px; top: 50px;">'+
                                                    '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">' +
                                                        '&times;' +
                                                    '</button>' +
                                                    'We could not find anything.' +
                                                    '</div>');
                                            }
                                        }
                                    });
                                }
                            }
                        });
                    }
                }
            });
        }

        if ( text.length < 64 ) {
            GetSearchBlockbyHeight();
        } else if ( text.length == 64 ) {
            GetSearchBlock();
        } else {
            wrongSearchAlert();
        }

        e.preventDefault();

    });

    function wrongSearchAlert() {
        $('#page').after(
            '<div class="alert alert-danger alert-dismissable fade in" style="position: fixed; right: 50px; top: 50px;">'+
            '<button type="button" class="close" ' +
            'data-dismiss="alert" aria-hidden="true">' +
            '&times;' +
            '</button>' +
            '<strong>Wrong search query!</strong><br /> Please enter block height or hash, transaction hash, or payment id.' +
            '</div>');
    }

    $('#txt_search').keyup(function(e){
        if(e.keyCode === 13)
            $('#btn_search').click();
    });
</script>

<div id="content">
    <div class="container">
<?php
if($module!=""){
!@include("pages/".$module.".php");
}else{
?>
  <div id="page"></div>
  <p id="loading" class="text-center"><i class="fa fa-circle-o-notch fa-spin"></i></p>
<?php
}
?>
    </div>
</div>

<footer>
    <div class="container">
        <div class="row">
            <div class="col-lg-4 col-md-4 col-sm-6">
                <p>
                    <small>
                        &copy; 2018 <strong>PennyKoin</strong>
                    </small>
                </p>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6">
                <p>
                    <small>
                        <!-- This copyright should be left intact -->
                        Powered by <a target="_blank" href="https://github.com/hoangduongbao/pennykoin-blockchain-explorer"><i class="fa fa-github"></i> PennyKoin Blockchain Explorer</a>
                        <br />
                        <span class="text-muted">Based on Conceal Block Explorer, cryptonote-universal-pool and Karbowanec-Blockchain-Explorer
                            open sourced under the <a href="http://www.gnu.org/licenses/gpl-2.0.html">GPL</a></span>
                    </small>
                </p>
            </div>
            <div class="col-lg-4 col-md-4 col-sm-6">
                <ul>
                  <li><a href="https://pennykoin.com">PennyKoin Home</li>
				          <li><a href="https://wallet.pennykoin.com">PennyKoin Wallet Generator</li>
                  <li><a href="https://pool.pennykoin.com">PennyKoin Dev Pool</li>
                  <li><a href="https://blocks.pennykoin.com">PennyKoin Block Explorer</li>
                  <li><a href="https://github.com/Pennykoin">PennyKoin GitHub</li>

                </ul>
            </div>
        </div>
    </div>
</footer>
<a href="#" class="scrollup"><i class="fa fa-chevron-circle-up"></i></a>
    <script type="text/javascript">
        jQuery(function($) { $(document).ready(function() {
            $(window).scroll(function(){
                if ($(this).scrollTop() > 500) {
                    $('.scrollup').fadeIn();
                } else {
                    $('.scrollup').fadeOut();
                }
            });

            $('.scrollup').click(function(){
                $("html, body").animate({ scrollTop: 0 }, 600);
                return false;
            });

            $('.scrollup').css('opacity','0.3');

            $('.scrollup').hover(function(){
                $(this).stop().animate({opacity: 0.9}, 400);
            }, function(){
                $(this).stop().animate({opacity: 0.3}, 400);
            });

        });});
    </script>
</body>
</html>
