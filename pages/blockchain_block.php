<h2><i class="fa fa-cube fa-fw" aria-hidden="true"></i> Block <small id="block.hash" style="word-break: break-all;"></small></h2>
<div class="row">
    <div class="col-md-6 stats">
        <div><span data-toggle="tooltip" data-placement="right" data-original-title="Block index in the chain, counting from zero (i.e. genesis block)."><i class="fa fa-question-circle"></i></span> Height: <span id="block_height"><span id="block.height"></span></span></div>
        <div><span data-toggle="tooltip" data-placement="right" data-original-title="Block timestamp displayed as UTC. The timestamp correctness it up to miner, who mined the block."><i class="fa fa-question-circle"></i></span> Timestamp: <span id="block.timestamp"></span></div>
        <div><span data-toggle="tooltip" data-placement="right" data-original-title="“major version”.“minor version”"><i class="fa fa-question-circle"></i></span> Version: <span id="block.version"></span></div>
        <div><span data-toggle="tooltip" data-placement="right" data-original-title="How difficult it is to find a solution for the block. More specifically, it`s mathematical expectation for number of hashes someone needs to calculate in order to find a correct nonce value solving the block."><i class="fa fa-question-circle"></i></span> Difficulty: <span id="block.difficulty"></span></div>
        <div><span data-toggle="tooltip" data-placement="right" data-original-title="True, if the block belongs to an alternative chain. In such case all the transactions, excluding coinbase, are removed from the block back to transaction pool to be included in another block. It means there is no reward for the miner."><i class="fa fa-question-circle"></i></span> Orphan: <span id="block.orphan"></span></div>
        <div><span data-toggle="tooltip" data-placement="right" data-original-title="Number of transactions in the block, including coinbase transaction (which transfers block reward to the miner)."><i class="fa fa-question-circle"></i></span> Transactions: <span id="block.transactions"></span></a></div>
        <div><span data-toggle="tooltip" data-placement="right" data-original-title="Cumulative amount of coins issued by all the blocks in blockchain from the genesis and up to this block."><i class="fa fa-question-circle"></i></span> Total coins in the network: <span id="block.totalCoins"></span></div>
        <div><span data-toggle="tooltip" data-placement="right" data-original-title="Cumulative number of transactions in the blockchain, from the genesis block and up to this block."><i class="fa fa-question-circle"></i></span> Total transactions in the network: <span id="block.totalTransactions"></span></div>
    </div>

    <div class="col-md-6 stats">
        <div><span data-toggle="tooltip" data-placement="right" data-original-title="Cumulative size of all transactions in the block, including coinbase. In case it's exceeding 'effective txs median' the reward penalty occurs and therefore miner receives less reward."><i class="fa fa-question-circle"></i></span> Total transactions size, bytes: <span id="block.transactionsSize"></span></div>
        <div><span data-toggle="tooltip" data-placement="right" data-original-title="Size of the whole block, i.e. block header plus all transactions."><i class="fa fa-question-circle"></i></span> Total block size, bytes: <span id="block.blockSize"></span></div>
        <div><span data-toggle="tooltip" data-placement="right" data-original-title="Median value of block total transactions size among last n blocks."><i class="fa fa-question-circle"></i></span> Current txs median, bytes: <span id="block.currentTxsMedian"></span></div>
        <div><span data-toggle="tooltip" data-placement="right" data-original-title="Bounded from below median value that is actually used to calculate penalty. More specifically, &lt;effective median&gt; = max(&lt;current median&gt;, 20000)"><i class="fa fa-question-circle"></i></span> Effective txs median, bytes: <span id="block.effectiveTxsMedian"></span></div>
        <div><span data-toggle="tooltip" data-placement="right" data-original-title="Penalty for exceeding the median. &lt;penalty&gt; = (&lt;total&nbsp;transactions&nbsp;size&gt; / &lt;effective&nbsp;tx&nbsp;median&gt; − 1) ^ 2. No penalty if total transactions size is less then effective median. Penalty is near 100% if total txs size is twice the effective median. Greater blocks are not allowed."><i class="fa fa-question-circle"></i></span> Reward penalty: <span id="block.rewardPenalty"></span></div>
        <div><span data-toggle="tooltip" data-placement="right" data-original-title="Base value for calculating the block reward. Does not depend on how many transactions are included into the block. Also, this is how many coins the miner would receive if the block contains only coinbase transaction."><i class="fa fa-question-circle"></i></span> Base reward: <span id="block.baseReward"></span></div>
        <div><span data-toggle="tooltip" data-placement="right" data-original-title="Sum of fees for all transactions in the block."><i class="fa fa-question-circle"></i></span> Transactions fee: <span id="block.transactionsFee"></span></div>
        <div><span data-toggle="tooltip" data-placement="right" data-original-title="Actual amount of coins the miner received for finding the block. &lt;reward&gt; = &lt;base reward&gt; × (1 − &lt;penalty&gt;) + &lt;transactions fee&gt;"><i class="fa fa-question-circle"></i></span> Reward: <span id="block.reward"></span></div>
    </div>
</div>

