var api = 'https://pool.pennykoin.com/daemon';
var blockTargetInterval = 180;
var coinUnits = 100;
var symbol = 'PK';
var refreshDelay = 30000;
// pools stats by MainCoins
var networkStat = {
    "pk": [
      ["pool.pennykoin.com", "https://pool.pennykoin.com/api"],
      ["pk.easypool.pro", "https://api.easypool.pro/pk"],
      ["easyhash.pro/pk", "https://easyhash.pro/pk/api"],
      ["walpool.com/pk","https://api.walpool.com/rpc/pennyk"],
      ["getpool.org/pk","https://api.getpool.org/rpc/pk"],
      ["youpool.io/PK","https://pool.pennykoin.com/youpoolio"],
      ["communitypool.org/pk","https://communitypool.org:8121"]
    ]
};
