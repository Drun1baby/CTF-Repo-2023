self.importScripts(
    "https://cdn.bootcdn.net/ajax/libs/spark-md5/3.0.0/spark-md5.js"
);
self.onmessage = (event) => {
    const captcha = event.data
    var res = 0;
    for (let index = 1; index < 1000000000; index++) {
        var hash = SparkMD5.hash(index.toString());
        if (hash.substr(0, 7) == captcha) {
            console.log(index);
            res = index;
            break
        }
    }
    postMessage(res);
};