<h3 class="transactions"><i class="fa fa-exchange fa-fw" aria-hidden="true"></i> Тransactions</h3>
<div class="table-responsive">
    <table class="table table-hover">
        <thead>
        <tr>
            <th><i class="fa fa-paw"></i> Hash</th>
            <th><i class="fa fa-percent"></i> Fee</th>
            <th><i class="fa fa-money"></i> Total Amount</th>
            <th><i class="fa fa-arrows"></i> Size</th>
        </tr>
        </thead>
        <tbody id="transactions_rows">

        </tbody>
    </table>
</div>

<script>
    var block, xhrGetBlock;

    currentPage = {
        destroy: function(){
            if (xhrGetBlock) xhrGetBlock.abort();
        },
        init: function(){
            getBlock();
        },
        update: function(){
        }
    };

    function getBlock(){
        if (xhrGetBlock) xhrGetBlock.abort();
        var searchBlk = $.parseJSON(sessionStorage.getItem('searchBlock'));
        if (searchBlk) {
            renderBlock(searchBlk);
        } else {
            xhrGetBlock = $.ajax({
                url: api + '/json_rpc',
                method: "POST",
                data: JSON.stringify({
                    jsonrpc:"2.0",
                    id: "test",
                    method:"f_block_json",
                    params: {
                        hash: '<?php echo $phash;?>'
                    }
                }),
                dataType: 'json',
                cache: 'false',
                success: function(data){
                    block = data.result.block;
                    renderBlock(block);
                }
            });
        }
        sessionStorage.removeItem('searchBlock');
    }

    function renderBlock(block){
        updateText('block.hash', block.hash);
        updateText('block.height', block.height);
        updateText('block.timestamp', formatDate(block.timestamp));
        updateText('block.version', block.major_version + '.' + block.minor_version);
        updateText('block.difficulty', block.difficulty);
        updateText('block.orphan', block.orphan_status ? "YES" : "NO");
        updateText('block.transactions', block.transactions.length);
        updateText('block.transactionsSize', block.transactionsCumulativeSize);
        updateText('block.blockSize', block.blockSize);
        updateText('block.currentTxsMedian', block.sizeMedian);
        updateText('block.effectiveTxsMedian', block.effectiveSizeMedian);
        updateText('block.rewardPenalty', block.penalty*100 + "%");
        updateText('block.baseReward', getReadableCoins(block.baseReward));
        updateText('block.transactionsFee', getReadableCoins(block.totalFeeAmount));
        updateText('block.reward', getReadableCoins(block.reward));
        updateText('block.totalCoins', getReadableCoins(block.alreadyGeneratedCoins));
        updateText('block.totalTransactions', block.alreadyGeneratedTransactions);
        renderTransactions(block.transactions);

        makePrevBlockLink(block.prev_hash);

        $.ajax({
            url: api + '/json_rpc',
            method: "POST",
            data: JSON.stringify({
                jsonrpc: "2.0",
                id: "test",
                method: "getblockheaderbyheight",
                params: {
                    height: (block.height + 1)
                }
            }),
            dataType: 'json',
            cache: 'false',
            success: function(data){
                if(data.result){
                    var nextBlockHash = data.result.block_header.hash;
                }
                if(nextBlockHash) {
                    makeNextBlockLink(nextBlockHash);
                }
            },
            error: function (ajaxContext) {
            }
        });

    }

    function getTransactionCells(transaction){
        return '<td>' + formatPaymentLink(transaction.hash) + '</td>' +
               '<td>' + getReadableCoins(transaction.fee, 4, true) + '</td>' +
               '<td>' + getReadableCoins(transaction.amount_out, 4, true) + '</td>' +
               '<td>' + transaction.size + '</td>';
    }

    function getTransactionRowElement(transaction, jsonString){
        var row = document.createElement('tr');
        row.setAttribute('data-json', jsonString);
        row.setAttribute('data-hash', transaction.hash);
        row.setAttribute('id', 'transactionRow' + transaction.hash);

        row.innerHTML = getTransactionCells(transaction);

        return row;
    }

    function renderTransactions(transactionResults){
        var $transactionsRows = $('#transactions_rows');

        for (var i = 0; i < transactionResults.length; i++){
            var transaction = transactionResults[i];
            var transactionJson = JSON.stringify(transaction);
            var existingRow = document.getElementById('transactionRow' + transaction.hash);

            if (existingRow && existingRow.getAttribute('data-json') !== transactionJson){
                $(existingRow).replaceWith(getTransactionRowElement(transaction, transactionJson));
            }
            else if (!existingRow){

                var transactionElement = getTransactionRowElement(transaction, transactionJson);
                $transactionsRows.append(transactionElement);
            }
        }
    }

    function makeNextBlockLink(blockHash){
        $('#block_height').append(' <a href="' + getBlockchainUrl(blockHash) + '" title="Next block"><i class="fa fa-chevron-circle-right" aria-hidden="true"></i></a>');
    }

    function makePrevBlockLink(blockHash){
        $('#block_height').prepend('<a href="' + getBlockchainUrl(blockHash) + '" title="Previous block"><i class="fa fa-chevron-circle-left" aria-hidden="true"></i></a> ');
    }

    function formatPrevNextBlockLink(hash){
        return '<a href="' + getBlockchainUrl(hash) + '">' + hash + '</a>';
    }

    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
