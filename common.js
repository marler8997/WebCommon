function get(id) {
    return document.getElementById(id);
}

function prettyTime(millis) {
    if (millis < 1000) {
        return (millis).toFixed(0) + ' millis';
    }
    if (millis < 60000) {
        return (millis / 1000).toFixed(0) + ' seconds';
    }
    if (millis < 3600000) {
        return (millis / 60000).toFixed(1) + ' minutes';
    }
    if (millis < 86400000) {
        return (millis / 3600000).toFixed(1) + ' hours';
    }
    return (millis / 86400000).toFixed(0) + ' days';
}
function prettyTimeLong(seconds) {
    if (seconds < 60) {
	seconds = parseInt(seconds);
        if(seconds == 1) return '1 second';
	return seconds + ' seconds';
    }
    if(seconds < 120) {
	seconds -= 60;
	if(seconds == 0) return '1 minute';
	if(seconds == 1) return '1 minute and 1 second';
	return '1 minute and ' + seconds + ' seconds';
    }
    var minutes = parseInt(seconds / 60);
    if(minutes < 60) {
	return minutes + ' minutes';
    }
    return minutes + ' minutes';
}




function addCommas(nStr) {
    nStr += '';
    x = nStr.split('.');
    x1 = x[0];
    x2 = x.length > 1 ? '.' + x[1] : '';
    var rgx = /(\d+)(\d{3})/;
    while (rgx.test(x1)) {
        x1 = x1.replace(rgx, '$1' + ',' + '$2');
    }
    return x1 + x2;
}

if (typeof XMLHttpRequest == "undefined") {
    XMLHttpRequest = function() {
        try { return new ActiveXObject("Msxml2.XMLHTTP.6.0"); } catch (e) { }
        try { return new ActiveXObject("Msxml2.XMLHTTP.3.0"); } catch (e) { }
        try { return new ActiveXObject("Microsoft.XMLHTTP"); } catch (e) { }
        throw new Error("This browser does not support XMLHttpRequest.");
    };
}

//private
function _HttpHandler() {
    if (this.readyState == 4) {
        this.lock.request = null;
        this.callback();
    }
}

function HttpGet(url, lock, callback) {
    if (!('request' in lock)) {
        lock['request'] = null;
    }
    if (lock.request == null) {
        lock.request = new XMLHttpRequest();
        var request = lock.request;
        request['lock'] = lock;
        request.callback = callback;
        request.onreadystatechange = _HttpHandler;
        request.open("GET", url);
        request.send();
    }
}
function HttpPost(url, post, lock, callback) {
    if (!('request' in lock)) {
        lock['request'] = null;
    }
    if (lock.request == null) {
        lock.request = new XMLHttpRequest();
        var request = lock.request;
        request['lock'] = lock;
        request.callback = callback;
        request.onreadystatechange = _HttpHandler;
        request.open('POST', url);
        request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        //request.setRequestHeader("Content-Length", post.length);
        //request.setRequestHeader("Connection","close");
        request.send(post);
    }
}

function _HttpJsonHandler() {
    if (this.status != 200) {
        this.ErrorCallback('Server returned HTTP error code ' + this.status);
        return;
    }
    var json;
    try {
        json = JSON.parse(this.responseText);
    } catch (err) {
        this.ErrorCallback('Server returned invalid JSON: ' + err);
        return;
    }

    if ('error' in json) {
        this.ErrorCallback('Server returned error: ' + json.error);
        return;
    }
    this.JsonCallback(json);
}

function HttpGetJson(url, lock, errorCallback, jsonCallback) {
    if (!('request' in lock)) {
        lock['request'] = null;
    }
    if (lock.request == null) {
        lock.request = new XMLHttpRequest();
        var request = lock.request;
        request['lock'] = lock;
        request['ErrorCallback'] = errorCallback;
        request['JsonCallback'] = jsonCallback;
        request.callback = _HttpJsonHandler;
        request.onreadystatechange = _HttpHandler;
        request.open("GET", url);
        request.send();
    }
}
function HttpPostJson(url, post, lock, errorCallback, jsonCallback) {
    if (!('request' in lock)) {
        lock['request'] = null;
    }
    if (lock.request == null) {
        lock.request = new XMLHttpRequest();
        var request = lock.request;
        request['lock'] = lock;
        request['ErrorCallback'] = errorCallback;
        request['JsonCallback'] = jsonCallback;
        request.callback = _HttpJsonHandler;
        request.onreadystatechange = _HttpHandler;
        request.open("POST", url);
        request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        request.send(post);
    }
}