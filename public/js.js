(function (exports, doc) {

    exports.Search = function (inputId, resultId, suggestId)
    {
        this._input = doc.getElementById(inputId);
        this._result = doc.getElementById(resultId);
        this._suggest = doc.getElementById(suggestId);
        this._input.onkeyup = this._onKeyUp.bind(this);
        this._lastSearch = 0;
        this._request = new XMLHttpRequest();
        this._request.onreadystatechange = this._responseCallback.bind(this);
    }
    exports.Search.prototype = {

        _onKeyUp: function ()
        {
            var query = this._input.value
            ,   now = new Date().getTime()
            ;
            if (query.length < 3 || (now - this._lastSearch) < 500) {
                return;
            }
            this._lastSearch = now;
            this._request.abort();
            this._request.open('GET', '/?q=' + encodeURIComponent(query), true);
            this._request.send();
        },

        _responseCallback: function ()
        {
            if (this._request.readyState != 4 /* 4 = DONE */
                || this._request.status != 200) {
                return;
            }
            this._result.innerHTML = this._request.responseText;
            this._lastSearch = 0;
        }

    };

})(window, document);

