# PennyKoin Blockchain Explorer
Block explorer for PennyKoin Cryptocurrency.

#### Installation

1) It takes data from daemon "pkdaemon". It should be accessible from the Internet. Run the daemon with open port as follows:
```bash
./pkdaemon --rpc-bind-ip=0.0.0.0 --rpc-bind-port=9001
```
2) Just upload to your website and change 'api' variable in config.js to point to your daemon. You will also need to go through the API pages and update the links to point to your daemon IP as well. The API pages are located in the "q/" directory, the q/*.php file must point to your daemon IP. You must change all 4 of them "hashrate" "reward" "supply" "height"
3) You need webserver with php support to run this Blockchain Explorer
- For the apache server (.htaccess)
```bash
RewriteEngine On
Header set Access-Control-Allow-Origin "*"
Header add Access-Control-Allow-Headers "origin, x-requested-with, content-type"
Header add Access-Control-Allow-Methods "PUT, GET, POST, DELETE, OPTIONS"
RewriteRule ^block/(.*)$ index.php?phash=$1&module=blockchain_block
RewriteRule ^tx/(.*)$ index.php?phash=$1&module=blockchain_transaction
RewriteRule ^pools$ index.php?module=pools
RewriteRule ^api$ index.php?module=api
RewriteRule ^paymentid/(.*)$ index.php?phash=$1&module=blockchain_payment_id
RewriteRule ^txviewer$ txviewer/index.html
```
- For the nginx server
```bash
    location / {
      rewrite ^/block/(.*)$ /index.php?phash=$1&module=blockchain_block;
  		rewrite ^/tx/(.*)$ /index.php?phash=$1&module=blockchain_transaction;
  		rewrite ^/pools$ /index.php?module=pools;
  		rewrite ^/api$ /index.php?module=api;
  		rewrite ^/paymentid/(.*)$ /index.php?phash=$1&module=blockchain_payment_id;
  		rewrite ^/txviewer$ /txviewer/index.html;
    }
```
