<h2><i class="fa fa-tag fa-fw" aria-hidden="true"></i> Payment ID <small id="paymend_id" style="word-break: break-all;"></small></h2>

<h3><i class="fa fa-exchange fa-fw" aria-hidden="true"></i> Transactions with this Payment ID</h3>
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
    var paymentId, xhrGetTsx, txsByPaymentId;
    paymentId = urlParam('hash');
    updateText('paymend_id', paymentId);

    currentPage = {
        destroy: function(){
            if (xhrGetTsx) xhrGetTsx.abort();
        },
        init: function(){
            getTransactions();
        },
        update: function(){
        }
    };

    function getTransactions(){
        if (xhrGetTsx) xhrGetTsx.abort();
        txsByPaymentId = $.parseJSON(sessionStorage.getItem('txsByPaymentId'));
        if (txsByPaymentId) {
            renderTransactions(txsByPaymentId);
        } else {
            xhrGetTsx = $.ajax({
                url: api + '/json_rpc',
                method: "POST",
                data: JSON.stringify({
                    jsonrpc:"2.0",
                    id: "test",
                    method:"k_transactions_by_payment_id",
                    params: {
                        payment_id: '<?php echo $phash;?>'
                    }
                }),
                dataType: 'json',
                cache: 'false',
                success: function(data){
                    var txs = data.result.transactions;
                    renderTransactions(txs);
                }
            });
        }
        sessionStorage.removeItem('txsByPaymentId');
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

    $(function() {
        $('[data-toggle="tooltip"]').tooltip();
    });
</script>
